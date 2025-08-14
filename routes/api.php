<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Demo API untuk development
Route::middleware(['throttle:10,1'])->group(function () {
    Route::get('/demo-users', [AuthController::class, 'getDemoUsers']);
});

Route::get('/healthz', function () {
    return response()->json(['status' => 'ok']);
});