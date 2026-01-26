<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\UserRoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
	Route::get('/user', [AuthController::class, 'user']);
	Route::post('/logout', [AuthController::class, 'logout']);

	Route::get('rooms', [RoomController::class, 'index']);
	Route::get('rooms/{room}', [RoomController::class, 'show']);
	Route::apiResource('tournaments', TournamentController::class);
	Route::get('currencies', [CurrencyController::class, 'index']);

	Route::prefix('user-rooms')->group(function () {
		Route::get('/', [UserRoomController::class, 'index']);
		Route::get('/{room}', [UserRoomController::class, 'show']);
		Route::put('/{room}/balance', [UserRoomController::class, 'updateBalance']);
		Route::post('/{room}/attach', [UserRoomController::class, 'attach']);
		Route::delete('/{room}/detach', [UserRoomController::class, 'detach']);
	});

	Route::prefix('dashboard')->group(function () {
		Route::get('/stats', [DashboardController::class, 'stats']);
	});

	Route::prefix('admin')->middleware('admin')->group(function () {
		Route::get('/users', [\App\Http\Controllers\Api\AdminUserController::class, 'index']);
			Route::get('/users/{user}', [\App\Http\Controllers\Api\AdminUserController::class, 'show']);
		Route::post('/users/{user}/ban', [\App\Http\Controllers\Api\AdminUserController::class, 'ban']);
		Route::post('/users/{user}/unban', [\App\Http\Controllers\Api\AdminUserController::class, 'unban']);
		Route::delete('/users/{user}', [\App\Http\Controllers\Api\AdminUserController::class, 'destroy']);

		Route::post('rooms', [RoomController::class, 'store']);
		Route::put('rooms/{room}', [RoomController::class, 'update']);
		Route::delete('rooms/{room}', [RoomController::class, 'destroy']);

		Route::post('currencies', [CurrencyController::class, 'store']);
		Route::get('currencies/{currency}', [CurrencyController::class, 'show']);
		Route::put('currencies/{currency}', [CurrencyController::class, 'update']);
		Route::delete('currencies/{currency}', [CurrencyController::class, 'destroy']);
	});
});
