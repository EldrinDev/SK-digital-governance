<?php

namespace App;

use App\Models\Barangay;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Reads an SK's own Facebook Page through Facebook's Graph API: the Chairperson connects the
 * Page once, and after that a post's link is enough to bring the post over as an announcement.
 */
class FacebookPages
{
    /**
     * What the Page connection asks for: the list of Pages the person manages, and their posts.
     */
    private const array SCOPES = ['pages_show_list', 'pages_read_engagement', 'pages_read_user_content'];

    private const string POST_FIELDS = 'id,message,full_picture,created_time,permalink_url';

    private const array IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /**
     * Determine whether the platform has a Meta app to connect Pages with.
     */
    public static function isConfigured(): bool
    {
        return filled(config('services.facebook.app_id')) && filled(config('services.facebook.app_secret'));
    }

    /**
     * Get the Facebook page where the officer agrees to connect their Pages. Apps set up with
     * Facebook Login for Business name a login configuration instead of listing permissions.
     */
    public function loginUrl(string $state): string
    {
        $configId = config('services.facebook.config_id');

        return 'https://www.facebook.com/'.config('services.facebook.graph_version').'/dialog/oauth?'.http_build_query([
            'client_id' => config('services.facebook.app_id'),
            'redirect_uri' => route('officer.facebook.callback'),
            'state' => $state,
            'response_type' => 'code',
            ...(filled($configId) ? ['config_id' => $configId] : ['scope' => implode(',', self::SCOPES)]),
        ]);
    }

    /**
     * Trade the code Facebook sent back for the Pages the officer manages. Page tokens made
     * from a long-lived user token don't expire, so the Page stays connected.
     *
     * @return list<array{id: string, name: string, access_token: string}>
     */
    public function pagesFor(string $code): array
    {
        $credentials = ['client_id' => config('services.facebook.app_id'), 'client_secret' => config('services.facebook.app_secret')];

        $userToken = $this->call('oauth/access_token', [...$credentials, 'redirect_uri' => route('officer.facebook.callback'), 'code' => $code])->json('access_token');
        $longLivedToken = $this->call('oauth/access_token', [...$credentials, 'grant_type' => 'fb_exchange_token', 'fb_exchange_token' => $userToken])->json('access_token');
        $pages = $this->call('me/accounts', ['fields' => 'id,name,access_token', 'limit' => 100, 'access_token' => $longLivedToken])->json('data') ?? [];

        return array_values(array_map(fn (array $page): array => [
            'id' => (string) $page['id'],
            'name' => (string) $page['name'],
            'access_token' => (string) $page['access_token'],
        ], array_filter($pages, fn (mixed $page): bool => is_array($page) && isset($page['id'], $page['name'], $page['access_token']))));
    }

    /**
     * Find one of the connected Page's posts from its link.
     *
     * Links that carry the post's number are looked up directly. Newer links (pfbid…) and
     * share links can't be, so those are matched against the Page's recent posts instead.
     *
     * @return array{message: string, picture: ?string, posted_at: Carbon, permalink: string}
     */
    public function post(Barangay $barangay, string $link): array
    {
        $link = $this->followShareLink($link);
        $pageId = (string) $barangay->facebook_page_id;
        $token = (string) $barangay->facebook_page_token;

        foreach ($this->postIds($link, $pageId) as $id) {
            $response = $this->send($id, ['fields' => self::POST_FIELDS, 'access_token' => $token]);

            if ($response->successful() && str_starts_with((string) $response->json('id'), $pageId.'_')) {
                return $this->toPost($response->json());
            }

            $this->failIfDisconnected($response);
        }

        $wanted = $this->normalize($link);
        $recent = $this->call("{$pageId}/posts", ['fields' => self::POST_FIELDS, 'limit' => 100, 'access_token' => $token])->json('data') ?? [];

        foreach ($recent as $post) {
            if ($this->normalize((string) ($post['permalink_url'] ?? '')) === $wanted) {
                return $this->toPost($post);
            }
        }

        throw new FacebookImportException("That post wasn’t found on {$barangay->facebook_page_name}. Use a link to one of the Page’s own posts: open the post, then copy the link from its date.");
    }

