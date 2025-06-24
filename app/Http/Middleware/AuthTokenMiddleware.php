<?php
namespace App\Http\Middleware;

use App\Models\Auth\PersonalAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        // Pull token from ecommerce DB via auth_db
        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || ! $accessToken->tokenable) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Log in user manually
        Auth::login($accessToken->tokenable);

        return $next($request);
    }
}
