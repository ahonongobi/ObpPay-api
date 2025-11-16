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

    // Fecth user by obp_id
    Route::get('/user/by-obp/{obp_id}', [AuthController::class, 'findByObp']);

    // Wallet
    Route::get('/wallet/balance',       [WalletController::class, 'balance']);
    Route::get('/wallet/transactions',  [WalletController::class, 'transactions']);
    Route::post('/wallet/deposit',      [WalletController::class, 'deposit']);
    Route::post('/wallet/transfer',     [WalletController::class, 'transfer']);

    // Loan
    Route::get('/loan/eligibility',     [LoanrequestsController::class, 'eligibility']);
    Route::post('/loan/request',        [LoanrequestsController::class, 'requestLoan']);

    
});

Route::middleware('auth:sanctum')->get('/user/score', function (Request $request) {
    return response()->json([
        'score' => $request->user()->score,
    ]);
});

Route::middleware('auth:sanctum')->get('/user/score/latest', function (Request $request) {
    $last = \App\Models\UserScore::where('user_id', $request->user()->id)
        ->orderByDesc('id')
        ->first();

    return response()->json([
        'score' => $request->user()->score,
        'last_points' => $last?->points ?? 0,
        'reason' => $last?->reason ?? null,
    ]);
});
