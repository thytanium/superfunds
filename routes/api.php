<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\PotentialDuplicateFundController;
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

Route::resource('companies', CompanyController::class);
Route::resource('managers', ManagerController::class);
Route::resource('funds', FundController::class);
Route::resource(
    'potential_duplicate_funds',
    PotentialDuplicateFundController::class,
);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
