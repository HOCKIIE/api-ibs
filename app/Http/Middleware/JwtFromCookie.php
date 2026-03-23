<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtFromCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // 🔥 เอาจาก header ก่อน
        $token = $request->bearerToken();


        if (!$token) {
            $token = $request->cookie('accessToken');
        }

        if (!$token) {
            throw new UnauthorizedHttpException('', 'Token not provided');
        }

        // 🔥 inject ให้ jwt.auth ใช้ต่อ
        $request->headers->set('Authorization', 'Bearer ' . $token);

        return $next($request);
    }
}
