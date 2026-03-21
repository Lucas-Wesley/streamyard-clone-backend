<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class LiveKitController extends Controller
{
    /**
     * Gera um token JWT para o participante acessar uma sala do LiveKit.
     *
     * POST /api/livekit/token
     * Body: { "room": "sala-123", "identity": "Lucas" }
     * Response: { "token": "eyJ..." }
     */
    public function gerarToken(Request $request)
    {
        $request->validate([
            'room' => 'required|string|max:100',
            'identity' => 'required|string|max:100',
        ]);

        $roomName = $request->input('room');
        $identity = $request->input('identity');

        // Permissões de vídeo/áudio para o participante
        $videoGrant = new VideoGrant();
        $videoGrant->setRoomJoin(true);
        $videoGrant->setRoomName($roomName);

        // Configurações do token
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($identity)
            ->setName($identity)
            ->setTtl(6 * 60 * 60); // 6 horas

        // Gera o token usando as credenciais do LiveKit
        $token = new AccessToken(
            config('livekit.api_key'),
            config('livekit.api_secret')
        );
        $token->init($tokenOptions);
        $token->setGrant($videoGrant);

        return response()->json([
            'token' => $token->toJwt(),
        ]);
    }
}
