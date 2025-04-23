<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

class AboutUsCtrl extends Controller
{
    public function index()
    {
        try {
            $data = About::find(1);
            return response()->json($data);
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
            $data->title_th = Purify::clean($request->get('title_th'));
            $data->title_en = Purify::clean($request->get('title_en'));
            $data->title_jp = Purify::clean($request->get('title_jp'));
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
