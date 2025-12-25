<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsCtrl extends Controller
{
    public function videoEffect()
    {
        try {
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
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function videoEffectUpdate(Request $request)
    {
        try {
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $path = '/uploads/videos';
                $filename = 'intro_video.' . $file->getClientOriginalExtension();
                // $file->move(public_path('uploads/videos'), $filename);
                Storage::disk('public')->putFileAs(
                    $path,
                    $file,
                    $filename
                );

                return response()->json([
                    'status' => true,
                    'statusCode' => 200,
                    'message' => 'Success!',
                    'path' => "$path/$filename"
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'statusCode' => 400,
                    'message' => 'No video file provided.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
