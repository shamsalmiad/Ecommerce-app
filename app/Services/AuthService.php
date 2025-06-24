<?php

namespace App\Services;

use App\Enums\TokenAbility;

class AuthService
{
    public static function generateTokens($user): array
    {
        $atExpireTime = now()->addMinutes((int) config('sanctum.ac_expiration',60));
        $rtExpireTime = now()->addMinutes((int) config('sanctum.rt_expiration',120));

        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], $atExpireTime);
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], $rtExpireTime);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
        ];
    }
}
