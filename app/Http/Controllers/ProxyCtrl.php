<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ProxyCtrl extends Controller
{
    
    public function proxy(Request $request, $slug)
    {
        $data = \App\Models\Brand::where(['apiName'=>$slug,'status'=>1])->first();
        if (!$data) {
            return response('Missing url', 400);
        }
        $targetUrl = $data->website;

        try {
            $response = Http::get($targetUrl);
            $body = $response->body();

            // ✅ แก้ path ของ asset ให้โหลดจากเว็บจริง
            // สมมติ $body คือ HTML ที่ดึงมาจากเว็บต้นทาง
            $body = preg_replace_callback(
                '/(src|href)=["\'](\/[^"\']*)["\']|url\(["\']?(\/[^)"\']*)["\']?\)/ix',
                function ($matches) use ($targetUrl) {
                    $base = rtrim($targetUrl, '/');
                    
                    // ถ้าเป็น src/href
                    if (!empty($matches[2])) {
                        return $matches[1] . '="' . $base . $matches[2] . '"';
                    }

                    // ถ้าเป็น url(/...)
                    if (!empty($matches[3])) {
                        return 'url(' . $base . $matches[3] . ')';
                    }

                    return $matches[0];
                },
                $body
            );


            return response($body, 200)
                ->header('Content-Type', $response->header('Content-Type', 'text/html'))
                ->header('X-Frame-Options', '')
                ->header('Content-Security-Policy', '');
        } catch (\Exception $e) {
            return response('Error: '.$e->getMessage(), 500);
        }
    }

    private function rewriteHtml($html, $slug, $baseUrl)
    {
        // ปรับ asset path → ชี้กลับมาที่ /proxy/{slug}/...
        $patterns = [
            '#(src|href)=[\'"]/(?!/)#i',  // absolute path (/css/style.css)
        ];
        $replacements = [
            '$1="/proxy/'.$slug.'/', 
        ];

        $html = preg_replace($patterns, $replacements, $html);

        // ปรับ URL แบบ relative เป็น absolute
        $html = preg_replace_callback(
            '#(src|href)=[\'"](?!http)([^\'"]+)[\'"]#i',
            function ($matches) use ($slug, $baseUrl) {
                $url = $matches[2];
                return $matches[1] . '="/proxy/' . $slug . '/' . ltrim($url, '/') . '"';
            },
            $html
        );

        return $html;
    }
}
