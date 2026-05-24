<?php

namespace App\Providers;

use App\Domain\Lead\LeadRepositoryInterface;
use App\Infrastructure\Repositories\EloquentLeadRepository;
use App\Models\Lead;
use App\Models\Tarefa;
use App\Models\Usuario;
use App\Policies\LeadPolicy;
use App\Policies\TarefaPolicy;
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
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Tarefa::class, TarefaPolicy::class);

        Gate::define('gerenciar-usuarios', function (Usuario $usuario): bool {
            return in_array($usuario->perfil, ['admin', 'gerente'], true);
        });
    }
}
