<?php

use App\Models\Tarefa;

test('tarefa não atrasada quando não tem prazo', function () {
    $tarefa = new Tarefa;
    $tarefa->concluida = false;
    $tarefa->prazo = null;

    expect($tarefa->estaAtrasada())->toBeFalse();
});

test('tarefa não atrasada quando já está concluída', function () {
    $tarefa = new Tarefa;
    $tarefa->concluida = true;
    $tarefa->prazo = now()->subDay();

    expect($tarefa->estaAtrasada())->toBeFalse();
});

test('tarefa atrasada quando prazo passou e não está concluída', function () {
    $tarefa = new Tarefa;
    $tarefa->concluida = false;
    $tarefa->prazo = now()->subDay();

    expect($tarefa->estaAtrasada())->toBeTrue();
});

test('tarefa não atrasada quando prazo é futuro', function () {
    $tarefa = new Tarefa;
    $tarefa->concluida = false;
    $tarefa->prazo = now()->addDay();

    expect($tarefa->estaAtrasada())->toBeFalse();
});
