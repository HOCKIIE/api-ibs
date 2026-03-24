<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Http\Resources\BrandResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BrandCtrl extends Controller
{
    protected $model;
    protected $imageWidth;
    protected $imageHeight;

    public function __construct()
    {
        $this->imageWidth = env('BRAND_IMAGE_WIDTH', 1000);
        $this->imageHeight = env('BRAND_IMAGE_HEIGHT', 1000);
        $this->model = new Brand;
    }

    public function index(Request $request)
    {
        try {
            $status = $request->status;
            $keyword = $request->keyword;
            $orderBy = $request->orderBy ? $request->orderBy : 'desc';
            $limit = $request->limit ? $request->limit : 10;

            $data = Brand::when($request->status, function ($query) use ($status) {
                if ($status == 'true') {
                    $query->where('status', 1);
                }
                if ($status == 'false') {
                    $query->where('status', 0);
                }
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($where) use ($keyword) {
                    $where->where('title_th', 'like', "%$keyword%")
                        ->orWhere('title_en', 'like', "%$keyword%")
                        ->orWhere('title_ja', 'like', "%$keyword%");
                });
            })
            ->orderBy('created_at', $orderBy)
            ->paginate($limit);
            return BrandResource::collection($data);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $data = Brand::where('id',$request->id)->with('categories')->get();
            return response()->json(BrandResource::collection($data));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getBrand()
    {
        try {
            $data = Brand::where('status', 1)->get();
            return response()->json(BrandResource::collection($data));
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'title_th' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'title_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string',
                'apiName' => 'required|string|max:255|unique:brand,apiName',
                'category' => 'exists:category,id', // Validate category as an array of existing IDs
            ]);

            // Handle image upload
            $path = $request->file('image')->store('images/brand');

            // Create a new brand
            $data = new Brand;
            $data->image = $path;
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->description_th = $request->description_th;
            $data->description_en = $request->description_en;
            $data->description_ja = $request->description_ja;
            $data->detail_th = $request->detail_th;
            $data->detail_en = $request->detail_en;
            $data->detail_ja = $request->detail_ja;
            $data->descendant_th = json_decode($request->descendant_th,true);
            $data->descendant_en = json_decode($request->descendant_en,true);
            $data->descendant_ja = json_decode($request->descendant_ja,true);
            $data->website = $request->website;
            $data->apiName = $request->apiName;
            $data->status = $request->status || 0;
            
            if($data->save())
            {
                $data->categories()->attach($request->category);
                if($request->hasFile('image')){
                    // Store the image and get its path
                    $data->image = $this->uploadImage($request->file('image'), $data->id);
                    $data->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Brand created successfully.',
                    'data' => (new BrandResource(Brand::findOrfail($data->id)))->resolve(),
                ], 201);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create brand.',
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = Brand::findOrFail($id);

            // Validate the request data
            $request->validate([
                'title_th' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'title_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string',
                'apiName' => 'required|string|max:255|unique:brand,apiName,'.$id,
                'category' => 'exists:category,id', // Validate category as an array of existing IDs
            ]);

            // Update the brand details
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->description_th = $request->description_th;
            $data->description_en = $request->description_en;
            $data->description_ja = $request->description_ja;
            $data->detail_th = $request->detail_th;
            $data->detail_en = $request->detail_en;
            $data->detail_ja = $request->detail_ja;
            $data->descendant_th = json_decode($request->descendant_th,true);
            $data->descendant_en = json_decode($request->descendant_en,true);
            $data->descendant_ja = json_decode($request->descendant_ja,true);
            $data->website = $request->website;
            $data->apiName = $request->apiName;
            $data->status = $request->status;
            $categories = $request->input('category', []);

            // Save the updated data
            if($data->save())
            {
                $data->categories()->sync($categories);
                if ($request->hasFile('image')) {
                    $image = Str::after($data->image, '/storage/');
                    Storage::disk('public')->delete($image);
                    $data->image = $this->uploadImage($request->file('image'), $id);
                    $data->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Brand updated successfully.',
                    'data' => (new BrandResource(Brand::with('categories')->findOrfail($id)))->resolve(),
                ], 200);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update brand.',
                ], 500);
            }

            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        try{
            $data = Brand::findOrFail($id);
            $data->status  = $request->changeTo;
            if($data->save()){
                return response()->json([
                    'status' => true,
                    'statusCode' => 200,
                    'message' => 'Success, Status has been updated.'
                ]);
            }else{
                return response()->json([ 'status' => false, 'statusCode' => 500, 'message' => 'Invalid ID.']);
            }

        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'statusCode' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id'   => 'required|array',
                'id.*' => 'integer|exists:users,id',
            ]);

            $data = $this->model::whereIn('id',$request->id)->get();

            foreach ($data as $item) {
                $item->is_deleted = true;
                $item->save();
                $item->delete();
            }
            return response()->json([
                'status' => true,
                'message' => 'Blog post deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadImage($image, $id = null)
    {
        $file = $image;
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file->getPathname());
        $width = $image->width();
        $height = $image->height();
        if (
            $width > $height && $width > $this->imageHeight ||
            $width < $height && $width < $this->imageHeight
        ) {
            $image->scale(height: $this->imageWidth)
                ->crop($this->imageWidth, $this->imageHeight, 0, 0, position: 'center');
        }
        if ($width < $this->imageWidth) {
            $image->scale(width: $this->imageWidth);
        }

        $webpBinary = (string) $image->toWebp(80);
        Storage::disk('public')->put("uploads/brand/$id/$filename", $webpBinary);
        return "/storage/uploads/brand/$id/$filename";
    }

    public function getBrandByApiName($apiName){

        $data = Brand::where('apiName',$apiName)->where('status',1)->first();
        if($data->id){
            return response()->json([
                'status' => true,
                "message" => "Api Name: $apiName found",
                'data' => (new BrandResource($data))->resolve(),
            ],200);
        }else{
            return response()->json([
                'status' => false,
                "message" => "Api Name: $apiName not found",
            ],404);
        }

    }
    
}