    /**
     * Download a post's photo, if it's an image the portal accepts (JPG, PNG or WebP, up to 5 MB).
     *
     * @return array{contents: string, mime: string, extension: string}|null
     */
    public function downloadPicture(string $url): ?array
    {
        try {
            $response = Http::timeout(20)->get($url);
        } catch (ConnectionException) {
            return null;
        }

        $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        if (! $response->successful() || ! isset(self::IMAGE_TYPES[$mime]) || strlen($response->body()) > 5 * 1024 * 1024) {
            return null;
        }

        return ['contents' => $response->body(), 'mime' => $mime, 'extension' => self::IMAGE_TYPES[$mime]];
    }

    /**
     * Graph API ids worth trying for a link: "{page}_{post}" from the number in it.
     *
     * @return list<string>
     */
    private function postIds(string $link, string $pageId): array
    {
        $parts = parse_url($link);
        parse_str($parts['query'] ?? '', $query);
        $path = $parts['path'] ?? '';
        $ids = [];

        if (isset($query['story_fbid'], $query['id']) && ctype_digit((string) $query['story_fbid']) && ctype_digit((string) $query['id'])) {
            $ids[] = "{$query['id']}_{$query['story_fbid']}";
        }

        if (preg_match('#/(?:posts|permalink|photos(?:/[^/]+)?|videos)/(\d+)#', $path, $match) === 1) {
            $ids[] = "{$pageId}_{$match[1]}";
        }

        if (isset($query['fbid']) && ctype_digit((string) $query['fbid'])) {
            $ids[] = "{$pageId}_{$query['fbid']}";
        }

        return array_values(array_unique($ids));
    }

    /**
     * A share link (facebook.com/share/p/…) only redirects to the post; ask where it leads.
     */
    private function followShareLink(string $link): string
    {
        if (! str_starts_with((string) parse_url($link, PHP_URL_PATH), '/share/')) {
            return $link;
        }

        try {
            $location = (string) Http::withoutRedirecting()->timeout(10)->get($link)->header('Location');
        } catch (ConnectionException) {
            return $link;
        }

        return preg_match('#^https://(?:www\.|m\.|web\.)?facebook\.com/#', $location) === 1 ? $location : $link;
    }

    /**
     * Put a post link in one form, so links copied from the app, the phone or a browser compare equal.
     */
    private function normalize(string $link): string
    {
        $parts = parse_url($link);
        parse_str($parts['query'] ?? '', $query);
        $kept = array_intersect_key($query, array_flip(['story_fbid', 'id', 'fbid']));
        ksort($kept);

        return 'facebook.com'.rtrim($parts['path'] ?? '', '/').($kept === [] ? '' : '?'.http_build_query($kept));
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array{message: string, picture: ?string, posted_at: Carbon, permalink: string}
     */
    private function toPost(array $post): array
    {
        return [
            'message' => (string) ($post['message'] ?? ''),
            'picture' => isset($post['full_picture']) ? (string) $post['full_picture'] : null,
            'posted_at' => Carbon::parse((string) $post['created_time'])->setTimezone(config('app.timezone')),
            'permalink' => (string) $post['permalink_url'],
        ];
    }

    /**
     * Call the Graph API, turning any failure into a message for the officer.
     *
     * @param  array<string, mixed>  $query
     */
    private function call(string $path, array $query): Response
    {
        $response = $this->send($path, $query);

        $this->failIfDisconnected($response);

        if ($response->failed()) {
            throw new FacebookImportException('Facebook didn’t answer as expected. Try again in a moment.');
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function send(string $path, array $query): Response
    {
        try {
            return $this->graph()->get($path, $query);
        } catch (ConnectionException) {
            throw new FacebookImportException('Facebook couldn’t be reached. Check the internet connection and try again.');
        }
    }

    /**
     * Facebook error 190 means the token no longer works: the password changed, or the app was removed from the Page.
     */
    private function failIfDisconnected(Response $response): void
    {
        if ($response->json('error.code') === 190) {
            throw new FacebookImportException('The connection to the Facebook Page has expired. Ask your SK Chairperson to connect the Page again.');
        }
    }

    private function graph(): PendingRequest
    {
        return Http::baseUrl('https://graph.facebook.com/'.config('services.facebook.graph_version'))->acceptJson()->timeout(15);
    }
}
