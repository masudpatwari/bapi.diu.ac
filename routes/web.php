<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\APIBankController;
use App\Http\Controllers\ApiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    return response()->json("Hello ! Welcome to Dhaka International University.");

});


Route::group(['prefix' => 'exim', 'middleware' => ['bank']], function () {

            Route::post('search_student', [APIBankController::class, 'searchStudent']);
            Route::post('confirm_payment', [APIBankController::class, 'confirmPayment']);
            Route::get('transection_info/{date}', [APIBankController::class, 'transectionInfo']);
            Route::get('single_transection_info/{receipt_no}', [APIBankController::class, 'singleTransectionInfo']);
            Route::get('transection_delete/{receipt_no}', [APIBankController::class, 'transectionDelete']);
            Route::GET('get_purpose_pay', [ApiController::class, 'getPurposePay'])->name('PurposePay');
            
        });
   

