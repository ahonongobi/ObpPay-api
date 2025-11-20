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
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanrequestsController;
use App\Http\Controllers\MoMoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OtpsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InstallmentPlanController;
use App\Http\Controllers\PurchaseController;
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
    Route::post('/wallet/deposit',      [WalletController::class, 'deposit']); // MoMo Connected to flutter for now 
    Route::post('/wallet/transfer',     [WalletController::class, 'transfer']); // connected to flutter

    // Loan
    Route::get('/loan/eligibility',     [LoanrequestsController::class, 'eligibility']); // connected to flutter
    Route::post('/loan/request',        [LoanrequestsController::class, 'requestLoan']); // connected to flutter

    // MoMo Payment
    Route::post('/momo/pay', [MoMoController::class, 'mobilePay']);  // connected to flutter

   
    
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
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']); // Connected to flutter
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']); // Connected to flutter
    Route::post('/user/update-photo', [UserController::class, 'updatePhoto']); // Connected to flutter


    Route::post('/kyc/upload', [KycController::class, 'uploadDocument']); // connected to flutter
    Route::get('/kyc/status', [KycController::class, 'getStatus']); // connected to flutter
    Route::post('/kyc/submit', [KycController::class, 'submitKyc']); // connected to flutter

    
});

Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/categories', [CategoriesController::class, 'index']);


// Installment Plans by Product
Route::get('/products/{id}/installments', [InstallmentPlanController::class, 'getPlans'])->middleware('auth:sanctum');

// Start installment payment
Route::post('/market/installment/start', [InstallmentPlanController::class, 'startInstallment'])
    ->middleware('auth:sanctum');

Route::post('/market/pay-now', [PurchaseController::class, 'payNow'])
    ->middleware('auth:sanctum');


// Cron: Process automatic payments nned to run later: * * * * * php /var/www/html/obppay/artisan schedule:run >> /dev/null 2>&1

Route::post('/cron/installments/process', [InstallmentPlanController::class, 'processMonthly']);
