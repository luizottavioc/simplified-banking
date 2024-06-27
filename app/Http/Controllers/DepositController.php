<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Deposit\DepositRequest;
use App\Http\Requests\Deposit\WithdrawRequest;
use App\Services\DepositService;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    use HttpResponses;

    private $depositService;

    public function __construct(DepositService $depositService)
    {
        $this->depositService = $depositService;
    }

    public function getAllDeposits()
    {
        return $this->error('Not implemented', 501);
    }

    public function getOneDeposit()
    {
        return $this->error('Not implemented', 501);
    }

    public function createDeposit(DepositRequest $request)
    {
        try {
            DB::beginTransaction();

            $depositData = $request->validated();
            $loggedUser = auth()->user()->toArray();

            $depositResponse = $this->depositService->createDeposit(
                $depositData,
                $loggedUser
            );

            DB::commit();

            return $this->response(
                'Deposit created successfully',
                201,
                $depositResponse
            );
        } catch (ServiceException $e) {
            logger()->error('createDeposit - Service Exception: ' . $e->getMessage());
            DB::rollBack();

            return $this->error(
                $e->getMessage(),
                $e->getCode(),
                []
            );
        } catch (\Exception $e) {
            logger()->error('createDeposit - Exception: ' . $e->getMessage());
            DB::rollBack();

            return $this->error(
                'Unexpected error on create deposit',
                500
            );
        }
    }

    public function withdraw(WithdrawRequest $request)
    {
        try {
            DB::beginTransaction();

            $withdrawData = $request->validated();
            $loggedUser = auth()->user()->toArray();

            $withdrawResponse = $this->depositService->createWithdraw(
                $withdrawData,
                $loggedUser
            );

            DB::commit();

            return $this->response(
                'Withdraw created successfully',
                201,
                $withdrawResponse
            );
        } catch (ServiceException $e) {
            logger()->error('withdraw - Service Exception: ' . $e->getMessage());
            DB::rollBack();
            
            return $this->error(
                $e->getMessage(),
                $e->getCode(),
                []
            );
        } catch (\Exception $e) {
            logger()->error('withdraw - Exception: ' . $e->getMessage());
            DB::rollBack();

            return $this->error(
                'Unexpected error on create deposit',
                500
            );
        }
    }
}
