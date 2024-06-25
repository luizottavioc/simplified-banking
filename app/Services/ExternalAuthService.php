<?php

namespace App\Services;

use App\Contracts\ExternalAuthServiceInterface;
use Illuminate\Support\Facades\Http;

class ExternalAuthService implements ExternalAuthServiceInterface
{
    public function getExternalAuth(): bool
    {
        $req = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get('https://util.devi.tools/api/v2/authorize');

        $authorizationCode = $req->status();

        return $authorizationCode === 200;
    }
}