<?php

use App\Http\Controllers\DepositController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\TransferController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('hello-world', function (Request $request) {
    return 'Hello, world!';
});

Route::group(['middleware' => 'jwt'], function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->withoutMiddleware('jwt');
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('jwt');
        Route::get('me', [AuthController::class, 'getMe']);
        Route::get('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('deposit')->group(function () {
        Route::get('/', [DepositController::class, 'getAllDeposits']);
        Route::get('/{id}', [DepositController::class, 'getOneDeposit']);
        Route::post('/', [DepositController::class, 'createDeposit'])->middleware('usertype:teller');
        Route::post('/withdraw', [DepositController::class, 'withdraw'])->middleware('usertype:teller');
    });

    Route::prefix('transfers')->group(function () {
        Route::get('/', [TransferController::class, 'getAllTransfers']);
        Route::get('/{id}', [TransferController::class, 'getOneTransfer']);
        Route::post('/', [TransferController::class, 'createTransfer']);
    });

    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionsController::class, 'getAllTransactions']);
        Route::get('/{idUser}', [TransactionsController::class, 'getTransactionsByUser'])->middleware('usertype:admin,teller');
    });
});
