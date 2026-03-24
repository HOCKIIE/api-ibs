<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $model;
    function __construct()
    {
        $this->model = new User;
    }
    public function index(Request $request)
    {
        $model = new User();
        $status = $request->status;
        $keyword = $request->keyword;
        $orderBy = $request->orderBy ? $request->orderBy : 'desc';
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
                ->orWhere('title_ja',"like","%$keyword%")
                ->orWhere('name',"like","%$keyword%")
                ->orWhere('phone',"like","%$keyword%")
                ->orWhere('email',"like","%$keyword%");
        })
        ->orderBy('created_at', $orderBy)
        ->paginate($limit);
        
        return UserResource::collection($data);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    
        // ✅ เข้ารหัส password ด้วย Bcrypt
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    
        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role'=>'required|string',
            'title_th' => 'required|string',
            'title_en' => 'required|string',
            'title_ja' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $data = new User;
        $data->role = $request->role;
        $data->title_th = $request->title_th;
        $data->title_en = $request->title_en;
        $data->title_ja = $request->title_ja;
        $data->contact_sale = $request->contact_sale;
        $data->name = $request->name;
        $data->phone = $request->phone;
        $data->email = $request->email;
        $data->password = bcrypt($request->password);
        if($data->save()){
            return response()->json(["status"=>true,"message"=>"The data has been created!"],201);
        }else{
            return response()->json(["status"=>true,"message"=>"An error occurred!"],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json(new UserResource($user));
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
        $request->validate([
            'role'=>'required|string',
            'title_th' => 'required|string',
            'title_en' => 'required|string',
            'title_ja' => 'required|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
        ]);

        $data = User::findOrFail($id);
        $data->role = $request->role;
        $data->title_th = $request->title_th;
        $data->title_en = $request->title_en;
        $data->title_ja = $request->title_ja;
        $data->contact_sale = $request->contact_sale;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        if($request->password){
            $data->password = bcrypt($request->password);
        }
        if($data->save()){
            return response()->json(["status"=>true,"message"=>"The data has been updated!"],200);
        }else{
            return response()->json(["status"=>true,"message"=>"An error occurred!"],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id'   => 'required|array',
                'id.*' => 'integer|exists:users,id',
            ]);

            $data = $this->model::whereIn('id',$request->id)->delete();

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
}
