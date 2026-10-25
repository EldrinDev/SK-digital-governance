<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicEvidenceController extends Controller
{
    /**
     * Serve a file on the public board, under the same rules as the board itself.
     */
    public function show(Evidence $evidence): StreamedResponse
    {
        abort_unless($evidence->isPubliclyVisible(), 404);

        return $evidence->isImage()
            ? Storage::response($evidence->path, $evidence->original_name)
            : Storage::download($evidence->path, $evidence->original_name);
    }
}
