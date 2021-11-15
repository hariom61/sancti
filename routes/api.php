<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Sancti\Http\Controllers\SanctiController;

Route::prefix('api')->name('api.')->middleware(['api'])->group(function() {

	// Routes
	Route::post('/login', [SanctiController::class, 'login'])->name('login');
	Route::post('/register', [SanctiController::class, 'register'])->name('register');
	Route::post('/reset', [SanctiController::class, 'reset'])->name('reset');
	Route::get('/activate/{id}/{code}', [SanctiController::class, 'activate'])->name('activate');

	// Only logged users
	Route::middleware(['auth:sanctum'])->group(function () {
		Route::get('/user', [SanctiController::class, 'user'])->name('user');
		Route::get('/delete', [SanctiController::class, 'delete'])->name('delete');
		Route::get('/logout', [SanctiController::class, 'logout'])->name('logout');
		Route::post('/change-password', [SanctiController::class, 'change'])->name('change-password');
	});

});