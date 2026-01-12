<?php

use App\Http\Controllers\AwsAccountController;
use App\Http\Controllers\AwsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('aws-accounts', AwsAccountController::class);
Route::resource('aws', AwsController::class);
