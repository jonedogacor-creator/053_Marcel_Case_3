<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentProfilePhotoController extends Controller
{
    public function __invoke(Request $request, int $studentProfileId)
    {
        $profile = $request->user()->studentProfiles()->findOrFail($studentProfileId);
        $path = $profile->photo_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, headers: [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
