<?php
use Illuminate\Support\Facades\Route;

// Admin
Route::get('/admin', function () {
    return view('admin');
});
Route::get('/admin/{any}', function () {
    return view('admin');
})->where('any', '.*');

// Site principal
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');