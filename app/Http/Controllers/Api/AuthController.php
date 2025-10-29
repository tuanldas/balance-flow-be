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
     * Đăng nhập
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

        $user = Auth::user();
        
        // Tạo token với refresh token
        $tokenResult = $user->createToken('auth-token');
        $token = $tokenResult->accessToken;
        $refreshToken = $tokenResult->refreshToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.login_success'),
            'data' => [
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $tokenResult->token->expires_at->diffInSeconds(now()),
            ],
        ]);
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
     * Refresh token
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

        try {
            // Tìm refresh token trong database
            $token = \Laravel\Passport\RefreshToken::where('id', $refreshToken)->first();
            
            if (!$token || $token->revoked) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.auth.refresh_token_invalid'),
                ], 401);
            }

            // Kiểm tra token hết hạn
            if ($token->expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.auth.refresh_token_expired'),
                ], 401);
            }

            $user = $token->accessToken->user;
            
            // Revoke old tokens
            $token->accessToken->revoke();
            $token->revoke();

            // Tạo token mới
            $tokenResult = $user->createToken('auth-token');
            $newAccessToken = $tokenResult->accessToken;
            $newRefreshToken = $tokenResult->refreshToken;

            return response()->json([
                'success' => true,
                'message' => __('messages.auth.refresh_success'),
                'data' => [
                    'access_token' => $newAccessToken,
                    'refresh_token' => $newRefreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $tokenResult->token->expires_at->diffInSeconds(now()),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.refresh_failed'),
            ], 500);
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