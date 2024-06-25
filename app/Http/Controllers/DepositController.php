<?php

namespace App\Http\Controllers;

use App\Contracts\ExternalAuthServiceInterface;
use App\Exceptions\ServiceException;
use App\Http\Requests\Deposit\DepositRequest;
use App\Http\Requests\Deposit\WithdrawRequest;
use App\Models\Deposit;
use App\Models\User;
use App\Services\DepositService;
use App\Traits\HttpResponses;
use App\Utils\HttpRequests;
use Illuminate\Support\Facades\Http;

class DepositController extends Controller
{
    use HttpResponses;

    protected $externalAuthService;

    public function __construct(ExternalAuthServiceInterface $externalAuthService)
    {
        $this->externalAuthService = $externalAuthService;
    }


    private function handleDepositError($createdDeposit, $updatedUser): bool
    {
        $depositWasCreated = !empty($createdDeposit);
        $userWasUpdated = !empty($updatedUser);

        if (!$depositWasCreated && !$userWasUpdated) {
            return true;
        }

        if ($depositWasCreated) {
            $depositModel = new Deposit();
            $depositModel->deleteDeposit($createdDeposit->id);

            if ($userWasUpdated) {
                $userModel = new User();
                $userModel->decrementUserWallet($updatedUser->id, $createdDeposit->value);
            }

            return true;
        }

        return !$userWasUpdated;
    }

    private function handleWithdrawError($createdWithdraw, $updatedUser): bool
    {
        $withdrawWasCreated = !empty($createdWithdraw);
        $userWasUpdated = !empty($updatedUser);

        if (!$withdrawWasCreated && !$userWasUpdated) {
            return true;
        }

        if ($withdrawWasCreated) {
            $withdrawModel = new Deposit();
            $withdrawModel->deleteWithdraw($createdWithdraw->id);

            if ($userWasUpdated) {
                $userModel = new User();
                $userModel->incrementUserWallet($updatedUser->id, $createdWithdraw->value);
            }

            return true;
        }

        return !$userWasUpdated;
    }

    public function getAllDeposits()
    {
    }

    public function getOneDeposit()
    {
    }

    public function createDeposit(DepositRequest $request)
    {
        $deposit = null;
        $updatedUser = null;

        try {
            $depositData = $request->validated();

            $userModel = new User();
            $depositService = new DepositService();
            $loggedUser = auth()->user()->toArray();

            $depositToInsert = $depositService->getDepositToInsert(
                $depositData,
                $loggedUser
            );

            $depositModel = new Deposit();

            $deposit = $depositModel->createDeposit($depositToInsert);
            $updatedUser = $userModel->incrementUserWallet(
                $loggedUser['id'],
                $depositData['value']
            );

            $authorization = $this->externalAuthService->getExternalAuth();
            if (!$authorization) {
                return $this->error(
                    'Authorization error',
                    500
                );
            }

            $responseData = [
                'deposit' => $deposit,
                'user' => $updatedUser
            ];

            return $this->response(
                'Deposit created successfully',
                201,
                $responseData
            );
        } catch (ServiceException $e) {
            $handleError = $this->handleDepositError($deposit, $updatedUser);
            if ($handleError) {
                return $this->error(
                    $e->getMessage(),
                    $e->getCode(),
                    []
                );
            }

            return $this->error(
                'Unexpected error on create deposit',
                500
            );
        } catch (\Exception $e) {
            $this->handleDepositError($deposit, $updatedUser);
            return $this->error(
                'Unexpected error on create deposit',
                500
            );
        }
    }

    public function withdraw(WithdrawRequest $request)
    {
        $withdraw = null;
        $updatedUser = null;

        try {
            $withdrawData = $request->validated();
            $loggedUser = auth()->user()->toArray();

            $depositService = new DepositService();
            $withdrawToInsert = $depositService->getWithdrawToInsert(
                $withdrawData,
                $loggedUser
            );

            $depositModel = new Deposit();
            $userModel = new User();

            $withdraw = $depositModel->createWithdraw($withdrawToInsert);
            $updatedUser = $userModel->decrementUserWallet($loggedUser['id'], $withdrawData['value']);

            $responseData = [
                'withdraw' => $withdraw,
                'user' => $updatedUser
            ];

            return $this->response(
                'Withdraw created successfully',
                201,
                $responseData
            );
        } catch (ServiceException $e) {
            $handleError = $this->handleWithdrawError($withdraw, $updatedUser);
            if ($handleError) {
                return $this->error(
                    $e->getMessage(),
                    $e->getCode(),
                    []
                );
            }

            return $this->error(
                'Unexpected error on create deposit',
                500
            );
        } catch (\Exception $e) {
            $this->handleWithdrawError($withdraw, $updatedUser);
            return $this->error(
                'Unexpected error on create deposit',
                500
            );
        }
    }
}
