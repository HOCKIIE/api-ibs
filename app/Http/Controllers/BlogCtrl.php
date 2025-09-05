<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Http\Resources\BlogResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;


class BlogCtrl extends Controller
{
    protected $imageWidth;
    protected $imageHeight;

    public function __construct()
    {
        $this->imageWidth = env('BRAND_IMAGE_WIDTH', 1000);
        $this->imageHeight = env('BRAND_IMAGE_HEIGHT', 1000);
    }
    public function index(Request $request)
    {
        try {
            $model = new Blog;
            $status = $request->status;
            $keyword = $request->keyword;
            $orderBy = $request->orderBy ? $request->orderBy : 'desc';
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
                ->with('categories')
                ->orderBy('created_at', $orderBy)
                ->paginate($limit);

            return BlogResource::collection($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadImage($request, $path)
    {
        $file = $request->file('image');
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
        Storage::disk('public')->put("uploads/$path/$filename", $webpBinary);
        return "/storage/uploads/$path/$filename";
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
                'detail_th' => 'required|string',
                'detail_en' => 'required|string',
                'detail_ja' => 'required|string',
                'pathName' => 'nullable|string|max:255|unique:blog,pathName',
            ]);

            $imagePath = null;

            // Create a new blog post
            $blog = Blog::create([
                'title_th' => $request->title_th,
                'title_en' => $request->title_en,
                'title_ja' => $request->title_ja,
                'description_th' => $request->description_th,
                'description_en' => $request->description_en,
                'description_ja' => $request->description_ja,
                'detail_th' => $request->detail_th,
                'detail_en' => $request->detail_en,
                'detail_ja' => $request->detail_ja,
                'pathName' => $request->pathName,
                'published_at' => $request->has('publish') ? now()->toDateTimeString() : NULL,
                'created_at' => now()->toDateTimeString(),
            ]);
            // store category
            $blog->categories()->attach($request->category);
            if ($blog->id && $request->hasFile('image')) {
                $imagePath = $this->uploadImage($request, "blog/$blog->id");
                Blog::where('id', $blog->id)->update(['image' => $imagePath]);
            }
            return response()->json([
                'status' => true,
                'message' => 'Blog post created successfully',
                'data' => $blog,
            ], 201);
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
            $data = Blog::with('categories')->findOrfail($id);
            return response()->json((new BlogResource($data))->resolve());
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
                'pathName' => 'nullable|string|max:255|unique:blog,pathName,' . $id,
            ]);
            $data = Blog::findOrfail($id);
            if ($request->hasFile('image')) {
                if ($data->image) {
                    Storage::delete($data->image);
                }
                $data->image = $this->uploadImage($request, "blog/$id");
            }

            $categories = $request->input('category', []);
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->description_th = $request->description_th;
            $data->description_en = $request->description_en;
            $data->description_ja = $request->description_ja;
            $data->detail_th = $request->detail_th;
            $data->detail_en = $request->detail_en;
            $data->detail_ja = $request->detail_ja;
            $data->pathName = $request->pathName;
            $data->updated_at = now()->toDateTimeString();
            // 
            if ($request->has('published_at') && $data->published_at == null) {
                $data->published_at = $request->published_at;
            }
            //
            if ($data->save()) {
                $data->categories()->sync($categories);
                return response()->json([
                    'status' => true,
                    'message' => 'Blog post updated successfully',
                    'data' => (new BlogResource(Blog::with('categories')->findOrfail($id)))->resolve()
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
            $data = Blog::findOrfail($id);
            if ($data->image) {
                Storage::delete($data->image);
            }
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

    public function recent(Request $request, $number)
    {
        try{
            $number = $number == null ? 4 : (int)$number;
            $data = Blog::where('status', 1)
                ->whereNotNull('published_at')
                ->orderBy('published_at', 'desc')
                ->limit($number)
                ->get();
            if ($data) {
                return response()->json([
                    "status" => true,
                    "data" => BlogResource::collection($data)
                ],200);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No data found"
                ],200);
            }
        }  catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],500);
        }
    }

    public function getBlog(Request $request)
    {
        try{
            $data = Blog::where('status',1)
                ->whereNotNull('published_at')
                ->orderBy('published_at', 'desc')
                ->skip(4)
                ->get();
            if($data->isEmpty()){
                return response()->json([
                    "status" => false,
                    "message" => "No data found"
                ]);
            }else{
                return response()->json([
                    "data" => BlogResource::collection($data),
                    "status" => true,
                    "message" => "Success"
                ]); 
            }
        } catch (\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],500);
        }
    }

    public function getBlogByPathName(Request $request, $pathName)
    {
        try {
            $data = Blog::where('pathName',$pathName)->get();
            if ($data->isEmpty()) {
                return response()->json([
                    "status" => false,
                    "message" => "No data found"
                ]);
            } else {
                return response()->json([
                    "status" => true,
                    "message" => "data found",
                    "data" => BlogResource::collection($data)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}
