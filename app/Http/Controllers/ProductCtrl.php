<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Str;

class ProductCtrl extends Controller
{
    protected $imageWidth;
    protected $imageHeight;

    public function __construct()
    {
        $this->imageWidth = env('PRODUCT_IMAGE_WIDTH', 1000);
        $this->imageHeight = env('PRODUCT_IMAGE_HEIGHT', 1000);
    }

    public function index(Request $request)
    {
        try {
            $model = new Product;
            $status = $request->status;
            $keyword = $request->keyword;
            $limit = $request->limit ? $request->limit : 10;

            $data = $model->when($request->status, function ($query) use ($status) {
                if ($status == 'true') {
                    $query->where('status', 1);
                }
                if ($status == 'false') {
                    $query->where('status', 0);
                }
            })
                ->when($request->keyword, function ($query) use ($keyword) {
                    $query->where('title_th', "like", "%$keyword%")
                        ->orWhere('title_en', "like", "%$keyword%")
                        ->orWhere('title_ja', "like", "%$keyword%");
                })
                ->with('brand')
                ->paginate($limit);

            return ProductResource::collection($data);
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
                'brand' => 'required',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string',
                'detail_th' => 'required|string',
                'detail_en' => 'required|string',
                'detail_ja' => 'required|string',
            ]);
            // Create a new blog post
            $data = Product::create([
                'title_th' => $request->title_th,
                'title_en' => $request->title_en,
                'title_ja' => $request->title_ja,
                'description_th' => $request->description_th,
                'description_en' => $request->description_en,
                'description_ja' => $request->description_ja,
                'detail_th' => $request->detail_th,
                'detail_en' => $request->detail_en,
                'detail_ja' => $request->detail_ja,
                'published_at' => $request->has('publish') ? now()->toDateTimeString() : NULL,
            ]);
            // store brand
            if ($data->save()) {
                $data->brand()->sync($request->input('brand', []));
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $this->uploadImage($request->file('image'), $data->id);
                }
                $data->image = $imagePath;
                $data->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Product created successfully',
                    'data' => $data,
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create product',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show(string $id)
    {
        try {
            $data = Product::with('brand')->findOrfail($id);
            return response()->json((new ProductResource($data))->resolve());
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'title_th' => 'nullable|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'title_ja' => 'nullable|string|max:255',
                'description_th' => 'nullable|string',
                'description_en' => 'nullable|string',
                'description_ja' => 'nullable|string',
                'detail_th' => 'nullable|string',
                'detail_en' => 'nullable|string',
                'detail_ja' => 'nullable|string',
            ]);

            $data = Product::findOrfail($id);
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->description_th = $request->description_th;
            $data->description_en = $request->description_en;
            $data->description_ja = $request->description_ja;
            $data->detail_th = $request->detail_th;
            $data->detail_en = $request->detail_en;
            $data->detail_ja = $request->detail_ja;
            $data->updated_at = now()->toDateTimeString();
            // 
            if ($request->has('published_at') && $data->published_at == null) {
                $data->published_at = $request->published_at;
            }
            // Update other fields
            if ($data->save()) {

                $data->brand()->sync($request->input('brand', []));
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $this->uploadImage($request->file('image'), $data->id);
                    $image = Str::after($data->image, '/storage/');
                    print_r(" >>> $image");
                    Storage::disk('public')->delete($image);
                    
                    $data->image = $imagePath;
                    $data->save();
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Blog post updated successfully',
                    'data' => (new ProductResource(Product::with('brand')->findOrfail($id)))->resolve()
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update blog post',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $data = Product::findOrfail($id);
            $data->delete();
            return response()->json([
                'status' => true,
                'message' => 'Blog post deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $data = Product::findOrfail($id);
            if ($data->image) {
                Storage::delete($data->image);
            }
            $data->forceDelete();
            return response()->json([
                'status' => true,
                'message' => 'Blog post deleted successfully',
            ], 200);
        } catch (\Exception $e) {
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
        if(
            $width > $height && $width > $this->imageHeight ||
            $width < $height && $width < $this->imageHeight
        ) {
            $image->scale(height:$this->imageWidth)
                ->crop($this->imageWidth, $this->imageHeight, 0,0,position: 'center');
        }
        if($width < $this->imageWidth) {
            $image->scale(width:$this->imageWidth);
        }

        $webpBinary = (string) $image->toWebp(80);
        Storage::disk('public')->put("uploads/product/$id/$filename", $webpBinary);
        return "/storage/uploads/product/$id/$filename";
    }
}
