<?php

use App\Http\Controllers\EghlController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('payment', [EghlController::class, 'generatePaymentUrl']);

/**
 * Ensure these routes are publicly accessible by client browser/eghl server
 */
Route::prefix('eghl')->group(function () {
    Route::post('callback', [EghlController::class, 'callback']);
    Route::get('redirect', [EghlController::class, 'redirect']);
    Route::post('redirect', [EghlController::class, 'redirect']);
});
