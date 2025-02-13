<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    return response()->json(['message' => 'Hello']);
});

Route::post('/register', [AuthController::class, 'register'])->name('user.register');