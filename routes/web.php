<?php

use Illuminate\Support\Facades\Route;

Route::post('/__ds__/clear', function () {
    ds()->clear();
});
