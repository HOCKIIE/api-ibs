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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $model = new Blog;
            $status = $request->status;
            $keyword = $request->keyword;
            $limit = $request->limit ? $request->limit : 10;

            $data = $model->when($request->status, function($query) use($status){
                if($status == 'true'){
                    $query->where('status',1);
                }
                if($status == 'false'){
                    $query->where('status',0);
                }
            })
            ->when($request->keyword, function($query) use($keyword){
                $query->where('title_th',"like","%$keyword%")
                    ->orWhere('title_en',"like","%$keyword%")
                    ->orWhere('title_ja',"like","%$keyword%");
            })
            ->with('categories')
            ->paginate($limit);

            return BlogResource::collection($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadImage($image)
    {
        $file = $image;
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($file->getPathname());
        $image->resize(1200, null, function ($constraint) {
            // $constraint->aspectRatio();
            $constraint->upsize(); // Prevent upscaling if the image is smaller
        });
        $webpBinary = (string) $image->toWebp(80);
        Storage::disk('public')->put('uploads/' . $filename, $webpBinary);
        return '/storage/uploads/' . $filename;
    }
    /**
     * Store a newly created resource in storage.
     */
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
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->uploadImage($request->file('image'));
            }

            // Create a new blog post
            $blog = Blog::create([
                'image' => $imagePath,
                'title_th' => $request->title_th,
                'title_en' => $request->title_en,
                'title_jp' => $request->title_jp,
                'description_th' => $request->description_th,
                'description_en' => $request->description_en,
                'description_jp' => $request->description_jp,
                'detail_th' => $request->detail_th,
                'detail_en' => $request->detail_en,
                'detail_jp' => $request->detail_jp,
                'published_at' => $request->has('publish') ? now()->toDateTimeString() : NULL,
            ]);
            // store category
            $blog->categories()->attach($request->category);

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
    

    /**
     * Display the specified resource.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
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
            $data = Blog::findOrfail($id);
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($data->image) {
                    Storage::delete($data->image);
                }
                $data->image = $this->uploadImage($request->file('image'));
            }

            // update category
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
            $data->updated_at = now()->toDateTimeString();
            // 
            if($request->has('published_at') && $data->published_at == null){
                $data->published_at = $request->published_at;
            }
            // Update other fields
            if($data->save()){
                $data->categories()->sync($categories);
                return response()->json([
                    'status' => true,
                    'message' => 'Blog post updated successfully',
                    'data' => (new BlogResource(Blog::with('categories')->findOrfail($id)))->resolve()
                ],200);
            }else{
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Blog::findOrfail($id);
            // Delete the image if it exists
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

}