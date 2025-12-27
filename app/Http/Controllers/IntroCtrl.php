<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IntroCtrl extends Controller
{
    public function videoEffect()
    {
        try {
            $prefix = 'uploads/videos';
            $disk = Storage::disk(env('FILESYSTEM_DISK'));
            
            // หาไฟล์ intro_video.*
            $file = collect($disk->files($prefix))
                ->first(fn ($f) => str_starts_with(basename($f), 'intro_video.'));

            $file = $file
                ? "/$file"
                : "/$prefix/default_video.mp4";
            return response()->json($file, 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
