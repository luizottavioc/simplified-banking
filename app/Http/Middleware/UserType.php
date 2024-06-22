<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\HttpResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserType as UserTypeModel;

class UserType
{
    use HttpResponses;

    private $userTypeModel = null;
    private $getFnUserTypeByName = [
        'admin' => 'getAdminType',
        'teller' => 'getTellerType',
        'merchant' => 'getMerchantType',
        'usual' => 'getUsualType',
    ];

    public function __construct() {
        $this->userTypeModel = new UserTypeModel();
    }

    public function verifyUserType(User $user, $type): bool {
        $fnGetUserType = $this->getFnUserTypeByName[$type];

        if (!isset($fnGetUserType)) {
            return false;
        }
 
        $type = $this->userTypeModel->$fnGetUserType();
        if (!$type) {
            return false;
        }

        if ($user->user_type_id != $type->id) {
            return false;
        }

        return true;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$types): Response
    {
        $user = $request->user();

        foreach ($types as $type) {
            if ($this->verifyUserType($user, $type)) {
                return $next($request);
            }
        }
        
        return $this->error('Unauthorized', 401);
    }
}
