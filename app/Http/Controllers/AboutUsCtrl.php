<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;

class AboutUsCtrl extends Controller
{
    public function index()
    {
        try {
            $data = About::find(1);
            return response()->json([
                'status' => true,
                'message' => 'About Us',
                'data' => [
                    'title_th' => 'เกี่ยวกับเรา',
                    'title_en' => 'About Us',
                    'title_jp' => '私たちについて',
                    'description_th' => 'รายละเอียดเกี่ยวกับเรา',
                    'description_en' => 'Details about us',
                    'description_jp' => '私たちに関する詳細',
                ],
            ], 200);
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
            $data->title_th = $request->get('title_th');
            $data->title_en = $request->get('title_en');
            $data->title_jp = $request->get('title_jp');
        }
    }
}
