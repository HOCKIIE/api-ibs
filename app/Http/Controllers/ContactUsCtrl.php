<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Owner;
use App\Models\User;
use App\Http\Resources\ContactResource;
use App\Http\Resources\UserResource;
use App\Models\Contact;
use Symfony\Component\HttpFoundation\JsonResponse;

class ContactUsCtrl extends Controller
{
    public function index(): JsonResponse
    {
        $data = Owner::find(1);
        return response()->json(new ContactResource($data));
    }

    public function salesData(): JsonResponse
    {
        $data = User::where('contact_sale',1)->get();
        return response()->json(new UserResource($data));
    }

    public function store(Request $request): JsonResponse
    {
        $data = new Contact();
        $data->firstName = $request->firstName;
        $data->lastName = $request->lastName;
        $data->email = $request->email;
        $data->message = $request->message;

        if ($data->save()) {
            return response()->json([
                "status" => true,
                "message" => "Success, Data has been saved."
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "An error occurred!"
            ]);
        }
    }

    public function update(Request $request)
    {
        $data = Owner::find(1);
        $data->title_th = $request->title_th;
        $data->title_en = $request->title_en;
        $data->title_ja = $request->title_ja;
        $data->address_th = $request->address_th;
        $data->address_en = $request->address_en;
        $data->address_ja = $request->address_ja;
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
