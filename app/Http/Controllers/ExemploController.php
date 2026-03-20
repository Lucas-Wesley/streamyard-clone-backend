<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExemploController extends Controller
{
    /**
     * Listar todas as tarefas.
     */
    public function index(): JsonResponse
    {
        // Exemplo com dados mockados (depois você troca por Eloquent)
        $tarefas = [
            ['id' => 1, 'titulo' => 'Estudar Laravel', 'concluida' => false],
            ['id' => 2, 'titulo' => 'Criar API REST', 'concluida' => false],
            ['id' => 3, 'titulo' => 'Configurar Sail', 'concluida' => true],
        ];

        return response()->json($tarefas);
    }

    /**
     * Criar nova tarefa.
     */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'titulo' => 'required|string|max:255',
        ]);

        // Simulando criação
        $tarefa = [
            'id' => rand(4, 100),
            'titulo' => $dados['titulo'],
            'concluida' => false,
        ];

        return response()->json($tarefa, 201);
    }

    /**
     * Exibir uma tarefa específica.
     */
    public function show(string $id): JsonResponse
    {
        $tarefa = [
            'id' => (int) $id,
            'titulo' => 'Tarefa de exemplo',
            'concluida' => false,
        ];

        return response()->json($tarefa);
    }

    /**
     * Atualizar uma tarefa.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $dados = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'concluida' => 'sometimes|boolean',
        ]);

        $tarefa = [
            'id' => (int) $id,
            'titulo' => $dados['titulo'] ?? 'Tarefa atualizada',
            'concluida' => $dados['concluida'] ?? false,
        ];

        return response()->json($tarefa);
    }

    /**
     * Deletar uma tarefa.
     */
    public function destroy(string $id): JsonResponse
    {
        return response()->json(['mensagem' => "Tarefa {$id} removida com sucesso."]);
    }
}
