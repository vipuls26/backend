<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            Log::warning('API request missing bearer token', [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ], 401);
        }

        $accessToken = AccessToken::query()
            ->with('user')
            ->where('token', hash('sha256', $plainToken))
            ->first();

        if (! $accessToken || ! $accessToken->user) {
            Log::warning('API request used invalid bearer token', [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ], 401);
        }

        $accessToken->forceFill(['last_used_at' => now()])->save();
        Auth::setUser($accessToken->user);

        return $next($request);
    }
}
