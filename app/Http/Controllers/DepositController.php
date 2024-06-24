<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Deposit\CreateDepositRequest;
use App\Http\Requests\Deposit\WithdrawRequest;
use App\Models\Deposit;
use App\Models\User;
use App\Services\DepositService;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    use HttpResponses;

    public function getAllDeposits()
    {
    }

    public function getOneDeposit()
    {
    }

    public function createDeposit(CreateDepositRequest $request)
    {
        try {
            $depositData = $request->validated();

            $loggedUser = auth()->user();
            $userModel = new User();

            $taller = $loggedUser->toArray();
            $userTo = $userModel->getUserById($depositData['user_id'])->toArray();
            
            $depositModel = new Deposit();
            $depositService = new DepositService();

            $depositToInsert = $depositService->getDepositToInsert(
                $depositData, 
                $taller, 
                $userTo
            );
            
            $deposit = $depositModel->createDeposit($depositToInsert);
            $newUser = $userModel->incrementUserWallet($userTo['id'], $depositData['value']);

            $responseData = [
                'deposit' => $deposit,
                'user' => $newUser
            ];

            return $this->response('Deposit created successfully', 201, $responseData);
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getCode(), []);
        } catch (\Exception $e) {
            return $this->error('Unexpected error on create deposit', 500);
        }
    }

    public function withdraw(WithdrawRequest $request)
    {
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
            $newUser = $userModel->decrementUserWallet($loggedUser['id'], $withdrawData['value']);

            $responseData = [
                'withdraw' => $withdraw,
                'user' => $newUser
            ];

            return $this->response('Withdraw created successfully', 201, $responseData);
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getCode(), []);
        } catch (\Exception $e) {
            return $this->error('Unexpected error on create deposit', 500);
        }
    }
}
