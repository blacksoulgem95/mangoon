<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OauthAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_root' => $user->is_root,
            ],
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function me()
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }

    public function logout()
    {
        Auth::logout();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'token' => Auth::refresh(),
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function oauthRedirect(string $provider)
    {
        try {
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'OAuth provider not configured',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function oauthCallback(string $provider)
    {
        try {
            $socialiteUser = Socialite::driver($provider)->user();

            $existingAccount = OauthAccount::where('provider', $provider)
                ->where('provider_user_id', $socialiteUser->getId())
                ->first();

            if ($existingAccount) {
                $user = $existingAccount->user;
            } else {
                $user = User::where('email', $socialiteUser->getEmail())->first();

                if (!$user) {
                    $user = User::create([
                        'name' => $socialiteUser->getName() ?? $socialiteUser->getNickname(),
                        'email' => $socialiteUser->getEmail(),
                        'password' => Hash::generate(random_bytes(32)),
                    ]);
                }

                OauthAccount::create([
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'provider_user_id' => $socialiteUser->getId(),
                    'token' => $socialiteUser->token,
                    'refresh_token' => $socialiteUser->refreshToken,
                    'expires_at' => $socialiteUser->expiresIn ? now()->addSeconds($socialiteUser->expiresIn) : null,
                ]);
            }

            $token = Auth::login($user);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_root' => $user->is_root,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'linked' => !$existingAccount,
            ]);

        } catch (\Exception $e) {
            Log::error('OAuth callback error', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'OAuth authentication failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
