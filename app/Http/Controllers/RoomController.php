<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    /**
     * Lista as transmissões do usuário autenticado.
     * GET /api/broadcasts
     */
    public function index(Request $request)
    {
        $rooms = $request->user()->rooms()->latest()->get();

        return response()->json($rooms);
    }

    /**
     * Cria uma nova transmissão.
     * POST /api/broadcasts
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $room = $request->user()->rooms()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'room_name'   => 'room_' . Str::uuid(),
            'guest_token' => Str::random(32),
        ]);

        return response()->json([
            'room'       => $room,
            'guest_link' => url("/guest/{$room->guest_token}"),
        ], 201);
    }
}