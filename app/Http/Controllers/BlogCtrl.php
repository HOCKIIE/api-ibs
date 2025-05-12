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
    public function index()
    {
        try {
            $data = Blog::with('categories')->all();
            return BlogResource::collection($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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

                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $manager = new ImageManager(new GdDriver());

                $image = $manager->read($file->getPathname());
                $image->resize(1200, null, function ($constraint) {
                    // $constraint->aspectRatio();
                    $constraint->upsize(); // Prevent upscaling if the image is smaller
                });

                $webpBinary = (string) $image->toWebp(80);

                Storage::disk('public')->put('uploads/' . $filename, $webpBinary);
                $imagePath = '/storage/uploads/' . $filename;
            }


            // Create a new blog post
            $blog = Blog::create([
                'image' => $imagePath,
                'category' => $request->category,
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
            $blog->categories()->attach([1, 2, 3]);

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
            $data = Blog::findOrfail($id);
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'title_th' => 'string|max:255',
                'title_en' => 'string|max:255',
                'title_jp' => 'string|max:255',
                'description_th' => 'string',
                'description_en' => 'string',
                'description_jp' => 'string',
                'detail_th' => 'string',
                'detail_en' => 'string',
                'detail_jp' => 'string',
            ]);

            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($data->image) {
                    Storage::delete($data->image);
                }
                // Store the new image and update the path in the database
                $path = $request->file('image')->store('images/blogs', 'public');
                $data->image = $path;
            }

            // update category
            $data->categories()->sync($request->category);
            // Update other fields
            $data->update($request->except(['image']));

            return response()->json([
                'status' => true,
                'message' => 'Blog post updated successfully',
                'data' => $data,
            ], 200);
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
