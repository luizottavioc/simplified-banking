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

    public function register(RegisterUserRequest $request)
    {
        try {
            $userData = $request->validated();

            $hasCpf = !empty($userData['cpf']);
            $hasCnpj = !empty($userData['cnpj']);

            if ($hasCpf && $hasCnpj) {
                return $this->error('Error on create user', 422, ['cpf and cnpj cannot be used at the same time']);
            }

            $userTypeModel = new UserType();
            $userType = $hasCnpj ? $userTypeModel->getMerchantType() : $userTypeModel->getUsualType();

            $userInsert = [
                ...$userData,
                'user_type_id' => $userType->id,
            ];

            $userModel = new User();
            $user = $userModel->registerUser($userInsert);

            $token = auth()->login($user);
            $userTreated = new UserResource($user);

            return $this->respondWithToken($token, $userTreated);
        } catch (\Throwable $th) {
            return $this->error('Error on create user', 500, [$th->getMessage()]);
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

    // public function forgotPassword(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), ['email' => 'required|email']);

    //         if ($validator->fails()) {
    //             return $this->error('Error on validate email', 400, [$validator->errors()]);
    //         }

    //         $userModel = new User();
    //         $email = $validator->validated()['email'];
    //         $user = $userModel->getUserByEmail($email);

    //         if (!$user) {
    //             return $this->error('User not found', 404);
    //         }

    //         $forgotPasswordModel = new UserForgotPassword();
    //         $forgotPassword = $forgotPasswordModel->createForgotPassword($user->id);

    //         if (is_int($forgotPassword)) {
    //             return $this->error('Error on create forgot password token', 406, [
    //                 'User cannot have more than two valid tokens at the same time'
    //             ]);
    //         }

    //         if (!$forgotPassword) {
    //             return $this->error('Error on create forgot password token', 500);
    //         }

    //         //send e-mail
    //         //$forgotPassword->token

    //         return $this->response('The e-mail with the token was sent', 200);
    //     } catch (\Throwable $th) {
    //         return $this->error('Error on forgot password action', 500, [$th->getMessage()]);
    //     }
    // }

    // public function resetPassword(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'email' => 'required|email',
    //             'token' => 'required|string',
    //             'password' => 'required|string|min:8|max:100'
    //         ]);

    //         if ($validator->fails()) return $this->error('Error on validate data', 400, [$validator->errors()]);

    //         $userModel = new User();
    //         $email = $validator->validated()['email'];
    //         $user = $userModel->getUserByEmail($email);

    //         if (!$user) {
    //             return $this->error('User not found', 404);
    //         }

    //         $forgotPasswordModel = new UserForgotPassword();
    //         $token = $validator->validated()['token'];
    //         $verifyToken = $forgotPasswordModel->verifyToken($user->id, $token);

    //         if (!$verifyToken) {
    //             return $this->error('Token not found or expired', 404);
    //         }

    //         $setUseToken = $forgotPasswordModel->useToken($token);

    //         if (!$setUseToken) {
    //             return $this->error('Error on use token', 500);
    //         }

    //         $newPassword = $validator->validated()['password'];
    //         $updatePassword = $userModel->updateUserPassword($user->id, $newPassword);

    //         if (!$updatePassword) {
    //             return $this->error('Error on update password', 500);
    //         }

    //         return $this->response('Password changed successfully', 200, [
    //             'user' => new UserResource($updatePassword)
    //         ]);
    //     } catch (\Throwable $th) {
    //         return $this->error('Error on reset password action', 500, [$th->getMessage()]);
    //     }
    // }

    // public function verifyToken()
    // {
    //     try {
    //         $user = auth()->user();
    //         if (!$user) {
    //             return $this->error('User not found', 404);
    //         }

    //         $treatedUser = new UserResource($user);

    //         $payload = auth()->payload();
    //         $expiresIn = $payload->get('exp') - time();

    //         return $this->response('Token is valid', 200, [
    //             'user' => $treatedUser,
    //             'expires_in' => $expiresIn
    //         ]);
    //     } catch (\Throwable $th) {
    //         return $this->error('Error on verify token', 500, [$th->getMessage()]);
    //     }
    // }

    // /**
    //  * Get the authenticated User.
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function me()
    // {
    //     $user = auth()->user();
    //     if (!$user) {
    //         return $this->error('User not found', 404);
    //     }

    //     $treatedUser = new UserResource($user);
    //     return $this->response('User data', 200, $treatedUser);
    // }

    // /**
    //  * Refresh a token.
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function refresh()
    // {
    //     $tokenRefresh = auth()->refresh();
    //     if (!$tokenRefresh) {
    //         return $this->error('Error on refresh token', 500);
    //     }

    //     $user = auth()->user();
    //     $treatedUser = new UserResource($user);

    //     return $this->respondWithToken($tokenRefresh, $treatedUser);
    // }

    // /**
    //  * Log the user out (Invalidate the token).
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    // public function logout()
    // {
    //     auth()->logout(true);
    //     return $this->response('Successfully logged out', 200);
    // }
}
