<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Models\UserType;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use HttpResponses;

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $userData = $request->validated();

            $hasCpf = !empty($userData['cpf']);
            $hasCnpj = !empty($userData['cnpj']);

            if(!$hasCpf && !$hasCnpj) {
                return $this->error(
                    'Error on create user',
                    422,
                    ['cpf or cnpj is required']
                );
            }

            if ($hasCpf && $hasCnpj) {
                return $this->error(
                    'Error on create user',
                    422,
                    ['cpf and cnpj cannot be used at the same time']
                );
            }

            $userTypeModel = new UserType();
            $userType = $hasCnpj ?
                $userTypeModel->getMerchantType() :
                $userTypeModel->getUsualType();

            $userInsert = [
                ...$userData,
                'user_type_id' => $userType->id,
            ];

            $userModel = new User();
            $user = $userModel->registerUser($userInsert);

            $token = auth()->tokenById($user->id);
            $userTreated = new UserResource($user);

            return $this->respondWithToken($token, $userTreated, 201);
        } catch (\Throwable $th) {
            return $this->error('Unexpected error on create user!', 500);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $token = auth()->attempt($credentials);

        if (!$token) {
            return $this->error('Unauthorized', 401);
        }

        $user = auth()->user();
        $treatedUser = new UserResource($user);

        return $this->respondWithToken($token, $treatedUser);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMe()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->error('User not found', 404);
        }

        $treatedUser = new UserResource($user);
        return $this->response('User data', 200, $treatedUser);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return $this->response('Successfully logged out', 200);
    }
}
