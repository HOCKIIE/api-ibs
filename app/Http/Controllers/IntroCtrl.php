<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IntroCtrl extends Controller
{
    public function videoEffect()
    {
        try{
            $prefix = '/storage/uploads/videos';
            $disk = Storage::disk(env('FILESYSTEM_DISK'));

            $files = $disk->files('videos');

            $file = collect($files)->first(fn ($f) =>
                pathinfo($f, PATHINFO_FILENAME) === 'intro_video'
            );

            $file = $disk->exists("$prefix/$file")
                ? "$prefix/$file"
                : "$prefix/default_video.mp4";
            return response()->json($file,200);
        } catch(\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}
