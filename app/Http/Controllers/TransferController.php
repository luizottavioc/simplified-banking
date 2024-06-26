<?php

namespace App\Http\Controllers;

use App\Contracts\ExternalAuthServiceInterface;
use App\Exceptions\ServiceException;
use App\Http\Requests\Transfer\TransferRequest;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use App\Traits\HttpResponses;

class TransferController extends Controller
{
    use HttpResponses;

    protected $externalAuthService;

    public function __construct(ExternalAuthServiceInterface $externalAuthService)
    {
        $this->externalAuthService = $externalAuthService;
    }

    private function handleTransferError(
        object|null $startedTransfer,
        object|null $updatedUserPayer,
        object|null $updatedUserPayee,
        string|null $errorObservation
    ): bool {
        $transferWasStarted = !is_null($startedTransfer);
        $payeeWasUpdated = !is_null($updatedUserPayee);
        $payerWasUpdated = !is_null($updatedUserPayer);

        if (!$transferWasStarted && !$payeeWasUpdated && !$payerWasUpdated) {
            return true;
        }

        if ($transferWasStarted) {
            $transferModel = new Transfer();
            $transferModel->finishTransferNotCompleted($startedTransfer->id, $errorObservation);

            $userModel = new User();

            if ($payerWasUpdated) {
                $userModel->incrementUserWallet(
                    $updatedUserPayer->id,
                    $startedTransfer->value
                );
            }

            if ($payeeWasUpdated) {
                $userModel->decrementUserWallet(
                    $updatedUserPayee->id,
                    $startedTransfer->value
                );
            }

            return true;
        }

        return !$payeeWasUpdated && !$payerWasUpdated;
    }

    public function getAllTransfers()
    {
    }

    public function getOneTransfer()
    {
    }
    
    public function createTransfer(TransferRequest $request)
    {
        $transfer = null;
        $updatedPayer = null;
        $updatedPayee = null;
        $observation = null;

        try {
            $transferData = $request->validated();

            $userModel = new User();
            $transferService = new TransferService();

            $loggedUser = auth()->user()->toArray();
            $userPayee = $userModel->getUserById($transferData['payee_id']);

            if (is_null($userPayee)) {
                return $this->error('User payee not found', 404);
            }

            $transferToInsert = $transferService->getTransferToInsert(
                $transferData,
                $loggedUser,
                $userPayee->toArray()
            );

            $transferModel = new Transfer();
            $transfer = $transferModel->initTransfer(
                $transferToInsert['payer_id'],
                $transferToInsert['payee_id'],
                $transferToInsert['value']
            );

            $updatedPayer = $userModel->decrementUserWallet(
                $transferToInsert['payer_id'],
                $transferToInsert['value']
            );
            $updatedPayee = $userModel->incrementUserWallet(
                $transferToInsert['payee_id'],
                $transferToInsert['value']
            );

            $authorization = $this->externalAuthService->getExternalAuth();
            if (!$authorization) {
                $observation = 'Pay authorization error';
                return $this->error(
                    'Authorization error',
                    500
                );
            }

            $transferModel->finishTransferCompleted($transfer->id, null);

            $responseData = [
                'transfer' => $transfer,
                'payer' => $updatedPayer,
                'payee' => $updatedPayee
            ];

            return $this->response('Transfer created successfully', 201, $responseData);

        } catch (ServiceException $e) {
            $handleError = $this->handleTransferError(
                $transfer,
                $updatedPayer,
                $updatedPayee,
                $observation
            );

            if ($handleError) {
                return $this->error(
                    $e->getMessage(),
                    $e->getCode(),
                    []
                );
            }

            return $this->error(
                'Unexpected error on create transfer',
                500
            );
        } catch (\Exception $e) {
            $this->handleTransferError(
                $transfer,
                $updatedPayer,
                $updatedPayee,
                $observation
            );
            
            return $this->error(
                'Unexpected error on create transfer',
                500
            );
        }
    }
}
