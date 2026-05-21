<?php

namespace App\Providers;

use App\Domain\Lead\LeadRepositoryInterface;
use App\Infrastructure\Repositories\EloquentLeadRepository;
use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LeadRepositoryInterface::class, EloquentLeadRepository::class);
    }

    public function boot(): void
    {
        Gate::define('gerenciar-usuarios', function (Usuario $usuario): bool {
            return in_array($usuario->perfil, ['admin', 'gerente'], true);
        });
    }
}
