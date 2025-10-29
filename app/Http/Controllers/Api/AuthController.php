<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

        // Cấp token theo Password Grant ngay sau khi đăng ký
        $tokenPayload = $this->passwordGrantToken(
            username: $request->email,
            password: $request->password
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.register_success'),
            'data' => [
                'user' => $user,
                'access_token' => $tokenPayload['access_token'] ?? null,
                'refresh_token' => $tokenPayload['refresh_token'] ?? null,
                'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
                'expires_in' => $tokenPayload['expires_in'] ?? null,
            ],
        ], 201);
    }

    /**
     * Đăng nhập sử dụng OAuth2 Password Grant
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Lấy token theo Password Grant (chuẩn của Passport)
        $tokenPayload = $this->passwordGrantToken(
            username: $request->email,
            password: $request->password
        );

        if (!isset($tokenPayload['access_token'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.login_failed'),
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.login_success'),
            'data' => [
                'access_token' => $tokenPayload['access_token'],
                'refresh_token' => $tokenPayload['refresh_token'] ?? null,
                'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
                'expires_in' => $tokenPayload['expires_in'] ?? null,
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
     * Refresh token sử dụng Passport
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

        $tokenPayload = $this->refreshGrantToken($refreshToken);

        if (!isset($tokenPayload['access_token'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.refresh_failed'),
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.refresh_success'),
            'data' => [
                'access_token' => $tokenPayload['access_token'],
                'refresh_token' => $tokenPayload['refresh_token'] ?? null,
                'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
                'expires_in' => $tokenPayload['expires_in'] ?? null,
            ],
        ]);
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

    /**
     * Gọi token endpoint theo Password Grant.
     *
     * @return array<string, mixed>
     */
    private function passwordGrantToken(string $username, string $password): array
    {
        $clientId = (string)config('passport.password_client_id');
        $clientSecret = (string)config('passport.password_client_secret');

        if ($clientId === '' || $clientSecret === '') {
            abort(500, 'Passport password client is not configured.');
        }
        $baseUrl = config('oauth.server_url', config('app.url'));

        $a = rtrim($baseUrl, '/') . '/oauth/token';
        $response = Http::asForm()->post($a, [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $username,
            'password' => $password,
            'scope' => '',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Refresh access token theo chuẩn Password Grant.
     *
     * @return array<string, mixed>
     */
    private function refreshGrantToken(string $refreshToken): array
    {
        $clientId = (string)config('passport.password_client_id');
        $clientSecret = (string)config('passport.password_client_secret');

        if ($clientId === '' || $clientSecret === '') {
            abort(500, 'Passport password client is not configured.');
        }
        $baseUrl = config('oauth.server_url', config('app.url'));

        $a = rtrim($baseUrl, '/') . '/oauth/token';
        $response = Http::asForm()->post($a, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => '',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}
