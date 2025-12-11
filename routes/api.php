<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function () {
    \Illuminate\Support\Facades\Log::debug('test');
    \Illuminate\Support\Facades\Log::debug(json_encode(auth()->user()));
    return response()->json(auth()->user());
});


Route::middleware([
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Session\Middleware\StartSession::class,
])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function() {

        Log::debug(sprintf("User: %s", json_encode(auth()->user())));
        return auth()->user();
    });

    Route::group(['prefix' => 'commissions', 'as' => 'commissions.'], static function () {
        Route::get('/{user}', [CommissionController::class, 'commissions'])->name('index');
    });
});

Route::post('/create-wallet/{user}', [WalletController::class, 'createWallet']);
Route::get('/get-wallet/{user}', [WalletController::class, 'index']);

Route::get("is-connected", static function () {
    if(auth()->check()) {
        return response()->json(true);
    }
    return response()->json(false);
});
