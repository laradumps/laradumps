<?php

use Illuminate\Support\Facades\Route;

Route::post('/__ds__/clear', function () {
    ds()->clear();
});

//Route::post('/__ds__/extension', function () {
//    $wireId = request()->get('wireId');
//
//    cache()->put('laradumps-wire-id', $wireId);
//});
