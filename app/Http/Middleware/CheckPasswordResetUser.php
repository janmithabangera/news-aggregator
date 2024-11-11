<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPasswordResetUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && $request->email !== Auth::user()->email) {
            return response()->json([
                'message' => 'You can only reset your own password',
                'status' => 403
            ], 403);
        }

        return $next($request);
    }
}
