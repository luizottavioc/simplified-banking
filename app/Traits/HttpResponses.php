<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

trait HttpResponses
{
    /**
     * Return a default success response from the application.
     *
     * @param  string  $message custom message to be returned
     * @param  string|int  $statusCode request status code
     * @param  Illuminate\Database\Eloquent\Model|Illuminate\Http\Resources\Json\JsonResource|array $data data to be returned
     *
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function response(string $message, string|int $statusCode, Model|JsonResource|array $data = [])
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a default error response from the application.
     *
     * @param  string  $message custom message to be returned
     * @param  string|int  $statusCode request status code
     * @param  array $errors errors to be returned
     *
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function error(string $message, string|int $statusCode, array $errors = [])
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public function respondWithToken($token, $userData)
    {
        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => auth()->factory()->getTTL() * 60, // 60 minutes
                'user' => $userData
            ]
        ], 200);
    }
}
