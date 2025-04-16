<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Http\Resources\BlogResource;
use Illuminate\Support\Facades\Storage;

class BlogCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = Blog::all();
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
        // Validate the request data
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title_th' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
            'title_jp' => 'required|string|max:255',
            'description_th' => 'required|string',
            'description_en' => 'required|string',
            'description_jp' => 'required|string',
            'detail_th' => 'required|string',
            'detail_en' => 'required|string',
            'detail_jp' => 'required|string',
        ]);

        // Create a new blog post
        $blogPost = Blog::create([
            'title_th' => $request->input('title_th'),
            'title_en' => $request->input('title_en'),
            'title_jp' => $request->input('title_jp'),
            'description_th' => $request->input('description_th'),
            'description_en' => $request->input('description_en'),
            'description_jp' => $request->input('description_jp'),
            'detail_th' => $request->input('detail_th'),
            'detail_en' => $request->input('detail_en'),
            'detail_jp' => $request->input('detail_jp'),
            'status' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Blog post created successfully',
            'data' => $blogPost,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $data = Blog::findOrfail($id);
            return BlogResource::collection($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ],500);
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
        try{
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

            // Update other fields
            $data->update($request->except(['image']));

            return response()->json([
                'status' => true,
                'message' => 'Blog post updated successfully',
                'data' => $data,
            ], 200);
        } catch(\Exception $e) {
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
