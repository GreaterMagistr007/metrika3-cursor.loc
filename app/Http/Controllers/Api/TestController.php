<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class TestController extends Controller
{
    /**
     * Get current OTP for testing purposes only
     */
    public function getCurrentOtp(): JsonResponse
    {
        $phone = '+71234567890';
        $otp = Cache::get("auth_otp:{$phone}");
        
        return response()->json([
            'phone' => $phone,
            'otp' => $otp,
            'exists' => $otp !== null,
            'message' => $otp ? 'OTP найден' : 'OTP не найден'
        ]);
    }
}
