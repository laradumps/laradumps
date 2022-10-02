<?php

use Illuminate\Support\Facades\Route;
use LaraDumps\LaraDumps\Http\Controllers\ConfigController;

Route::post('/__ds__/clear', fn () => ds()->clear());

Route::group(['middleware' => ['web']], fn () => Route::resource('/laradumps', ConfigController::class));
