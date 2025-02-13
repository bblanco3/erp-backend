<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ValidateJwtToken
{
    public function handle(Request $request, Closure $next)
    {
        // Check for the Authorization header
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Authorization token not provided'], 401);
        }

        // Extract the JWT token
        $token = substr($authHeader, 7);

        try {
            // Decode the token (replace 'your-secret-key' with your actual secret key)
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));

            // Validate the structure of the token
            if (!isset($decoded->current_tenant) || !isset($decoded->current_user_profile) || !isset($decoded->tenants)) {
                return response()->json(['message' => 'Invalid token structure'], 401);
            }

            // Attach the token details to the request
            $request->merge([
                'current_tenant' => $decoded->current_tenant,
                'current_user_profile' => $decoded->current_user_profile,
                'tenants' => $decoded->tenants,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
