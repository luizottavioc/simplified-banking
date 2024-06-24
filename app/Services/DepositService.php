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

    public function getDepositToInsert(array $depositData, array $teller, array $userTo): array
    {
        $tellerType = $this->userTypeModel->getTellerType();
        if ($teller['user_type_id'] != $tellerType->id) {
            throw new ServiceException(
                'Only tellers can create deposits',
                403
            );
        }

        $usualType = $this->userTypeModel->getUsualType();
        if ($userTo['user_type_id'] != $usualType->id) {
            throw new ServiceException(
                'Only usual users can receive deposits',
                422
            );
        }

        $valueIsInt = is_int($depositData['value']);
        if (!$valueIsInt) {
            throw new ServiceException(
                'Value must be an integer',
                422
            );
        }

        if ($depositData['value'] <= 0) {
            throw new ServiceException(
                'Value must be greater than zero',
                422
            );
        }

        $depositToInsert = [
            'user_id' => $userTo['id'],
            'teller_id' => $teller['id'],
            'value' => $depositData['value'],
        ];

        return $depositToInsert;
    }

    public function getWithdrawToInsert(array $withdrawData, array $user): array 
    {
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