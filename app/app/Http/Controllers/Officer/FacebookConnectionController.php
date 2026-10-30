<?php

namespace App\Http\Controllers\Officer;

use App\FacebookImportException;
use App\FacebookPages;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\OfficerPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FacebookConnectionController extends Controller
{
    /**
     * Send the Chairperson to Facebook to let the platform read the SK's Page.
     */
    public function redirect(Request $request, FacebookPages $facebook): RedirectResponse
    {
        $this->authorizeChairperson($request);
        abort_unless(FacebookPages::isConfigured(), 404);

        $request->session()->put('facebook.state', $state = Str::random(40));

        return redirect()->away($facebook->loginUrl($state));
    }

    /**
     * Facebook sends the Chairperson back here. One Page is connected right away; with
     * several, the Chairperson picks the SK's.
     */
    public function callback(Request $request, FacebookPages $facebook): RedirectResponse|View
    {
        $this->authorizeChairperson($request);

        $state = (string) $request->session()->pull('facebook.state');
        $returnTo = route('officer.announcements.create');

        if ($state === '' || ! hash_equals($state, (string) $request->query('state')) || ! $request->filled('code')) {
            return redirect($returnTo)->toast('The Facebook Page wasn’t connected. Try again, and choose Continue on Facebook.', 'warning');
        }

        try {
            $pages = $facebook->pagesFor((string) $request->query('code'));
        } catch (FacebookImportException $exception) {
            return redirect($returnTo)->toast($exception->getMessage(), 'error');
        }

        if ($pages === []) {
            return redirect($returnTo)->toast('No Facebook Page came through. Connect again and tick your SK’s Page when Facebook asks which Pages to share.', 'warning');
        }

        if (count($pages) === 1) {
            return $this->connect($request, $pages[0]);
        }

        $request->session()->put('facebook.pages', $pages);

        return view('officer.facebook.choose', [
            'pages' => array_map(fn (array $page): array => ['id' => $page['id'], 'name' => $page['name']], $pages),
            'returnTo' => $returnTo,
        ]);
    }

    /**
     * Connect the Page the Chairperson picked from the ones Facebook listed.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeChairperson($request);

        $page = collect($request->session()->get('facebook.pages', []))->firstWhere('id', $request->string('page_id')->toString());

        if ($page === null) {
            return redirect()->route('officer.announcements.create')->toast('Choose one of the Pages Facebook listed, or connect again.', 'warning');
        }

        return $this->connect($request, $page);
    }

    /**
     * Stop reading the SK's Facebook Page. Announcements already brought over stay.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authorizeChairperson($request);

        $barangay = $request->user()->barangay;
        $pageName = $barangay->facebook_page_name;
        $barangay->forceFill(['facebook_page_id' => null, 'facebook_page_name' => null, 'facebook_page_token' => null, 'facebook_connected_at' => null])->save();

        AuditLog::record('facebook.disconnected', $request->user(), ['page' => $pageName], $barangay);

        return back()->toast('The Facebook Page was disconnected.', 'info');
    }

    /**
     * @param  array{id: string, name: string, access_token: string}  $page
     */
    private function connect(Request $request, array $page): RedirectResponse
    {
        $barangay = $request->user()->barangay;
        $barangay->forceFill([
            'facebook_page_id' => $page['id'],
            'facebook_page_name' => $page['name'],
            'facebook_page_token' => $page['access_token'],
            'facebook_connected_at' => now(),
        ])->save();

        $request->session()->forget('facebook.pages');
        AuditLog::record('facebook.connected', $request->user(), ['page' => $page['name']], $barangay);

        return redirect()->route('officer.announcements.create')
            ->with('status', "{$page['name']} is connected. Paste a post’s link to bring it over.");
    }

    private function authorizeChairperson(Request $request): void
    {
        abort_unless($request->user()->holdsPosition(OfficerPosition::Chairperson), 403, 'Only the SK Chairperson connects the Facebook Page.');
    }
}
