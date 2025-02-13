<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $token = $request->cookie('jwt');

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $payload = JWT::decode(
                $token, 
                new Key(config('jwt.secret'), 'HS256')
            );

            // Check if token is expired
            if ($payload->exp < time()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token has expired'
                ], 401);
            }

            // Get user and set authentication
            $user = User::find($payload->sub);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 401);
            }

            Auth::login($user);
            
            return $next($request);

        } catch (ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token'
            ], 401);
        }
    }
}
