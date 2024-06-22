<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Deposit\CreateDepositRequest;
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
            $userModel->incrementUserWallet($userTo['id'], $depositData['value']);

            return $this->response('Deposit created successfully', 201, $deposit);
        } catch (ServiceException $e) {
            return $this->error($e->getMessage(), $e->getCode(), []);
        } catch (\Exception $e) {
            return $this->error('Unexpected error on create deposit', 500);
        }
    }

    public function withdraw()
    {
    }
}
