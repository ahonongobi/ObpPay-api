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
use App\Http\Controllers\UserController;

Route::post('/auth/register', [AuthController::class, 'register']);  // connected to flutter
Route::post('/auth/login',    [AuthController::class, 'login']);  // connected to flutter

Route::post('/auth/send-otp', [OtpsController::class, 'sendOtp']);  // connected to flutter
Route::post('/auth/verify-otp', [OtpsController::class, 'verifyOtp']);  // connected to flutter 

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/auth/me',     [AuthController::class, 'me']); // connected to flutter
    Route::post('/auth/logout', [AuthController::class, 'logout']); // connected to flutter

    // Fecth user by obp_id
    Route::get('/user/by-obp/{obp_id}', [AuthController::class, 'findByObp']); // connected to flutter

    // Wallet
    Route::get('/wallet/balance',       [WalletController::class, 'balance']); // connected to flutter
    Route::get('/wallet/transactions',  [WalletController::class, 'transactions']);
    Route::post('/wallet/deposit',      [WalletController::class, 'deposit']);
    Route::post('/wallet/transfer',     [WalletController::class, 'transfer']); // connected to flutter

    // Loan
    Route::get('/loan/eligibility',     [LoanrequestsController::class, 'eligibility']); // connected to flutter
    Route::post('/loan/request',        [LoanrequestsController::class, 'requestLoan']); // connected to flutter

    
});

Route::middleware('auth:sanctum')->get('/user/score', function (Request $request) {
    return response()->json([
        'score' => $request->user()->score,
    ]);
}); // connected to flutter

Route::middleware('auth:sanctum')->get('/user/score/latest', function (Request $request) {
    $last = \App\Models\UserScore::where('user_id', $request->user()->id)
        ->orderByDesc('id')
        ->first();

    return response()->json([
        'score' => $request->user()->score,
        'last_points' => $last?->points ?? 0,
        'reason' => $last?->reason ?? null,
    ]);
}); // connected to flutter

Route::middleware('auth:sanctum')->group(function () {
    // ...
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('/user/update-photo', [UserController::class, 'updatePhoto']);
});
