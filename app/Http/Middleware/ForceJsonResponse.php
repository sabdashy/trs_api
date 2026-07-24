<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Paksa setiap request ke API dianggap menginginkan respons JSON.
     *
     * Tanpa ini, request tanpa header "Accept: application/json" yang gagal
     * autentikasi akan diperlakukan sebagai request web biasa, sehingga Laravel
     * mencoba redirect ke route 'login' (yang tidak ada di API) dan memunculkan
     * error 500 "Route [login] not defined". Dengan memaksa header Accept,
     * expectsJson() selalu true sehingga error dikembalikan sebagai JSON 401.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
