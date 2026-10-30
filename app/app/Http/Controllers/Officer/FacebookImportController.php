<?php

namespace App\Http\Controllers\Officer;

use App\AnnouncementCategory;
use App\FacebookImportException;
use App\FacebookPages;
use App\Http\Controllers\Controller;
use App\Rules\FacebookUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FacebookImportController extends Controller
{
    /**
     * Read a post from the SK's connected Facebook Page and hand back what the announcement
     * form needs: headline, caption, kind, original link and date, and the photo.
     *
     * The photo is kept aside until the form is saved; the form only gets a token for it.
     */
    public function __invoke(Request $request, FacebookPages $facebook): JsonResponse
    {
        $barangay = $request->user()->barangay;
        abort_unless(FacebookPages::isConfigured() && $barangay->hasFacebookPage(), 404);

        $validated = $request->validate(
            ['url' => ['required', 'string', 'max:500', new FacebookUrl]],
            ['url.required' => 'Paste the link to the Facebook post.'],
        );

        try {
            $post = $facebook->post($barangay, $validated['url']);
        } catch (FacebookImportException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $image = null;
        $picture = $post['picture'] !== null ? $facebook->downloadPicture($post['picture']) : null;

        if ($picture !== null) {
            $path = "facebook-imports/{$barangay->id}/".Str::random(40).'.'.$picture['extension'];
            Storage::put($path, $picture['contents']);
            $request->session()->put('facebook.images.'.($token = Str::random(32)), $path);
            $image = ['token' => $token, 'preview' => 'data:'.$picture['mime'].';base64,'.base64_encode($picture['contents'])];
        }

        return response()->json([
            'title' => $this->headline($post['message']),
            'body' => $post['message'],
            'category' => $this->kindOf($post['message'])->value,
            'source_url' => $post['permalink'],
            'originally_posted_at' => $post['posted_at']->format('Y-m-d\TH:i'),
            'image' => $image,
        ]);
    }

    /**
     * Facebook posts have no headline, so use the caption's first line that says something,
     * skipping lines that are only hashtags or a date, time or place.
     */
    private function headline(string $message): string
    {
        $line = collect(preg_split('/\R/u', $message))
            ->map(fn (string $line): string => trim($line))
            ->first(fn (string $line): bool => $line !== ''
                && preg_match('/^(#[\p{L}\p{N}_]+\s*)+$/u', $line) !== 1
                && preg_match('/^(🗓️?|📅|⏰|[\x{1F550}-\x{1F567}]|📍)/u', $line) !== 1);

        return $line !== null ? Str::limit($line, 149, '…', preserveWords: true) : 'Post from Facebook';
    }

    /**
     * Guess the kind of post from words SK pages commonly use. The officer can change it.
     */
    private function kindOf(string $message): AnnouncementCategory
    {
        $text = Str::lower($message);

        return match (true) {
            Str::contains($text, ['paalala', 'reminder', 'deadline', 'huling araw']) => AnnouncementCategory::Reminder,
            Str::contains($text, ['salamat', 'thank you', 'congratulations', 'congrats', 'pagbati', 'recap']) => AnnouncementCategory::Recognition,
            preg_match('/^\s*(🗓️?|📅|📍)/mu', $message) === 1, Str::contains($text, ['join us', 'inaanyayahan', 'tara na', 'register']) => AnnouncementCategory::Event,
            default => AnnouncementCategory::Announcement,
        };
    }
}
