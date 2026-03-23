<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LiveKitController;
use App\Http\Controllers\RoomController;
use App\Models\Room;
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

// Resolução de token de convidado (público)
Route::get('/guest/{token}', function (string $token) {
    $room = Room::where('guest_token', $token)->firstOrFail();

    return response()->json([
        'room_name' => $room->room_name,
        'title'     => $room->title,
    ]);
});

// Token LiveKit (público — usado tanto por host autenticado quanto por guests)
Route::post('/livekit/token', [LiveKitController::class, 'gerarToken']);

// ──────────────────────────────────────────────
// Rotas Protegidas (auth:sanctum)
// ──────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Broadcasts (Rooms)
    Route::get('/broadcasts', [RoomController::class, 'index']);
    Route::post('/broadcasts', [RoomController::class, 'store']);
});