<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    protected $appURL = '';
    protected $tokenExpire = '';
    protected $cookie = '';
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'refresh', 'me', 'logout']]);
        $this->appURL = env('APP_ENV') === "development" ? env('APP_URL_DEV') : env('APP_URL_PROD');
        $this->tokenExpire = 15;
        $this->cookie = [
            "name" => "accessToken",
            "expire" => 15, // minute
            "path" => "/",
            "domain" => $this->appURL,
            "secure" => true,
            "HttpOnly" => true,
            "raw" => false,
            "SameSite" => "none"
        ];
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
                    cookie(
                        $this->cookie['name'],
                        $token,  // value
                        $this->cookie['expire'],
                        $this->cookie['path'], 
                        $this->cookie['domain'],
                        $this->cookie['secure'],
                        $this->cookie['HttpOnly'],
                        $this->cookie['raw'],
                        $this->cookie['SameSite']
                    )
                )
                ->cookie(cookie('refreshToken', $refreshToken, 1440, '/', $this->appURL, true, true, false, 'None'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json($e->getMessage());
        }
    }
    public function me(Request $request)
    {
        $token = $request->cookie('accessToken');
        // $token = $request->header('accessToken');
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
                "$this->config->cookie->name" => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {

        return response()->json(['message' => 'Logged out'])
            ->cookie(
                $this->cookie['name'], // name
                '', // value
                -1, // expire
                $this->cookie['path'], // path
                $this->cookie['domain'], //localhost // domain
                $this->cookie['secure'], // secure
                $this->cookie['HttpOnly'], // HttpOnly ✅
                $this->cookie['raw'], // raw
                $this->cookie['SameSite'] // SameSite
            )
            ->cookie(
                $this->cookie['name'], // name
                '', // value
                -1, // expire
                $this->cookie['path'], // path
                $this->cookie['domain'], //localhost // domain
                $this->cookie['secure'], // secure
                $this->cookie['HttpOnly'], // HttpOnly ✅
                $this->cookie['raw'], // raw
                $this->cookie['SameSite'] // SameSite
            );
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refreshToken');
        if (!$refreshToken) {
            return response()->json(['message' => 'No refresh token'], 401);
        }
        try {

            $payload = JWTAuth::setToken($refreshToken)->getPayload();
            if ($payload->get('type') !== 'refresh') {
                throw new \Exception('Invalid token');
            }
            $userId = $payload->get('sub');
            $newAccessToken = JWTAuth::fromUser(User::find($userId));
            return response()
                ->json(['status' => 'success'])
                ->cookie(
                    Cookie(
                        $this->cookie['name'],
                        $newAccessToken,
                        $this->cookie['expire'],
                        $this->cookie['path'], 
                        $this->cookie['domain'],
                        $this->cookie['secure'],
                        $this->cookie['HttpOnly'],
                        $this->cookie['raw'],
                        $this->cookie['SameSite']
                    )
                );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Refresh failed'], 401);
        }
    }
}
