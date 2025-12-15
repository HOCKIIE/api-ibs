<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IntroCtrl extends Controller
{
    public function videoEffect()
    {
        try{
            $prefix = '/uploads/videos';
            $disk = Storage::disk(env('FILESYSTEM_DISK'));
            $file = $disk->exists("$prefix/intro_video.mp4") 
                ? "$prefix/intro_video.mp4"
                : null;
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
