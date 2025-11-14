<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RefreshTokenRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\ResetPasswordRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService,
    ) {}

    /**
     * Đăng ký tài khoản mới
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(
            name: $request->name,
            email: $request->email,
            password: $request->password
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.register_success'),
            'data' => $result,
        ], 201);
    }

    /**
     * Đăng nhập sử dụng OAuth2 Password Grant
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            email: $request->email,
            password: $request->password
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.login_failed'),
            ], 401);
        }

        if (isset($result['requires_verification']) && $result['requires_verification'] === true) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.verification_required'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.login_success'),
            'data' => $result,
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
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        $result = $this->authService->refreshToken($request->input('refresh_token'));

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.refresh_failed'),
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.refresh_success'),
            'data' => $result,
        ]);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.logout_success'),
        ]);
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $ok = $this->authService->changePassword(
            user: $request->user(),
            currentPassword: $request->input('current_password'),
            newPassword: $request->input('new_password')
        );

        if (! $ok) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.incorrect_current_password'),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.change_password_success'),
        ]);
    }

    /**
     * Gửi email chứa liên kết đặt lại mật khẩu
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $ok = $this->authService->sendPasswordResetLink($request->input('email'));

        if (! $ok) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.forgot_send_failed'),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.forgot_send_success'),
        ]);
    }

    /**
     * Đặt lại mật khẩu bằng token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $ok = $this->authService->resetPassword(
            email: $request->input('email'),
            token: $request->input('token'),
            newPassword: $request->input('password')
        );

        if (! $ok) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.reset_failed'),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.reset_success'),
        ]);
    }
}
