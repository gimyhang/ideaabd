<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            // প্রডাকশন এনভায়রনমেন্টে ক্র্যাশ বা সার্ভার এরর হলে ডিসকর্ড চ্যানেলে এলার্ট যাবে
            if (app()->environment('production') && $this->shouldReport($e)) {
                try {
                    Log::channel('discord')->error($e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'url'  => request()->fullUrl(),
                    ]);
                } catch (Throwable $logException) {
                    // ডিসকর্ড নেটওয়ার্ক সমস্যা থাকলে ডিফল্ট লগে লিখবে
                    Log::error($e->getMessage());
                }
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        // JSON response formatting for API or AJAX requests
        if ($request->expectsJson() || $request->is('api/*')) {

            // ১. ভ্যালিডেশন এরর (422)
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors()
                ], 422);
            }

            // ২. আনঅথেন্টিকেটেড এরর (401)
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // ৩. অননুমোদিত অ্যাক্সেস / পারমিশন এরর (403)
            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'This action is unauthorized.'
                ], 403);
            }

            // ৪. মডেল বা রাউট না পাওয়ার এরর (404)
            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested resource or endpoint not found.'
                ], 404);
            }

            // ৫. সাধারণ সার্ভার এরর (500 বা অন্যান্য HTTP Code)
            $statusCode = $e instanceof HttpExceptionInterface 
                ? $e->getStatusCode() 
                : 500;

            $message = config('app.debug') 
                ? $e->getMessage() 
                : 'Server error. Please try again later.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $statusCode);
        }

        return parent::render($request, $e);
    }
}