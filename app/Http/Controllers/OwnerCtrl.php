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
            $data = $this->model->where('id',$request->id)->first();
            $data->update($request->all());
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
            return OwnerResource::collection($response);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ],500);
        }
    }
}
