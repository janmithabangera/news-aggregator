<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private array $headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'X-Content-Type-Options' => 'nosniff',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (app()->environment('production')) {
            // Strict production headers
            $this->headers['Content-Security-Policy'] = "default-src 'self'";
            $this->headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
            $this->headers['Access-Control-Allow-Origin'] = config('app.frontend_url', '*');
            $this->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, DELETE';
            $this->headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization';
        } else {
            // Development-friendly headers
            $this->headers['Content-Security-Policy'] = "default-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*";
            $this->headers['Access-Control-Allow-Origin'] = '*';
            $this->headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, PATCH, DELETE, OPTIONS';
            $this->headers['Access-Control-Allow-Headers'] = 'X-Requested-With, Content-Type, Authorization, X-XSRF-TOKEN';
            $this->headers['Access-Control-Allow-Credentials'] = 'true';
        }

        foreach ($this->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
