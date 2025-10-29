<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.register_success'),
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * Đăng nhập sử dụng OAuth2 Password Grant
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.login_failed'),
            ], 401);
        }

        // Sử dụng OAuth2 Password Grant
        $http = new \GuzzleHttp\Client();
        
        try {
            $response = $http->post(config('app.url') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('passport.password_grant_client.id'),
                    'client_secret' => config('passport.password_grant_client.secret'),
                    'username' => $request->email,
                    'password' => $request->password,
                    'scope' => '*',
                ],
            ]);

            $tokenData = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'message' => __('messages.auth.login_success'),
                'data' => [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'token_type' => $tokenData['token_type'],
                    'expires_in' => $tokenData['expires_in'],
                ],
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $error = json_decode($e->getResponse()->getBody(), true);
            
            return response()->json([
                'success' => false,
                'message' => $error['message'] ?? __('messages.auth.login_failed'),
            ], 401);
        }
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Refresh token sử dụng OAuth2
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');
        
        if (!$refreshToken) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.refresh_token_required'),
            ], 400);
        }

        $http = new \GuzzleHttp\Client();
        
        try {
            $response = $http->post(config('app.url') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => config('passport.password_grant_client.id'),
                    'client_secret' => config('passport.password_grant_client.secret'),
                    'refresh_token' => $refreshToken,
                    'scope' => '*',
                ],
            ]);

            $tokenData = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'message' => __('messages.auth.refresh_success'),
                'data' => [
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'token_type' => $tokenData['token_type'],
                    'expires_in' => $tokenData['expires_in'],
                ],
            ]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $error = json_decode($e->getResponse()->getBody(), true);
            
            return response()->json([
                'success' => false,
                'message' => $error['message'] ?? __('messages.auth.refresh_failed'),
            ], 401);
        }
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.logout_success'),
        ]);
    }
}