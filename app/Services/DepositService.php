<?php

namespace App\Services;

use App\Contracts\ExternalAuthServiceInterface;
use App\Exceptions\ServiceException;
use App\Models\Deposit;
use App\Models\User;
use App\Models\UserType;

class DepositService
{
    private $userModel;
    private $userTypeModel;
    private $depositModel;

    private $externalAuthService;

    public function __construct(
        User $userModel,
        UserType $userTypeModel,
        Deposit $depositModel,
        ExternalAuthServiceInterface $externalAuthService
    ) {
        $this->userModel = $userModel;
        $this->userTypeModel = $userTypeModel;
        $this->depositModel = $depositModel;
        $this->externalAuthService = $externalAuthService;
    }

    public function createDeposit(array $depositData, array $loggedUser): array
    {
        $depositToInsert = $this->getDepositToInsert(
            $depositData,
            $loggedUser
        );

        $deposit = $this->depositModel->createDeposit($depositToInsert);
        $updatedUser = $this->userModel->incrementUserWallet(
            $loggedUser['id'],
            $depositData['value']
        );

        $authorization = $this->externalAuthService->getExternalAuth();
        if (!$authorization) {
            throw new ServiceException(
                'Authorization error',
                500
            );
        }

        return [
            'deposit' => $deposit,
            'user' => $updatedUser
        ];
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

    public function createWithdraw(array $withdrawData, array $loggedUser): array
    {
        $withdrawToInsert = $this->getWithdrawToInsert(
            $withdrawData,
            $loggedUser
        );

        $withdraw = $this->depositModel->createWithdraw(
            $withdrawToInsert
        );

        $updatedUser = $this->userModel->decrementUserWallet(
            $loggedUser['id'],
            $withdrawData['value']
        );

        return [
            'withdraw' => $withdraw,
            'user' => $updatedUser
        ];
    }

    public function getWithdrawToInsert(array $withdrawData, array $user): array
    {
        $usualType = $this->userTypeModel->getUsualType();
        $merchantType = $this->userTypeModel->getMerchantType();

        if ($user['user_type_id'] != $usualType->id && $user['user_type_id'] != $merchantType->id) {
            throw new ServiceException(
                'Only usual and merchant users can make withdraws',
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
            'value' => -$withdrawValue,
        ];

        return $withdrawToInsert;
    }
}