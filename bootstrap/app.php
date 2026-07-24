<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Semua request /api/* dipaksa mengembalikan JSON (mencegah redirect ke
        // route 'login' saat autentikasi gagal pada request tanpa header Accept).
        $middleware->prependToGroup('api', \App\Http\Middleware\ForceJsonResponse::class);

        $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Paksa semua request ke /api/* dianggap "ingin JSON". Tanpa ini, request
        // tanpa header "Accept: application/json" yang gagal autentikasi akan
        // dialihkan (redirect) ke route 'login' yang tidak ada di API → error 500.
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (
            ModelNotFoundException $e,
            $request
        ) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Data tidak ditemukan'
            ], 404);
        });

        $exceptions->render(function (
            NotFoundHttpException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'Endpoint tidak ditemukan'
                ], 404);
            }
        });

        $exceptions->render(function (
            ValidationException $e,
            $request
        ) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'Validasi gagal. Periksa kembali isian Anda.',
                'errors' => $e->errors()
            ], 422);
        });

        // Belum login / token tidak valid → 401
        $exceptions->render(function (
            AuthenticationException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                // Bedakan: tidak ada token sama sekali vs token ada tapi tidak valid.
                $message = $request->bearerToken()
                    ? 'Token tidak valid atau sudah kedaluwarsa. Silakan login kembali.'
                    : 'Anda harus login terlebih dahulu untuk mengakses data ini.';

                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => $message
                ], 401);
            }
        });

        // Akses tanpa hak (policy / authorize / abort(403)) → 403
        $exceptions->render(function (
            AccessDeniedHttpException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'code' => 403,
                    'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.'
                ], 403);
            }
        });

        // Salah metode HTTP (mis. GET ke endpoint POST) → 405
        $exceptions->render(function (
            MethodNotAllowedHttpException $e,
            $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'code' => 405,
                    'message' => 'Metode HTTP tidak diizinkan untuk endpoint ini.'
                ], 405);
            }
        });

        // Jaring pengaman terakhir untuk error tak terduga → 500
        // Hanya aktif saat APP_DEBUG=false agar saat ngoding error tetap terlihat.
        // PENTING: handler Throwable ini harus tetap berada PALING BAWAH.
        $exceptions->render(function (
            Throwable $e,
            $request
        ) {
            if ($request->is('api/*') && ! config('app.debug')) {
                return response()->json([
                    'success' => false,
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.'
                ], 500);
            }
        });

    })->create();
