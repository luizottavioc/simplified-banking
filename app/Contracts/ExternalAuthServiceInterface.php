<?php

namespace App\Contracts;

interface ExternalAuthServiceInterface
{
    public function getExternalAuth(): bool;
}