<?php

use App\Models\Tarefa;

test('tarefa nao atrasada quando nao tem prazo', function () {
    $tarefa = new Tarefa();
    $tarefa->concluida = false;
    $tarefa->prazo = null;

    expect($tarefa->estaAtrasada())->toBeFalse();
});

test('tarefa nao atrasada quando ja esta concluida', function () {
    $tarefa = new Tarefa();
    $tarefa->concluida = true;
    $tarefa->prazo = now()->subDay();

    expect($tarefa->estaAtrasada())->toBeFalse();
});

test('tarefa atrasada quando prazo passou e nao esta concluida', function () {
    $tarefa = new Tarefa();
    $tarefa->concluida = false;
    $tarefa->prazo = now()->subDay();

    expect($tarefa->estaAtrasada())->toBeTrue();
});

test('tarefa nao atrasada quando prazo e futuro', function () {
    $tarefa = new Tarefa();
    $tarefa->concluida = false;
    $tarefa->prazo = now()->addDay();

    expect($tarefa->estaAtrasada())->toBeFalse();
});
