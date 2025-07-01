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
                ->paginate($limit);
            return CategoryResource::collection($data);
            // return response()->json(CategoryResource::collection($data));c
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    function getCategoryWithProduct(Request $request)
    {
        $keyword = $request->keyword;
        $categoryQuery = Category::when($request->keyword, function ($query) use ($keyword) {
            $query->where(function ($where) use ($keyword) {
                $where->where('name_th', 'like', "%$keyword%")
                    ->orWhere('name_en', 'like', "%$keyword%")
                    ->orWhere('name_ja', 'like', "%$keyword%");
            })
                ->orWhereHas('brand', function ($q) use ($keyword) {
                    $q->where('name_th', 'like', "%$keyword%")
                        ->orWhere('name_en', 'like', "%$keyword%")
                        ->orWhere('name_ja', 'like', "%$keyword%");
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

        return response()->json(CategoryResource::collection($categoryQuery));
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
                'name_th' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'name_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string'
            ]);

            $category = Category::create([
                'image' => $request->file('image')->store('images/category', 'public'),
                'name_th' => $request->input('name_th'),
                'name_en' => $request->input('name_en'),
                'name_ja' => $request->input('name_ja'),
                'description_th' => $request->input('description_th'),
                'description_en' => $request->input('description_en'),
                'description_ja' => $request->input('description_ja'),
                'detail_th' => $request->input('detail_th'),
                'detail_en' => $request->input('detail_en'),
                'detail_ja' => $request->input('detail_ja'),
                'status' => false,
            ]);
            return response()->json(CategoryResource::collection($category));
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
                'title_th' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'title_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string',
            ]);

            $data = Category::findOrfail($id);
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
                    $imagePath = $this->uploadImage($request->file('image'), $id);
                    $image = Str::after($data->image, '/storage/');
                    Storage::disk('public')->delete($image);
    
                    $data->image = $imagePath;
                    $data->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => "Category updated successfully",
                    'data' => (new CategoryResource(Category::findOrfail($id)))->resolve()
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
