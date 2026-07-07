<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Site açılmadan önce tüm siteyi tek bir şifreyle korur (HTTP Basic).
 * .env içindeki SITE_SIFRE boşsa kapı devre dışıdır.
 */
class SiteKapisi
{
    public function handle(Request $request, Closure $next): Response
    {
        $sifre = (string) config('app.site_sifre', '');

        // Şifre tanımlı değilse kapı kapalı değildir (herkes girebilir).
        if ($sifre === '') {
            return $next($request);
        }

        if (hash_equals($sifre, (string) $request->getPassword())) {
            return $next($request);
        }

        return response('Bu alan şifre ile korunmaktadır.', 401, [
            'WWW-Authenticate' => 'Basic realm="Yeni Müzayede"',
        ]);
    }
}
