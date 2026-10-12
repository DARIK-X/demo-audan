<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' https://images.unsplash.com; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
        if ($request->is('admin', 'admin/*')) $response->headers->set('Cache-Control', 'no-store, private');
        if ($request->isSecure() && app()->environment('production')) $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        return $response;
    }
}
