<?php
namespace App\Models\Auth;

use Laravel\Sanctum\PersonalAccessToken as SanctumToken;

class PersonalAccessToken extends SanctumToken
{
    // Now it has findToken() and tokenable()
}
