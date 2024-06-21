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
     * @param  \Illuminate\Database\Eloquent\Model|\Illuminate\Http\Resources\Json\JsonResource|array $data data to be returned
     *
     * @return \Illuminate\Http\JsonResponse
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function error(string $message, string|int $statusCode, array $errors = [])
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    /**
     * Return a default user auth with token
     *
     * @param  string  $token token to be returned
     * @param  JsonResource $userData user data to be returned
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken(string $token, JsonResource $userData)
    {
        $dataToken = [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $userData
        ];

        return response()->json(['data' => $dataToken], 200);
    }
}
