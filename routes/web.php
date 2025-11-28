<?php

use App\Http\Controllers\Admin\LoanRequestController;
use App\Http\Controllers\Admin\WithdrawRequestController;
use Illuminate\Support\Facades\Route;
use App\Models\Transactions;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');



//route('login');

//Route::get()

// Admin login (public)
Route::get('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'showLogin'])
    ->name('admin.login');

Route::post('/admin/login', [\App\Http\Controllers\AdminAuthController::class, 'login'])
    ->name('admin.login.submit');

// Admin protected area
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {


    // logout route
    Route::post('/logout', [\App\Http\Controllers\AdminAuthController::class, 'logout'])
        ->name('admin.logout');

    Route::get('/index', function () {

        $users = \App\Models\User::latest()->paginate(5);



        $raw = Transactions::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->groupBy('month')
            ->pluck('total', 'month'); // key = month, value = total

        // Build full 12-month array
        $months = [];
        $totals = [];

        for ($m = 1; $m <= 12; $m++) {
            $months[] = $m;
            $totals[] = $raw[$m] ?? 0;
        }
        return view('admin.dashboard', compact('users', 'months', 'totals'));
    })->name('admin.dashboard');

    // create this route route('admin.kyc.index')
    Route::get('/kyc', [\App\Http\Controllers\AdminKYCController::class, 'index'])
        ->name('admin.kyc.index');

    Route::get('/kyc/{id}', [\App\Http\Controllers\AdminKYCController::class, 'show'])
        ->name('admin.kyc.show');

    Route::post('/kyc/{id}/approve', [\App\Http\Controllers\AdminKYCController::class, 'approve'])
        ->name('admin.kyc.approve');

    Route::post('/kyc/{id}/reject', [\App\Http\Controllers\AdminKYCController::class, 'reject'])
        ->name('admin.kyc.reject');

    Route::get('/transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])
        ->name('admin.transactions.index');

    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');


    Route::get('/users/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');


    Route::get('/loan', [LoanRequestController::class, 'index'])->name('admin.loans.index');
    Route::get('/loan/{id}', [LoanRequestController::class, 'show'])->name('admin.loans.show');

    Route::post('/loan/{id}/approve', [LoanRequestController::class, 'approve'])->name('admin.loans.approve');
    Route::post('/loan/{id}/reject', [LoanRequestController::class, 'reject'])->name('admin.loans.reject');



    // withdrawal routes can be added here later
    Route::get('/withdrawals', [WithdrawRequestController::class, 'index'])
        ->name('admin.withdrawals.index');

    Route::get('/withdrawals/{id}', [WithdrawRequestController::class, 'show'])
        ->name('admin.withdrawals.show');
    Route::post('/withdrawals/{id}/approve', [WithdrawRequestController::class, 'approve'])
        ->name('admin.withdrawals.approve');
    Route::post('/withdrawals/{id}/reject', [WithdrawRequestController::class, 'reject'])
        ->name('admin.withdrawals.reject');


        // marketplace rpoutes can be added here later
    Route::get('/marketplace', [\App\Http\Controllers\Admin\MarketplaceController::class, 'index'])
        ->name('admin.marketplace.index');

    Route::get('/marketplace/{id}', [\App\Http\Controllers\Admin\MarketplaceController::class, 'show'])
        ->name('admin.marketplace.show');
   // store route
    Route::post('/marketplace', [\App\Http\Controllers\Admin\MarketplaceController::class, 'store'])
        ->name('admin.marketplace.store');
        
    // setting route
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
        ->name('admin.settings.index');

    
});
