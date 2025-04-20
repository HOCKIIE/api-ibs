<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;
use App\Http\Resources\BrandResource;
use Illuminate\Support\Facades\Storage;

class BrandCtrl extends Controller
{
    public function index(Request $request)
    {
        try {

            $skip = $request->skip ?? 0;
            $limit = $request->limit ?? 10;

            $keyword = $request->keyword;

            $data = Brand::when($keyword, function ($query) use ($keyword) {
                $query->where(function ($where) use ($keyword) {
                    $where->where('name_th', 'like', "%$keyword%")
                        ->orWhere('name_en', 'like', "%$keyword%")
                        ->orWhere('name_jp', 'like', "%$keyword%")
                        ->orWhere('description_th', 'like', "%$keyword%")
                        ->orWhere('description_en', 'like', "%$keyword%")
                        ->orWhere('description_jp', 'like', "%$keyword%");
                });
            })
            ->skip($skip)
            ->take($limit)
            ->get();

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
                'name_th' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'name_jp' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_jp' => 'required|string',
            ]);

            // Handle image upload
            $path = $request->file('image')->store('images/brand');

            // Create a new brand
            $brand = Brand::create([
                'image' => $path,
                'name_th' => $request->input('name_th'),
                'name_en' => $request->input('name_en'),
                'name_jp' => $request->input('name_jp'),
                'description_th' => $request->input('description_th'),
                'description_en' => $request->input('description_en'),
                'description_jp' => $request->input('description_jp'),
                'status' => false,
            ]);

            return BrandResource::collection($brand);

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
            $brand = Brand::findOrFail($id);

            // Validate the request data
            $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name_th' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'name_jp' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_jp' => 'required|string'
            ]);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($brand->image) {
                    Storage::delete($brand->image);
                }
                // Store the new image and get its path
                $path = $request->file('image')->store('images/brand');
                $brand->image = $path;
            }

            // Update the brand details
            $brand->name_th = $request->input('name_th');
            $brand->name_en = $request->input('name_en');
            $brand->name_jp = $request->input('name_jp');
            $brand->description_th = $request->input('description_th');
            $brand->description_en = $request->input('description_en');
            $brand->description_jp = $request->input('description_jp');
            $brand->status = (Boolean)$request->status; // Set status to 1 (active)

            // Save the updated brand
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Brand updated successfully.',
                'data' => new BrandResource($brand),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);

            // Delete the image if it exists
            if ($brand->image) {
                Storage::delete($brand->image);
            }

            // Soft delete the brand
            $brand->delete();

            return response()->json([
                'status' => true,
                'message' => 'Brand deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
