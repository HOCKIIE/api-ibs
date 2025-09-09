<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Http\Resources\OwnerResource;

class OwnerCtrl extends Controller
{
    protected $model;
    public function __construct()
    {
        $this->model = new Owner();
    }
    public function index()
    {
        try{
            $data = $this->model->find(1);
            return response()->json([
                'status' => true,
                'message' => 'Data retrieval successfulใ',
                'data' => (new OwnerResource($data))->resolve()
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
        }

    }
    public function update(Request $request)
    {
        try{
            $data = $this->model::findOrFail($request->id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'ไม่พบข้อมูล Owner ที่ต้องการแก้ไข'
                ], 404);
            }
            $data->title_th = $request->title_th;
            $data->title_en = $request->title_en;
            $data->title_ja = $request->title_ja;
            $data->address_th = $request->address_th;
            $data->address_en = $request->address_en;
            $data->address_ja = $request->address_ja;
            $data->email = $request->email;
            $data->phone = $request->phone;
            $data->mobile = $request->mobile;
            $data->gmap = $request->gmap;
            $data->updated_at = now()->toDateTimeString();
            if($data->save()){
                $response = [
                    'status' => true,
                    'statusCode' => 200,
                    'message' => 'Success, Data has been updated.',
                    'data' => (new OwnerResource($data))->resolve()
                ];
            }else{
                $response = [
                    'status' => true,
                    'statusCode' => 200,
                    'message' => 'An error occurred!'
                ];
            }
            return response()->json($response);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }
}
