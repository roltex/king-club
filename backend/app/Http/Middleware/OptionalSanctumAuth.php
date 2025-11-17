<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalSanctumAuth
{
    /**
     * Handle an incoming request.
     * Attempts to authenticate with Sanctum but doesn't fail if no token is present.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if there's a Bearer token
        $token = $request->bearerToken();
        
        if ($token) {
            // Attempt to authenticate with Sanctum
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->can('*')) {
                // Set the authenticated user
                $request->setUserResolver(function () use ($accessToken) {
                    return $accessToken->tokenable;
                });
                
                // Set the auth guard
                auth()->shouldUse('sanctum');
            }
        }
        
        return $next($request);
    }
}

