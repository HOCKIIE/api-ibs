<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTFactory;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh','me', 'logout']]);
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
            $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            $user = Auth::user();
            $payload = JWTFactory::customClaims([
                'type' => 'refresh',
                'exp' => now()->addDay()->timestamp, // อายุ 1 วัน
                'sub' => $user->getJWTIdentifier(),  // สำคัญมาก ต้องมี
            ])->make();
            $refreshToken = JWTAuth::encode($payload)->get();

            return response()
            ->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'accessToken' => $token,
                    'type' => 'bearer',
                ]
            ])
            ->cookie(
                'accessToken',  // name
                $token,  // value
                60,  // expire
                '/',  // path
                'api-ibs.test',  // domain
                true,  // secure
                true,  // HttpOnly ✅
                false,  // raw
                'None' // SameSite
            )
            ->cookie('refreshToken', $refreshToken, 1440, '/', 'api-ibs.test', true, true, false, 'None');
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }
    public function me(Request $request)
    {
        $token = $request->cookie('accessToken');
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated (no token)'], 401);
        }
        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json([
                'status' => 'success',
                'accessToken' => $request->cookie('accessToken'),
                'user' => $user,
            ]);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error'], 401);
        }
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'accessToken' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {

        return response()->json(['message' => 'Logged out'])
        ->cookie(
            'accessToken', // name
            '', // value
            -1, // expire
            '/', // path
            '.vercel.app', //localhost // domain
            true, // secure
            true, // HttpOnly ✅
            false, // raw
            'None' // SameSite
        )
        ->cookie(
            'refreshToken', // name
            '', // value
            -1, // expire
            '/', // path
            '.vercel.app', //localhost // domain
            true, // secure
            true, // HttpOnly ✅
            false, // raw
            'None' // SameSite
        );

    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refreshToken');
        try {
            $payload = JWTAuth::setToken($refreshToken)->getPayload();
            if ($payload['type'] !== 'refresh') {
                return response()->json(['message' => 'Invalid refresh token'], 401);
            }
            $user = JWTAuth::setToken($refreshToken)->authenticate();
            $newAccessToken = JWTAuth::fromUser($user);

            return response()->json(['message' => 'Token refreshed'])
                ->cookie(
                'accessToken',  // name
                $newAccessToken,  // value
                60,  // expire
                '/',  // path
                '.vercel.app',  // domain
                true,  // secure
                true,  // HttpOnly ✅
                false,  // raw
                'None'  // SameSite
            );

        } catch (JWTException $e) {
            return response()->json(['status'=>false,'message' => 'Token expired or invalid'], 401);
        }
    }
}
