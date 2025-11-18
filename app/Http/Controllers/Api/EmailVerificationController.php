<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\EmailVerificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailVerificationController extends Controller
{
    public function __construct(
        private readonly EmailVerificationServiceInterface $verificationService,
    ) {
    }

    /**
     * Xác minh email qua URL đã ký.
     */
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        $ok = $this->verificationService->verifyBySignedUrl($id, $hash);

        if (!$ok) {
            return response()->json([
                'success' => false,
                'message' => __('messages.auth.verify_failed'),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.verify_success'),
        ]);
    }

    /**
     * Gửi lại email xác minh cho user đã đăng nhập.
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.auth.already_verified'),
            ]);
        }

        $this->verificationService->sendVerification($user);

        return response()->json([
            'success' => true,
            'message' => __('messages.auth.verification_sent'),
        ], 202);
    }
}


