<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('studio.{roomId}', function ($user, $roomId) {
    // Retorna os dados do nosso usuário fantasma para a sala
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});