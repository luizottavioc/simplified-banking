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
                422
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

        $depositToInsert = [
            'user_id' => $userTo['id'],
            'teller_id' => $teller['id'],
            'value' => $depositData['value'],
        ];

        return $depositToInsert;
    }
}