<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/perfil', [AuthController::class, 'perfil']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('/metricas', [DashboardController::class, 'metricas']);
        Route::get('/atividades', [DashboardController::class, 'atividades']);
        Route::get('/funil', [DashboardController::class, 'funil']);
    });

    Route::apiResource('leads', LeadController::class);
    Route::patch('leads/{lead}/estagio', [LeadController::class, 'moverEstagio']);
    Route::get('leads/{lead}/historico', [LeadController::class, 'historico']);
    Route::post('leads/{lead}/arquivos', [LeadController::class, 'uploadArquivo']);

    Route::get('pipeline', [LeadController::class, 'pipeline']);

    Route::apiResource('tarefas', TarefaController::class);
    Route::patch('tarefas/{tarefa}/concluir', [TarefaController::class, 'concluir']);

    Route::middleware('can:gerenciar-usuarios')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class);
    });
});
