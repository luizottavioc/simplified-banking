<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\User;
use App\Models\UserType;

class TransferService
{
    private $userTypeModel = null;

    public function __construct($userTypeModel = UserType::class)
    {
        $this->userTypeModel = new $userTypeModel();
    }

    public function getTransferToInsert(array $transferData, array $userPayer, array $userPayee): array
    {
        $usualType = $this->userTypeModel->getUsualType();
        if ($userPayer['user_type_id'] != $usualType->id) {
            throw new ServiceException(
                'Only usual users can make transfers',
                403
            );
        }

        $merchantType = $this->userTypeModel->getMerchantType();
        if (
            $userPayee['user_type_id'] != $usualType->id &&
            $userPayee['user_type_id'] != $merchantType->id
        ) {
            throw new ServiceException(
                'Only merchant and usual users can receive transfers',
            );
        }

        if ($userPayer['id'] == $userPayee['id']) {
            throw new ServiceException(
                'Cannot transfer to yourself',
                422
            );
        }

        $transferValue = $transferData['value'];

        $valueIsInt = is_int($transferValue);
        if (!$valueIsInt) {
            throw new ServiceException(
                'Value must be an integer',
                422
            );
        }

        if ($transferValue <= 0) {
            throw new ServiceException(
                'Value must be greater than zero',
                422
            );
        }

        if ($userPayer['wallet'] < $transferValue) {
            throw new ServiceException(
                'Insufficient funds in wallet',
                422
            );
        }

        return [
            'payer_id' => $userPayer['id'],
            'payee_id' => $userPayee['id'],
            'value' => $transferValue,
        ];
    }
}