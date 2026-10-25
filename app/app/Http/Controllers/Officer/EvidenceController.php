<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Evidence;
use App\Models\Ppa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EvidenceController extends Controller
{
    /**
     * Upload a photo or document as evidence of work on the PPA.
     */
    public function store(Request $request, Ppa $ppa): RedirectResponse
    {
        Gate::authorize('track', $ppa);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'is_public' => ['boolean'],
        ], [
            'title.required' => 'Describe what the file shows, e.g. “Day 1 attendance sheet”.',
            'file.required' => 'Choose a photo or PDF to upload.',
            'file.mimes' => 'Upload a photo (JPG, PNG, WebP) or a PDF.',
            'file.max' => 'The file must be 10 MB or smaller.',
        ]);

        $file = $request->file('file');

        $evidence = new Evidence([
            'title' => $validated['title'],
            'is_public' => $request->boolean('is_public'),
        ]);
        $evidence->forceFill([
            'barangay_id' => $ppa->barangay_id,
            'uploaded_by' => $request->user()->id,
            'path' => $file->store("evidence/{$ppa->barangay_id}"),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        $ppa->evidence()->save($evidence);

        AuditLog::record('evidence.uploaded', $request->user(), ['file' => $evidence->title, 'public' => $evidence->is_public ? 'yes' : 'no'], $ppa);

        return redirect()->route('officer.ppas.show', $ppa)->with('status', 'Evidence uploaded.');
    }

    /**
     * Show or download the file to people who can see the PPA.
     */
    public function show(Ppa $ppa, Evidence $evidence): StreamedResponse
    {
        Gate::authorize('view', $ppa);

        return $evidence->isImage()
            ? Storage::response($evidence->path, $evidence->original_name)
            : Storage::download($evidence->path, $evidence->original_name);
    }

    /**
     * Switch the file between internal and public.
     */
    public function update(Request $request, Ppa $ppa, Evidence $evidence): RedirectResponse
    {
        Gate::authorize('track', $ppa);

        $evidence->is_public = $request->validate(['is_public' => ['required', 'boolean']])['is_public'];
        $evidence->save();

        AuditLog::record('evidence.visibility_changed', $request->user(), ['file' => $evidence->title, 'public' => $evidence->is_public ? 'yes' : 'no'], $ppa);

        return redirect()->route('officer.ppas.show', $ppa)
            ->with('status', $evidence->is_public ? 'The file will show on the public portal once the ABYIP is published.' : 'The file is now internal only.');
    }

    /**
     * Delete the file.
     */
    public function destroy(Request $request, Ppa $ppa, Evidence $evidence): RedirectResponse
    {
        Gate::authorize('track', $ppa);

        Storage::delete($evidence->path);
        $evidence->delete();

        AuditLog::record('evidence.deleted', $request->user(), ['file' => $evidence->title], $ppa);

        return redirect()->route('officer.ppas.show', $ppa)->with('status', 'Evidence removed.');
    }
}
