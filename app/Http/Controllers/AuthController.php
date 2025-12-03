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
            $user = $this->authService->register($request->validated());

            return response()->json([
                'success' => true,
                'message' => __('auth.register_success'),
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.register_failed'),
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
                'message' => __('auth.login_success'),
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
                'message' => __('auth.login_failed'),
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
                    'message' => __('auth.user_not_found'),
                ], 401);
            }

            $this->authService->logout($user);

            return response()->json([
                'success' => true,
                'message' => __('auth.logout_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.login_failed'),
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
                    'message' => __('auth.user_not_found'),
                ], 401);
            }

            $this->authService->logoutAll($user);

            return response()->json([
                'success' => true,
                'message' => __('auth.logout_all_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.login_failed'),
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
                    'message' => __('auth.user_not_found'),
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('common.server_error'),
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
                    'message' => __('auth.user_not_found'),
                ], 401);
            }

            $updatedUser = $this->authService->updateProfile($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => __('auth.profile_updated'),
                'data' => $updatedUser,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.profile_update_failed'),
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
                    'message' => __('auth.user_not_found'),
                ], 401);
            }

            $this->authService->changePassword(
                $user,
                $request->validated()['current_password'],
                $request->validated()['new_password']
            );

            return response()->json([
                'success' => true,
                'message' => __('auth.password_changed'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.password_change_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.password_change_failed'),
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
                    'message' => __('auth.password_reset_link_sent'),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('auth.password_reset_link_failed'),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.password_reset_link_failed'),
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
                'message' => __('auth.password_reset_success'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.password_reset_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.password_reset_failed'),
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
                'message' => __('auth.email_verified_success'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.email_verification_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.email_verification_failed'),
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
                    'message' => __('auth.user_not_found'),
                ], 401);
            }

            $this->authService->resendVerificationEmail($user);

            return response()->json([
                'success' => true,
                'message' => __('auth.verification_email_sent'),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.verification_email_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('auth.verification_email_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
