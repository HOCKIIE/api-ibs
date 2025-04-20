<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Http\Resources\ContactResource;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactUsCtrl extends Controller
{
    public function index(): JsonResponse
    {
        $data = Owner::find(1);
        return response()->json(new ContactResource($data));
    }

    public function update(Request $request)
    {
        $data = Owner::find(1);
        $data->title = $request->title;
        $data->address = $request->address;
        $data->phone = $request->phone;
        $data->mobile = $request->mobile;
        $data->email = $request->email;
        $data->gmap = $request->gmap;
        if ($data->save()) {
            $response = [
                "status" => true,
                "message" => "Success, Data has been updated."
            ];
        } else {
            $response = [
                "status" => false,
                "message" => "An error occurred!"
            ];
        }
        return response()->json($response);
    }
}
