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

        return $req->status() === 200;
    }

    public function sendExternalNotification(): bool
    {
        $req = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post('https://util.devi.tools/api/v1/notify');

        return $req->status() === 204;
    }
}