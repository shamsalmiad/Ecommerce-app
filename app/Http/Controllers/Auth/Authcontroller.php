<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Authentication\UserBasicResource;
use App\Http\Resources\User\UserResource;
use App\Models\Authentication\LoginHistory;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|unique:users,email,except,id',
            'password' => 'required|min:6|max:20',
        ]);

        User::create($validated);

        return response()->json([
            'status' => 'Success',
            'message' => 'User registered successfully',
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        if (Auth::attempt($credentials)) {
            $user = $request->user();

            // Generate Tokens
            $tokens = AuthService::generateTokens($user);

            $user = new UserResource($user);

            $userData = array_merge(
                ['user' => $user->toArray($request)],
                [
                    'tokens' => [
                        'accessToken' => $tokens['accessToken'],
                        'refreshToken' => $tokens['refreshToken'],
                    ],
                ]
            );
            return ApiResponseService::success($userData, 'Login successful!');
        } else {
            return ApiResponseService::error('Invalid login credentials!', [], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->tokens()->delete();
        }

        return ApiResponseService::success([], 'Successfully logged out!');
    }

    public function refreshToken(Request $request)
    {
        if (Auth::check()) {
            $request->user()->currentAccessToken()->delete();
        }
        $accessToken = $request->user()->createToken(
            'access_token',
            [TokenAbility::ACCESS_API->value],
            now()->addMinutes((int) config('sanctum.ac_expiration'))
        );

        return ApiResponseService::success(['accessToken' => $accessToken->plainTextToken], 'Token refreshed');
    }
}
