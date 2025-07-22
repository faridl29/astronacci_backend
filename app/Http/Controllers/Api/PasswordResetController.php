<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found'
            ], 404);
        }

        $resetCode = sprintf('%06d', mt_rand(1, 999999));
        
        cache()->put('password_reset_' . $user->email, $resetCode, now()->addMinutes(15));

        return response()->json([
            'success' => true,
            'message' => 'Reset code sent successfully',
            'reset_code' => $resetCode,
            'email' => $request->email
        ]);
    }

    public function verifyResetCode(ResetPasswordRequest $request): JsonResponse
    {
        $cachedCode = cache()->get('password_reset_' . $request->email);
        
        if (!$cachedCode || $cachedCode !== $request->reset_code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        cache()->forget('password_reset_' . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}