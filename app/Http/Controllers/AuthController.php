<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công. Vui lòng kiểm tra email để xác thực tài khoản.',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng ký tài khoản thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công.',
                'data' => [
                    'user' => $result['user'],
                    'access_token' => $result['token'],
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng nhập thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 401);
            }

            $this->authService->logout($user);

            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng xuất thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user from all devices
     */
    public function logoutAll(): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 401);
            }

            $this->authService->logoutAll($user);

            return response()->json([
                'success' => true,
                'message' => 'Đã đăng xuất khỏi tất cả thiết bị.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đăng xuất thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin người dùng.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 401);
            }

            $updatedUser = $this->authService->updateProfile($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công.',
                'data' => $updatedUser,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thông tin thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 401);
            }

            $this->authService->changePassword(
                $user,
                $request->validated()['current_password'],
                $request->validated()['new_password']
            );

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->sendPasswordResetLink($request->validated()['email']);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Link đặt lại mật khẩu đã được gửi đến email của bạn.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi link đặt lại mật khẩu.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi link đặt lại mật khẩu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password using token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->resetPassword($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đặt lại mật khẩu thất bại.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đặt lại mật khẩu thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify email address
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $this->authService->verifyEmail(
                $request->validated()['id'],
                $request->validated()['hash']
            );

            return response()->json([
                'success' => true,
                'message' => 'Email đã được xác thực thành công.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xác thực email thất bại.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xác thực email thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend email verification notification
     */
    public function resendVerificationEmail(): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng.',
                ], 401);
            }

            $this->authService->resendVerificationEmail($user);

            return response()->json([
                'success' => true,
                'message' => 'Email xác thực đã được gửi lại.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gửi email xác thực thất bại.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gửi email xác thực thất bại.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
