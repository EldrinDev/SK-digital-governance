<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Barangay;
use App\Rules\FacebookUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BarangayProfileController extends Controller
{
    /**
     * Show the form for the SK's logo and Facebook page.
     */
    public function edit(Barangay $barangay): View
    {
        return view('admin.barangays.edit', ['barangay' => $barangay]);
    }

    /**
     * Save the SK's logo and Facebook page. Upload the official logo only with the SK's permission.
     */
    public function update(Request $request, Barangay $barangay): RedirectResponse
    {
        $validated = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:min_width=64,min_height=64,max_width=4000,max_height=4000'],
            'remove_logo' => ['boolean'],
            'facebook_url' => ['nullable', 'string', 'max:255', new FacebookUrl],
        ], [
            'logo.image' => 'Upload the logo as a PNG, JPG or WebP image.',
            'logo.mimes' => 'Upload the logo as a PNG, JPG or WebP image.',
            'logo.max' => 'The logo can be at most 2 MB.',
            'logo.dimensions' => 'Use a logo between 64 and 4000 pixels on each side.',
        ]);

        if ($request->hasFile('logo') || $request->boolean('remove_logo')) {
            if ($barangay->logo_path !== null) {
                Storage::delete($barangay->logo_path);
            }

            $barangay->logo_path = $request->hasFile('logo')
                ? $request->file('logo')->store("barangays/{$barangay->id}")
                : null;
        }

        $barangay->facebook_url = $validated['facebook_url'] ?? null;
        $changed = array_keys($barangay->getDirty());
        $barangay->save();

        if ($changed !== []) {
            AuditLog::record('barangay.updated', $request->user(), ['changed' => $changed], $barangay);
        }

        return redirect()->route('admin.barangays.index')->with('status', "SK {$barangay->name}’s profile was saved.");
    }
}
