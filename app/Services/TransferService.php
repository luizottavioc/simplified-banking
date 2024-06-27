<?php

namespace App\Services;

use App\Contracts\ExternalAuthServiceInterface;
use App\Exceptions\ServiceException;
use App\Jobs\SendNotificationJob;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserType;

class TransferService
{
    public function __construct(
        private User $userModel,
        private UserType $userTypeModel,
        private Transfer $transferModel,
        private ExternalAuthServiceInterface $externalAuthService,
        private SendNotificationJob $sendNotificationJob
    ) {
        $this->userTypeModel = $userTypeModel;
        $this->userModel = $userModel;
        $this->transferModel = $transferModel;
        $this->externalAuthService = $externalAuthService;
        $this->sendNotificationJob = $sendNotificationJob;
    }

    public function createTransfer(array $transferData, array $loggedUser): array
    {
        $userPayee = $this->userModel->getUserById(
            $transferData['payee_id']
        );

        if (is_null($userPayee)) {
            throw new ServiceException(
                'User payee not found',
                404
            );
        }

        $transferToInsert = $this->getTransferToInsert(
            $transferData,
            $loggedUser,
            $userPayee->toArray()
        );

        $transfer = $this->transferModel->initTransfer(
            $transferToInsert['payer_id'],
            $transferToInsert['payee_id'],
            $transferToInsert['value']
        );
        $updatedPayer = $this->userModel->decrementUserWallet(
            $transferToInsert['payer_id'],
            $transferToInsert['value']
        );
        $updatedPayee = $this->userModel->incrementUserWallet(
            $transferToInsert['payee_id'],
            $transferToInsert['value']
        );

        $authorization = $this->externalAuthService->getExternalAuth();
        if (!$authorization) {
            throw new ServiceException(
                'Authorization error',
                500
            );
        }

        $this->transferModel->finishTransfer($transfer->id);
        $this->sendNotificationJob
            ->dispatch(
                $userPayee->id,
                'New transfer',
                'You have received a new transfer from ' . $updatedPayer['name'],
                "/transfer/$transfer->id",
            )->onQueue('notify');

        return [
            'transfer' => $transfer,
            'payer' => $updatedPayer,
            'payee' => $updatedPayee
        ];
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