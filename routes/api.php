<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanrequestsController;
use App\Http\Controllers\OtpsController;

Route::post('/auth/register', [AuthController::class, 'register']);  // connected to flutter
Route::post('/auth/login',    [AuthController::class, 'login']);  // connected to flutter

Route::post('/auth/send-otp', [OtpsController::class, 'sendOtp']);  // connected to flutter
Route::post('/auth/verify-otp', [OtpsController::class, 'verifyOtp']);  // connected to flutter 

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Wallet
    Route::get('/wallet/balance',       [WalletController::class, 'balance']);
    Route::get('/wallet/transactions',  [WalletController::class, 'transactions']);
    Route::post('/wallet/deposit',      [WalletController::class, 'deposit']);
    Route::post('/wallet/transfer',     [WalletController::class, 'transfer']);

    // Loan
    Route::get('/loan/eligibility',     [LoanrequestsController::class, 'eligibility']);
    Route::post('/loan/request',        [LoanrequestsController::class, 'requestLoan']);
});
