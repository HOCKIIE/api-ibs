<?php

namespace App\Http\Controllers;

use App\Http\Resources\AboutResource;
use App\Models\About;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

class AboutUsCtrl extends Controller
{
    public function index()
    {
        try {
            $data = About::findOrFail(1);
            return response()->json((new AboutResource($data))->resolve());
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request)
    {
        try{
            $data = About::first();
            $data->title_th = Purify::clean($request->title_th);
            $data->title_en = Purify::clean($request->title_en);
            $data->title_jp = Purify::clean($request->title_jp);
            $data->detail_th = Purify::clean($request->detail_th);
            $data->detail_en = Purify::clean($request->detail_en);
            $data->detail_jp = Purify::clean($request->detail_jp);
            if($data->save()){
                $response = [
                    'status'=> true,
                    "message" => "Data has been updated."
                ];
            }else{
                $response = [
                    'status'=> false,
                    "message" => "An error occurred."
                ];
            }
            return response()->json($response);

        }catch(\Exception $e){
            return response()->json($e->getMessage());
        }
    }
}
