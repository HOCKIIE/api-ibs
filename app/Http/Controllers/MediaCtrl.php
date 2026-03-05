<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Log;

class MediaCtrl extends Controller
{
    public function gallery(Request $request)
    {
        try{
            $type = $request->type;
            $id = $request->id;
            $draftId = $request->draftId;
            $thisDraft = $draftId ? 'draft' : '';
            $path = $thisDraft ? "uploads/$type/draft/$draftId" : "uploads/$type/$id";
            $gallery = Storage::disk('public')->files($path);
            return response()->json([
                'status' => true,
                'message' => 'Gallery fetched successfully',
                'gallery' => array_values(array_filter(array_map(function ($file) use ($type, $id) {
                    if (basename($file) === '.DS_Store') {
                        return null;
                    }
                    return [
                        'id' => $id,
                        'selected' => false,
                        'type' => $type,
                        'url' => asset('storage/' . $file),
                    ];
                }, $gallery)))
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function imageUploads(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string',
                'images' => 'required',
                'images.*' => 'image|mimes:jpg,jpeg,png,gif|max:3072', // รองรับหลายไฟล์
            ]);
    
            $type = $request->type;
            $id = $request->draftId ? $request->draftId :$request->id;
            $draft = $request->draftId ? 'draft' : '';
            $response = [
                'status' => false,
                'message' => 'No images uploaded',
            ];
            $gallery = [];
    
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $manager = new ImageManager(new GdDriver());
                    $image = $manager->read($file->getPathname());
                    $webpBinary = (string) $image->toWebp(80);
                    if($draft){
                        $path = "uploads/$type/draft/$id";
                    } else {
                        $path = "uploads/$type/$id";
                    }
                    Storage::disk('public')->put("$path/$filename", $webpBinary);
                    $gallery[] = [
                        'id' => $id,
                        'selected' => true,
                        'type' => $type,
                        'url' => "/storage/$path/$filename",
                    ];
                }
            }
            if (count($gallery) > 0)
                $response = [
                    'status' => true,
                    'message' => 'Images uploaded successfully',
                    'gallery' => $gallery,
                ];
    
            return response()->json($response);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function deleteImage(Request $request)
    {
        try {
            $request->validate(['images' => 'required|array']);
            $images = $request->images;
            $response = [];
            foreach($images as $i => $image){
                $response[$i] = [
                    'status' => false,
                    'image' => $image,
                    'message' => 'Image not found',
                ];
                $image = Str::after($image, '/storage/');
                if (Storage::disk(env('FILESYSTEM_DISK'))->exists($image)) {
                    Storage::disk(env('FILESYSTEM_DISK'))->delete($image);
                    $response[$i] = [
                        'status' => true,
                        'message' => 'Image deleted successfully',
                    ];
                } 
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
