<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LiveKitController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// ──────────────────────────────────────────────
// Rotas Públicas
// ──────────────────────────────────────────────

Route::get('/status', function () {
    return response()->json([
        'status' => 'online',
        'versao' => '1.0.0',
    ]);
});

// Auth (login e registro são públicas)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rota customizada para liberar usuários anônimos no Reverb
Route::post('/broadcasting/auth', function (Request $request) {
    $userName = $request->header('X-User-Name', 'Anônimo');

    $fakeUser = new User();
    $fakeUser->id = (string) crc32($request->socket_id);
    $fakeUser->name = $userName;

    Auth::setUser($fakeUser);

    return Broadcast::auth($request);
});

// ──────────────────────────────────────────────
// Rotas Protegidas (auth:sanctum)
// ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Broadcasts (Rooms)
    Route::get('/broadcasts', [RoomController::class, 'index']);
    Route::post('/broadcasts', [RoomController::class, 'store']);

    Route::post('/livekit/token', [LiveKitController::class, 'gerarToken']);
});