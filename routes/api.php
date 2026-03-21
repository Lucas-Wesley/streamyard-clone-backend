<?php

use App\Http\Controllers\ExemploController;
use App\Http\Controllers\LiveKitController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Mesmo sem usar o banco, usamos a classe

Route::get('/status', function () {
    return response()->json([
        'status' => 'online',
        'versao' => '1.0.0',
    ]);
});

// Rota customizada para liberar usuários anônimos no Reverb
Route::post('/broadcasting/auth', function (Request $request) {
    // Pegamos o nome que o Nuxt/Vue vai mandar no cabeçalho (ou definimos 'Anônimo')
    $userName = $request->header('X-User-Name', 'Anônimo');

    // Criamos um usuário falso apenas na memória (não salva no banco)
    $fakeUser = new User();
    $fakeUser->id = (string) crc32($request->socket_id); // Precisa ser string
    $fakeUser->name = $userName;

    // Forçamos o Laravel a achar que este usuário está logado
    Auth::setUser($fakeUser);

    // O Laravel agora gera a assinatura criptografada e libera a porta!
    return Broadcast::auth($request);
});

Route::post('/livekit/token', [LiveKitController::class, 'gerarToken']);

Route::apiResource('tarefas', ExemploController::class);
