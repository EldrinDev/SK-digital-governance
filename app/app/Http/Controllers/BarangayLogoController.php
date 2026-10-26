<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangayLogoController extends Controller
{
    /**
     * Serve the SK's uploaded logo. Pages fall back to the national SK logo when there is none.
     */
    public function __invoke(Barangay $barangay): StreamedResponse
    {
        abort_if($barangay->logo_path === null, 404);

        return Storage::response($barangay->logo_path, headers: ['Cache-Control' => 'public, max-age=604800']);
    }
}
