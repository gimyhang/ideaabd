<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaptchaController extends Controller
{
    /**
     * Generate a new Image CAPTCHA challenge.
     */
    public function generate(Request $request, CaptchaService $captchaService): JsonResponse
    {
        $challenge = $captchaService->generate($request->ip());

        return response()->json([
            'success'    => true,
            'token'      => $challenge['token'],
            'image'      => $challenge['image'],
            'expires_in' => $challenge['expires_in'],
        ]);
    }

    /**
     * Verify user submitted CAPTCHA.
     */
    public function verify(Request $request, CaptchaService $captchaService): JsonResponse
    {
        $request->validate([
            'captcha_token' => ['required', 'string'],
            'captcha_code'  => ['required', 'string', 'min:1', 'max:20'],
        ]);

        $result = $captchaService->verify(
            (string) $request->input('captcha_token'),
            (string) $request->input('captcha_code'),
            $request->ip()
        );

        if (!$result['success']) {
            // Include a fresh CAPTCHA automatically on failure
            $freshChallenge = $captchaService->generate($request->ip());

            return response()->json([
                'success'     => false,
                'message'     => $result['message'],
                'fresh_token' => $freshChallenge['token'],
                'fresh_image' => $freshChallenge['image'],
            ], 422);
        }

        return response()->json([
            'success'     => true,
            'message'     => $result['message'],
            'proof_token' => $result['proof_token'] ?? null,
        ]);
    }
}
