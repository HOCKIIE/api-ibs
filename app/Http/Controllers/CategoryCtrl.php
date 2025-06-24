<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Http\Resources\CategoryResource;

class CategoryCtrl extends Controller
{
    function index(Request $request)
    {
        try {
            $status = $request->status;
            $keyword = $request->keyword;
            $limit = $request->limit ? $request->limit : 10;

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
            // return response()->json(CategoryResource::collection($data));
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
                    ->orWhere('name_jp', 'like', "%$keyword%");
            })
                ->orWhereHas('brand', function ($q) use ($keyword) {
                    $q->where('name_th', 'like', "%$keyword%")
                        ->orWhere('name_en', 'like', "%$keyword%")
                        ->orWhere('name_jp', 'like', "%$keyword%");
                });
        })
            ->with('brand')->get();

        // $brandQuery = Brand::
        // when($request->keyword, function($query) use($keyword) {
        //     $query->where(function($where) use($keyword) {
        //         $where->where('name_th', 'like', '%'.$keyword.'%')
        //             ->orWhere('name_en', 'like', '%'.$keyword.'%')
        //             ->orWhere('name_jp', 'like', '%'.$keyword.'%');
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

    public function store(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name_th' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'name_jp' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_jp' => 'required|string',
                'detail_th' => 'required|string',
                'detail_en' => 'required|string',
                'detail_jp' => 'required|string',
            ]);

            $category = Category::create([
                'image' => $request->file('image')->store('images/category', 'public'),
                'name_th' => $request->input('name_th'),
                'name_en' => $request->input('name_en'),
                'name_jp' => $request->input('name_jp'),
                'description_th' => $request->input('description_th'),
                'description_en' => $request->input('description_en'),
                'description_jp' => $request->input('description_jp'),
                'detail_th' => $request->input('detail_th'),
                'detail_en' => $request->input('detail_en'),
                'detail_jp' => $request->input('detail_jp'),
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
    public function update(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'name_th' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'name_jp' => 'required|string|max:255',
                'description_th' => 'required|string',
                'description_en' => 'required|string',
                'description_jp' => 'required|string',
                'detail_th' => 'required|string',
                'detail_en' => 'required|string',
                'detail_jp' => 'required|string',
                'status' => 'required|boolean',
            ]);

            $category = Category::find($request->id);
            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => "Category not found",
                ], 404);
            }

            $category->update([
                'image' => $request->file('image')->store('images/category', 'public'),
                'name_th' => $request->input('name_th'),
                'name_en' => $request->input('name_en'),
                'name_jp' => $request->input('name_jp'),
                'description_th' => $request->input('description_th'),
                'description_en' => $request->input('description_en'),
                'description_jp' => $request->input('description_jp'),
                'detail_th' => $request->input('detail_th'),
                'detail_en' => $request->input('detail_en'),
                'detail_jp' => $request->input('detail_jp'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                "status" => true,
                "message" => "Category updated successfully",
            ], 200);
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
}
