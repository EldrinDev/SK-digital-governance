<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicEvidenceController extends Controller
{
    /**
     * Serve a file on the public board: it must be marked public and belong to a published ABYIP.
     */
    public function show(Evidence $evidence): StreamedResponse
    {
        $evidence->loadMissing('ppa.abyip');

        abort_unless($evidence->is_public && $evidence->ppa->abyip->isPublished(), 404);

        return $evidence->isImage()
            ? Storage::response($evidence->path, $evidence->original_name)
            : Storage::download($evidence->path, $evidence->original_name);
    }
}
