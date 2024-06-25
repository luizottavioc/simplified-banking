<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
use App\Models\UserType;

class DepositService
{
    private $userTypeModel = null;

    public function __construct($userTypeModel = UserType::class)
    {
        $this->userTypeModel = new $userTypeModel();
    }

    public function getDepositToInsert(array $depositData, array $user): array
    {
        $usualType = $this->userTypeModel->getUsualType();
        if ($user['user_type_id'] != $usualType->id) {
            throw new ServiceException(
                'Only usual users can make deposits',
                403
            );
        }

        $depositValue = $depositData['value'];
        
        $valueIsInt = is_int($depositValue);
        if (!$valueIsInt) {
            throw new ServiceException(
                'Value must be an integer',
                422
            );
        }

        if ($depositValue <= 0) {
            throw new ServiceException(
                'Value must be greater than zero',
                422
            );
        }

        $depositToInsert = [
            'user_id' => $user['id'],
            'value' => $depositValue,
        ];

        return $depositToInsert;
    }

    public function getWithdrawToInsert(array $withdrawData, array $user): array 
    {
        $usualType = $this->userTypeModel->getUsualType();
        if ($user['user_type_id'] != $usualType->id) {
            throw new ServiceException(
                'Only usual users can make withdraws',
                403
            );
        }

        $withdrawValue = $withdrawData['value'];

        $valueIsInt = is_int($withdrawValue);
        if (!$valueIsInt) {
            throw new ServiceException(
                'Value must be an integer',
                422
            );
        }

        if ($withdrawValue <= 0) {
            throw new ServiceException(
                'Value must be greater than zero',
                422
            );
        }

        $userWallet = $user['wallet'];
        if ($userWallet < $withdrawValue) {
            throw new ServiceException(
                'User does not have enough money in wallet',
                422
            );
        }

        $withdrawToInsert = [
            'user_id' => $user['id'],
            'value' => - $withdrawValue,
        ];

        return $withdrawToInsert;
    }
}