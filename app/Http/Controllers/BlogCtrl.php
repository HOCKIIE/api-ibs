<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Str;
use App\Http\Resources\BlogResource;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BlogCtrl extends Controller
{
    protected $imageWidth;
    protected $imageHeight;
    protected $model;

    public function __construct()
    {
        $this->imageWidth = env('BLOG_IMAGE_WIDTH');
        $this->imageHeight = env('BLOG_IMAGE_HEIGHT');
        $this->model = new Blog;
    }
    public function index(Request $request)
    {
        try {
            $model = new Blog;
            $status = $request->status;
            $keyword = $request->keyword;
            $orderBy = $request->orderBy ? $request->orderBy : 'desc';
            $limit = $request->limit ? $request->limit : 10;

            $data = $model
            ->select(
                'id','draftId',
                'image','image_th','image_en','image_ja',
                'title_th','title_en','title_ja',
                'description_th','description_en','description_ja',
                'created_at','updated_at','status','pathName','published_at'
            )
            ->when($request->status, 
                function ($query) use ($status) {
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
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadImage($file, $path)
    {
        if($file)
        {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getPathname());
            $width = $image->width();
            $height = $image->height();
    
            if ($width > $this->imageWidth) {
                $image->scale(width: $this->imageWidth)
                    ->crop($this->imageWidth, $this->imageHeight, 0, 0, position: 'center');
            }
            if ($width < $this->imageWidth) {
                $image->scale(width: $this->imageWidth);
            }
    
            $webpBinary = (string) $image->toWebp(80);
            Storage::disk(env('FILESYSTEM_DISK'))->put("uploads/$path/$filename", $webpBinary);
            return "/storage/uploads/$path/$filename";
        }
        return NULL;
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                'image_th' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'image_en' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'image_ja' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'title_th' => 'required|string|max:255',
                'title_en' => 'required|string|max:255',
                'title_ja' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_ja' => 'required|string',
                'descendant_th' => 'required|string',
                'descendant_en' => 'required|string',
                'descendant_ja' => 'required|string',
                'pathName' => 'nullable|string|max:255|unique:blog,pathName',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 200);
            }

            $draftId = $request->draftId;

            $blog = Blog::create([
                'draftId' => $request->draftId,
                'title_th' => $request->title_th,
                'title_en' => $request->title_en,
                'title_ja' => $request->title_ja,
                'description_th' => $request->description_th,
                'description_en' => $request->description_en,
                'description_ja' => $request->description_ja,
                'detail_th' => $request->detail_th,
                'detail_en' => $request->detail_en,
                'detail_ja' => $request->detail_ja,
                'descendant_en' => json_decode($request->descendant_en,true),
                'descendant_th' => json_decode($request->descendant_th,true),
                'descendant_ja' => json_decode($request->descendant_ja.true),
                'pathName' => $request->pathName,
                'recommend' => $request->has('recommend') && $request->recommend == 'true' ? 1 : 0,
                'status' => $request->has('status') && $request->status === 'true' ? 1 : 0,
                'published_at' => $request->has('published_at') ? now()->toDateTimeString() : NULL,
                'created_at' => now()->toDateTimeString(),
            ]);
            // store category
            $blog->categories()->attach($request->category);
            if ($blog->id && $request->hasFile('image_th') && $request->hasFile('image_en') && $request->hasFile('image_ja')) 
            {
                $imageTH = $this->uploadImage($request->image_th, "blog/$blog->id");
                $imageEN = $this->uploadImage($request->image_en, "blog/$blog->id");
                $imageJA = $this->uploadImage($request->image_ja, "blog/$blog->id");
                Blog::where('id', $blog->id)->update([
                    'image_th' => $imageTH,
                    'image_en' => $imageEN,
                    'image_ja' => $imageJA,
                    'detail_th'=> Str::replace("blog/draft/$draftId", "blog/$blog->id", $request->detail_th),
                    'detail_en'=> Str::replace("blog/draft/$draftId", "blog/$blog->id", $request->detail_en),
                    'detail_ja'=> Str::replace("blog/draft/$draftId", "blog/$blog->id", $request->detail_ja),
                ]);

                $draftPath = "uploads/blog/draft/$draftId";
                $finalPath = "uploads/blog/$blog->id";

                // ถ้า draft folder มีอยู่ → ย้ายทั้ง folder
                if (Storage::disk('public')->exists($draftPath)) {
                    
                    Storage::disk('public')->makeDirectory($finalPath);
                    $files = Storage::disk('public')->allFiles("$draftPath");
                    foreach ($files as $file) {
                        $newPath = str_replace($draftPath, $finalPath, $file);
                        Storage::disk('public')->move($file, $newPath);
                    }
                    Storage::disk('public')->deleteDirectory($draftPath);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Blog post created successfully',
                    'statusCode' => 201,
                    'data' => (new BlogResource(Blog::with('categories')->findOrfail($blog->id)))->resolve(),
                ], 201);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => $request->all()
            ]);
        }
    }

    public function show(string $id)
    {
        try {
            $data = Blog::with('categories')->with('category')->findOrfail($id);
            return response()->json((new BlogResource($data))->resolve());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(),[
                'title_th' => 'nullable|string|max:255',
                'title_en' => 'nullable|string|max:255',
                'title_ja' => 'nullable|string|max:255',
                'description_th' => 'nullable|string',
                'description_en' => 'nullable|string',
                'description_ja' => 'nullable|string',
                'descendant_th' => 'nullable|string',
                'descendant_en' => 'nullable|string',
                'descendant_ja' => 'nullable|string',
                'pathName' => 'nullable|string|max:255|unique:blog,pathName,' . $id,
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 200,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 200);
            }
            $data = Blog::findOrfail($id);
            if ($request->hasFile('image_th')) {
                if ($data->image_th) {
                    $image = Str::after($data->image_th, '/storage/');
                    Storage::disk(env('FILESYSTEM_DISK'))->delete($image);
                }
                $data->image_th = $this->uploadImage($request->image_th, "blog/$id");
            }
            if ($request->hasFile('image_en')) {
                if ($data->image_en) {
                    $image = Str::after($data->image_en, '/storage/');
                    Storage::disk(env('FILESYSTEM_DISK'))->delete($image);
                }
                $data->image_en = $this->uploadImage($request->image_en, "blog/$id");
            }
            if ($request->hasFile('image_ja')) {
                if ($data->image_ja) {
                    $image = Str::after($data->image_ja, '/storage/');
                    Storage::disk(env('FILESYSTEM_DISK'))->delete($image);
                }
                $data->image_ja = $this->uploadImage($request->image_ja, "blog/$id");
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
            $data->descendant_th = json_decode($request->descendant_th,true);
            $data->descendant_en = json_decode($request->descendant_en,true);
            $data->descendant_ja = json_decode($request->descendant_ja,true);
            $data->pathName = $request->pathName;
            $data->recommend = $request->has('recommend') && $request->recommend == 'true' ? 1 : 0;
            $data->updated_at = now()->toDateTimeString();
            // 
            if ($request->has('published_at')) {
                $data->published_at = now();
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
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function changeStatus(Request $request, $id) 
    {
        try{
            $data = $this->model::findOrFail($id);
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
                'id.*' => 'integer|exists:blog,id',
            ]);

            $blogs = $this->model::whereIn('id',$request->id)->get();

            foreach ($blogs as $item) {
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

    public function recent($number)
    {
        try{
            $number = $number == null ? 4 : (int)$number;
            $data = Blog::select(
                'id',
                'title_th','title_en','title_ja',
                'description_th','description_en','description_ja',
                'created_at','updated_at','status','image','pathName','published_at'
            )
            ->where('status', 1)
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
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],500);
        }
    }

    public function getBlog(Request $request)
    {
        try{
            $limit = $request->limit ? $request->limit : 6;
            $data = Blog::select(
                'id',
                'title_th','title_en','title_ja',
                'description_th','description_en','description_ja',
                'created_at','updated_at','status','image','pathName','published_at'
            )
            ->where('status',1)
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->paginate($limit);

            if($data->isEmpty()){
                return response()->json([
                    "status" => false,
                    "message" => "No data found"
                ]);
            }else{
                return BlogResource::collection($data);
            }
        } catch (\Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],500);
        }
    }
    public function getBlogById($id = 0)
    {
        try {
            $data = $this->model::where('id',$id)
            ->with('categories')
            ->first();

            $category = [];
            foreach($data->categories as $k => $v){
                if ($data->id != $v->blog_id) { $category[] = $v->id; }
            }

            $recommend = $this->model::whereHas('categories', function($query) use($category,$data){
                $query
                ->where('blog_id','!=',$data->id)
                ->whereIn('category_id',$category);
            })
            ->whereNotNull('published_at')
            ->where('status',1)
            ->inRandomOrder()
            ->limit(3)
            ->get();

            if ($data->id) {
                return response()->json([
                    "status" => true,
                    "message" => "data found",
                    "data" => (new BlogResource($data))->resolve(),
                    "recommend" => BlogResource::collection($recommend)
                ],200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No data found"
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getBlogByPathName(Request $request, $pathName)
    {
        try {
            $data = $this->model::where('pathName',$pathName)
            ->with('categories')
            ->first();

            $category = [];
            foreach($data->categories as $k => $v){
                if ($data->id != $v->blog_id) { $category[] = $v->id; }
            }

            $recommend = $this->model::whereHas('categories', function($query) use($category,$data){
                $query
                ->where('blog_id','!=',$data->id)
                ->whereIn('category_id',$category);
            })
            ->whereNotNull('published_at')
            ->where('status',1)
            ->inRandomOrder()
            ->limit(3)
            ->get();

            if ($data->id) {
                return response()->json([
                    "status" => true,
                    "message" => "data found",
                    "data" => (new BlogResource($data))->resolve(),
                    "recommend" => BlogResource::collection($recommend)
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No data found"
                ]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

    }
    public function byCategory(Request $request)
    {
        try {
            $category = $request->category;
            $data = $this->model::select(
                'id',
                'title_th','title_en','title_ja',
                'description_th','description_en','description_ja',
                'created_at','updated_at','status','image','pathName','published_at'
            )
            ->where('stauts',1)
            ->whereNotNul('published_at')
            ->whereHas('categories',function($query) use($category){
                if (is_array($category)) {
                    $query->whereIn('categories.id', $category);
                } else {
                    $query->where('categories.id', $category);
                }
            })
            ->inRandomOrder()
            ->limit(3)
            ->with('categories')
            ->get();

            return response()->json([
                'status'=>true,
                'message'=>'Your request is successful.',
                'data' => BlogResource::collection($data)
            ],200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }

    public function byCustomer($limiit = 5) 
    {
        try {
            $data = $this->model::select(
                'id',
                'title_th','title_en','title_ja',
                'description_th','description_en','description_ja',
                'created_at','updated_at','status','image','pathName','published_at'
            )
            ->where('status',1)
            ->where('recommend',1)
            ->whereNotNull('published_at')
            ->inRandomOrder()
            ->limit($limiit)
            ->get();

            return response()->json([
                'status'=>true,
                'message'=>'Your request is successful.',
                'data' => BlogResource::collection($data)
            ],200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }

}
