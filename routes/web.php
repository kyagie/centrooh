<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});

Route::get('/terms-of-use', function () {
    return view('terms-of-use');
});
