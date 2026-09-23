<?php

use Illuminate\Support\Facades\Config;

test('o comando recusa rodar sem endpoint, para não criar bucket na AWS por engano', function () {
    Config::set('filesystems.disks.s3.endpoint', null);

    $this->artisan('s3:preparar-local')
        ->expectsOutputToContain('AWS_ENDPOINT não está definido')
        ->assertExitCode(1);
});

test('o comando aparece na lista do artisan com a descrição certa', function () {
    $this->artisan('list')
        ->expectsOutputToContain('s3:preparar-local')
        ->assertExitCode(0);
});
