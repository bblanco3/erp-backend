<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware extends BaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Get token from cookie instead of Authorization header
            $token = $request->cookie('jwt');
            if (!$token) {
                return response()->json(['error' => 'Token not found in cookie'], 401);
            }

            // Manually set token for JWTAuth since we're not using headers
            JWTAuth::setToken($token);
            
            // Authenticate the token
            $user = JWTAuth::authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        } catch (Exception $e) {
            return response()->json(['error' => 'Could not authenticate token'], 401);
        }

        return $next($request);
    }
}
