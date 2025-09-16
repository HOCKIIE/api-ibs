<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Str;

class CategoryCtrl extends Controller
{
    protected $imageWidth;
    protected $imageHeight;

    public function __construct()
    {
        $this->imageWidth = env('CATEGORY_IMAGE_WIDTH', 1000);
        $this->imageHeight = env('CATEGORY_IMAGE_HEIGHT', 1000);
    }

    function index(Request $request)
    {
        try {
            $status = $request->status;
            $keyword = $request->keyword;
            $orderBy = $request->orderBy ? $request->orderBy : 'desc';
            $limit = $request->limit? $request->limit : 10;

            $data = Category::when($request->status, function ($query) use ($status) {
                if ($status == 'true') {
                    $query->where('status', 1);
                }
                if ($status == 'false') {
                    $query->where('status', 0);
                }
            })->when($request->keyword, function ($query) use ($keyword) {
                $query->where('title_th', "like", "%$keyword%")
                    ->orWhere('title_en', "like", "%$keyword%")
                    ->orWhere('title_ja', "like", "%$keyword%");
            })
            ->orderBy('created_at', $orderBy)
            ->paginate($limit);
            return CategoryResource::collection($data);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    function getCategory(Request $request)
    {
        $keyword = $request->keyword;
        $data = Category::where('status',1)->when($request->keyword, function ($query) use ($keyword) {
            $query->where(function ($where) use ($keyword) {
                $where->where('title_th', 'like', "%$keyword%")
                    ->orWhere('title_en', 'like', "%$keyword%")
                    ->orWhere('title_ja', 'like', "%$keyword%");
            });
        })->get();
        return response()->json(CategoryResource::collection($data));
    }
    function getCategoryWithBrand(Request $request)
    {
        $keyword = $request->keyword;
        $data = Category::when($request->keyword, function ($query) use ($keyword) {
            $query->where(function ($where) use ($keyword) {
                $where->where('title_th', 'like', "%$keyword%")
                    ->orWhere('title_en', 'like', "%$keyword%")
                    ->orWhere('title_ja', 'like', "%$keyword%");
            })
            ->orWhereHas('brand', function ($q) use ($keyword) {
                $q->where('title_th', 'like', "%$keyword%");
                $q->orWhere('title_en', 'like', "%$keyword%");
                $q->orWhere('title_ja', 'like', "%$keyword%");
            });
        })
        ->with('brand')
        ->get();

        return response()->json(CategoryResource::collection($data));
    }
    function getCategoryWithProduct(Request $request)
    {
        $keyword = $request->keyword;
        $data = Category::when($request->keyword, function ($query) use ($keyword) {
            $query->where(function ($where) use ($keyword) {
                $where->where('title_th', 'like', "%$keyword%")
                    ->orWhere('title_en', 'like', "%$keyword%")
                    ->orWhere('title_ja', 'like', "%$keyword%");
            })
                ->orWhereHas('brand', function ($q) use ($keyword) {
                    $q->where('title_th', 'like', "%$keyword%")
                        ->orWhere('title_en', 'like', "%$keyword%")
                        ->orWhere('title_ja', 'like', "%$keyword%");
                });
        })
        ->with('brand')->get();

        // $brandQuery = Brand::
        // when($request->keyword, function($query) use($keyword) {
        //     $query->where(function($where) use($keyword) {
        //         $where->where('name_th', 'like', '%'.$keyword.'%')
        //             ->orWhere('name_en', 'like', '%'.$keyword.'%')
        //             ->orWhere('name_ja', 'like', '%'.$keyword.'%');
        //     });
        // })
        // ->with('category');

        // // ดึงผลลัพธ์จากทั้งสองคิวรี
        // $categoryResults = $categoryQuery->get();
        // $brandResults = $brandQuery->get();

        // // รวมผลลัพธ์
        // $combinedResults = $categoryResults->merge($brandResults);

        return response()->json(CategoryResource::collection($data));
    }

     public function show(string $id)
    {
        try {
            $data = Category::findOrfail($id);
            return response()->json((new CategoryResource($data))->resolve());
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
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'title_th' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'title_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string'
            ]);

            $data = new Category;
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->description_th = $request->description_th;
            $data->description_en = $request->description_en;
            $data->description_ja = $request->description_ja;
            $data->status = false;

            if($data->save()) {
                if ($request->hasFile('image')) {
                    $data->image = $this->uploadImage($request->file('image'), $data->id);
                    $data->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => "Category created successfully",
                    'data' => (new CategoryResource($data))->resolve()
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to create category",
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try {
            $request->validate([
                'title_th' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'title_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string',
            ]);

            $data = Category::findOrfail($request->id);
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->description_th = $request->description_th;
            $data->description_en = $request->description_en;
            $data->description_ja = $request->description_ja;
            $data->updated_at = now()->toDateTimeString();
            if($data->save()) {
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $this->uploadImage($request->file('image'), $request->id);
                    $image = Str::after($data->image, '/storage/');
                    Storage::disk('public')->delete($image);
    
                    $data->image = $imagePath;
                    $data->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => "Category updated successfully",
                    'data' => (new CategoryResource(Category::with('brand')->findOrfail($request->id)))->resolve()
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Failed to update category",
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => "Category not found",
                ], 404);
            }

            // Soft delete the category
            $category->delete();

            return response()->json([
                'status' => true,
                'message' => "Category deleted successfully",
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
        Storage::disk('public')->put("uploads/category/$id/$filename", $webpBinary);
        return "/storage/uploads/category/$id/$filename";
    }

}
