<?php

use App\Http\Controllers\Api\EmailController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('emails')->group(function () {
    Route::post('/', [EmailController::class, 'sendEmail'])->name('send-email');
    Route::get('/', [EmailController::class, 'getEmails'])->name('get-emails');
    Route::post('/search', [EmailController::class, 'searchEmails'])->name('search-email');
    Route::get('/stats', [EmailController::class, 'getEmailStats'])->name('get-email-stats');
    Route::get('/{emailId}', [EmailController::class, 'getEmail'])->name('get-email');
});
