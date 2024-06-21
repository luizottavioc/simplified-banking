<?php

namespace App\Http\Middleware;

use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class Jwt
{
    use HttpResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $th) {
            if ($th instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException)
                return $this->error('Token is Invalid', 401);

            if ($th instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException)
                return $this->error('Token is Expired', 401);

            if ($th instanceof \Tymon\JWTAuth\Exceptions\JWTException)
                return $this->error('Token is not provided', 401);

            return $this->error('Unauthorized', 401, [ 'Something went wrong' ]);
        }

        return $next($request);
    }
}
