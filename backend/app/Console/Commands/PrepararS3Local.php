<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Throwable;

/**
 * Cria o bucket de uploads no LocalStack, que sobe vazio.
 *
 * Serve para exercitar upload, URL assinada e a exclusão da LGPD sem conta na
 * AWS. Recusa rodar contra a AWS de verdade.
 */
class PrepararS3Local extends Command
{
    protected $signature = 's3:preparar-local';

    protected $description = 'Cria o bucket de uploads no S3 local (LocalStack)';

    public function handle(): int
    {
        $endpoint = config('filesystems.disks.s3.endpoint');
        $bucket = config('filesystems.disks.s3.bucket');

        if (! $endpoint) {
            $this->error('AWS_ENDPOINT não está definido. Este comando é só para o S3 local.');

            return self::FAILURE;
        }

        try {
            $cliente = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            if ($cliente->doesBucketExist($bucket)) {
                $this->info("O bucket {$bucket} já existe em {$endpoint}.");

                return self::SUCCESS;
            }

            $cliente->createBucket(['Bucket' => $bucket]);
            $this->info("Bucket {$bucket} criado em {$endpoint}.");

            return self::SUCCESS;
        } catch (Throwable $falha) {
            $this->error("Não foi possível falar com o S3 local: {$falha->getMessage()}");

            return self::FAILURE;
        }
    }
}
