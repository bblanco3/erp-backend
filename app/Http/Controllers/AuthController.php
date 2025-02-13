<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Services\AuthenticationService;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login user and create JWT token
     */
    public function login(Request $request)
    {
        try {

            // Validate login credentials
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Attempt authentication
            $result = $this->authService->attemptLogin($credentials);
            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], $result['status']);
            }

            // Generate JWT token and decode payload in one go
            $token = $this->createJwtToken($result['account']);
            $payload = json_decode(base64_decode(explode('.', $token)[1]));

            // Create the response
            $response = response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'token' => $token
            ]);

            // Set cookie
            $response->headers->setCookie($this->setCookieForDomain($token));

            // Set CORS headers
            $origin = $request->header('Origin');
            if ($origin) {
                $response->header('Access-Control-Allow-Origin', $origin);
                $response->header('Access-Control-Allow-Credentials', 'true');
            }

            return $response;

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function setCookieForDomain($token)
    {
        $domain = request()->getHost();
        // For localhost development, use localhost without the dot prefix
        if ($domain === 'localhost' || str_ends_with($domain, '.localhost')) {
            $cookieDomain = 'localhost';
        } else {
            // For production, extract the base domain and prefix with dot
            $parts = explode('.', $domain);
            $baseDomain = count($parts) > 1 ? implode('.', array_slice($parts, -2)) : $domain;
            $cookieDomain = '.' . $baseDomain;
        }
        
        return cookie('jwt', $token, 60 * 24, // 24 hours
            '/', // path
            $cookieDomain, // domain
            request()->secure(), // secure
            true, // httpOnly
            false, // raw
            'Lax' // sameSite
        );
    }

    private function removeCookieForDomain($response, $domain)
    {
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie(
            'jwt',              // name
            '',                // empty value
            1,                // expire immediately
            '/',              // path
            $domain,          // domain
            false,            // secure
            true,            // httpOnly
            false,           // raw
            'Lax'            // sameSite
        ));
    }

    /**
     * Logout user and invalidate token
     */
    public function logout()
    {
        try {
            Auth::logout();
            
            // Get the request details
            $request = request();
            $protocol = $request->secure() ? 'https' : 'http';
            $port = $request->getPort();
            $defaultPorts = ['http' => 80, 'https' => 443];
            $portSuffix = ($port && $port !== $defaultPorts[$protocol]) ? ":$port" : '';
            
            // Get the host and determine if we're on localhost/IP
            $host = $request->getHost();
            $isLocalEnvironment = str_contains($host, 'localhost') || filter_var($host, FILTER_VALIDATE_IP);
            
            $response = response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);

            if ($isLocalEnvironment) {
                // For localhost/IP, remove cookie for the exact host
                $this->removeCookieForDomain($response, $host);
            } else {
                // Get the root domain for production
                $parts = explode('.', $host);
                $rootDomain = implode('.', array_slice($parts, -2));
                
                // Remove cookie from root domain
                $this->removeCookieForDomain($response, $rootDomain);
                
                // Remove cookie from current subdomain if it exists
                if (substr_count($host, '.') > 1) {
                    $this->removeCookieForDomain($response, $host);
                }
            }

            // Set CORS headers
            $origin = $request->header('Origin');
            if ($origin) {
                $response->header('Access-Control-Allow-Origin', $origin);
                $response->header('Access-Control-Allow-Credentials', 'true');
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create JWT token with comprehensive account, users, and tenant information
     */
    private function createJwtToken(Account $account): string
    {
        // Get all users with their tenants
        $user_list = $account->get_users()->where('is_active', true)->with('tenant')->get()->mapWithKeys(function ($user) {
            return [
                $user->id => [
                    'tenant_id' => $user->tenant->id,
                    'tenant_entity' => $user->tenant->entity,
                    'tenant_name' => $user->tenant->name
                ]
            ];
        })->toArray();

        // ensure current account has a contact
        $contact = $account->get_contacts()->first();
        if (!$contact) {
            throw new \Exception('No contact found for this account');
        }

        // ensure current account has a user
        $current_user = $account->get_current_user()->with('tenant')->first();
        if (!$current_user) {

            // get the first active user
            $current_user = $account->get_users()->where('is_active', true)->with('tenant')->first();
            if ($current_user) {
                // Update the account's default user
                $account->default_user_id = $current_user->id;
                $account->save();
            }

        }
        if (!$current_user) {
            throw new \Exception('No active users found for this account');
        }

        // ensure current user has a tenant
        $current_tenant = $current_user->tenant;
        if (!$current_tenant) {
            throw new \Exception('No tenant found for the current user');
        }

        // create JWT payload
        $payload = [
            // Account information
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'is_active' => $account->is_active,
            ],
            // Contact information
            'contact' => [
                'id' => $contact->id,
                'nickname' => $contact->nickname,
                'name' => $contact->name,
                'is_active' => $contact->is_active,
            ],
            // Current user and tenant information
            'current_user_id' => $current_user->id,
            // All users with their tenants
            'user_list' => $user_list,
            // JWT specific claims
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addMinutes(config('jwt.ttl'))->timestamp,
            'jti' => Str::random(16)
        ];

        return JWT::encode(
            $payload,
            config('jwt.secret'),
            'HS256'
        );
    }

    /**
     * Get authenticated user information
     */
    public function me(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            return response()->json([
                'status' => 'success',
                'user' => $user->fullInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's tenants.
     */
    public function user_tenants(Request $request)
    {
        try {
            $user = $request->user();
            $account = $user->account;
            
            // Get all tenants for this account's users with full tenant information
            $tenants = $account->get_users()
                ->join('tenants', 'users.tenant_id', '=', 'tenants.id')
                ->select('tenants.id', 'tenants.name', 'tenants.entity')
                ->distinct()
                ->get();

            return response()->json([
                'status' => 'success',
                'tenants' => $tenants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching tenants',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'password' => Hash::make(Str::random(24)),
                    'is_active' => true
                ]
            );

            // Login user
            Auth::login($user);
            
            // Create token
            $token = $this->createJwtToken($user->account);

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user->fullInfo
            ])->cookie(
                'jwt',           // name
                $token,         // value
                config('jwt.ttl'),  // minutes from config
                '/',           // path
                null,          // domain
                true,          // secure (HTTPS only)
                true,          // httpOnly
                true,          // raw
                'Strict'       // sameSite
            );

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error with Google authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify authentication
     */
    public function verify_auth(Request $request)
    {
        try {
            $token = $request->cookie('jwt');
            if (!$token) {
                return response()->json(['authenticated' => false], 401);
            }

            // Use JWTAuth to verify the token
            \Tymon\JWTAuth\Facades\JWTAuth::setToken($token);
            $user = \Tymon\JWTAuth\Facades\JWTAuth::authenticate();
            
            if (!$user) {
                return response()->json(['authenticated' => false], 401);
            }

            return response()->json([
                'authenticated' => true,
                'user' => $user->fullInfo
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['authenticated' => false, 'error' => 'Token has expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['authenticated' => false, 'error' => 'Token is invalid'], 401);
        } catch (\Exception $e) {
            return response()->json(['authenticated' => false, 'error' => $e->getMessage()], 401);
        }
    }
}