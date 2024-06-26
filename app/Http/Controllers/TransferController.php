<?php

namespace App\Http\Controllers;

use App\Exceptions\ServiceException;
use App\Http\Requests\Transfer\TransferRequest;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    use HttpResponses;

    protected $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }


    public function getAllTransfers()
    {
    }

    public function getOneTransfer()
    {
    }

    public function createTransfer(TransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $transferData = $request->validated();
            $loggedUser = auth()->user()->toArray();
            $transferResponse = $this->transferService->createTransfer($transferData, $loggedUser);

            DB::commit();

            return $this->response('Transfer created successfully', 201, $transferResponse);
        } catch (ServiceException $e) {
            logger()->error('createTransfer - Service Exception: ' . $e->getMessage());
            DB::rollBack();

            return $this->error(
                $e->getMessage(),
                $e->getCode(),
            );
        } catch (\Exception $e) {
            logger()->error('createTransfer - Exception: ' . $e->getMessage());
            DB::rollBack();

            return $this->error(
                'Unexpected error on create transfer',
                500
            );
        }
    }
}
