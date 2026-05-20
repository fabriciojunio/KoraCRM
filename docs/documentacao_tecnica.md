# KoraCRM — Documentação Técnica

CRM para gestão de leads, pipeline de vendas e tarefas. Backend em PHP 8.2+
com Laravel 11, frontend em React + TypeScript.

## Sumário

1. [Visão geral](#visão-geral)
2. [Stack](#stack)
3. [Arquitetura](#arquitetura)
4. [Design patterns](#design-patterns)
5. [Banco de dados](#banco-de-dados)
6. [Endpoints](#endpoints)
7. [Autenticação e segurança](#autenticação-e-segurança)
8. [Testes](#testes)
9. [Docker](#docker)
10. [CI/CD](#cicd)
11. [Variáveis de ambiente](#variáveis-de-ambiente)
12. [Rodando localmente](#rodando-localmente)
13. [Decisões técnicas](#decisões-técnicas)

---

## Visão geral

Sistema web para equipes comerciais acompanharem o ciclo de vida de um lead —
da entrada até o fechamento (ganho/perdido) — com pipeline em Kanban,
tarefas vinculadas a leads, histórico de ações e um painel de métricas.

Perfis de usuário: `admin`, `gerente` e `vendedor`.

---

## Stack

**Backend**

- PHP 8.2+ / Laravel 11
- MySQL 8 (produção/Docker) — SQLite suportado para desenvolvimento local
- Redis 7 para cache e filas (opcional em dev)
- Laravel Sanctum (autenticação por token)
- Pest para testes
- L5-Swagger para documentação OpenAPI
- AWS SDK para upload em S3

**Frontend**

- React 18 + TypeScript 5
- Vite 5, Tailwind CSS 3
- TanStack React Query, Axios
- React Hook Form + Zod
- Vitest + Testing Library

**Infra**

- Docker / Docker Compose, Nginx
- GitHub Actions (lint, testes, build, deploy)

---

## Arquitetura

O backend é organizado em quatro camadas, com dependências apontando sempre
para dentro (HTTP → Application → Domain ← Infrastructure):

```
app/
├── Http/            Controllers, Form Requests, API Resources
├── Application/     Services (casos de uso) e DTOs
├── Domain/          Interfaces e regras de negócio
├── Infrastructure/  Repositórios (Eloquent)
└── Models/          Entidades Eloquent
```

- O **Controller** só recebe a requisição, delega para um Service e devolve a resposta.
- O **Service** orquestra o caso de uso e contém a regra de negócio.
- O Service depende da **interface** do repositório (Domain), nunca do Eloquent.
- A implementação concreta (Infrastructure) é injetada via container no
  `AppServiceProvider`.

---

## Design patterns

**Repository**

`App\Domain\Lead\LeadRepositoryInterface` define o contrato de persistência.
`App\Infrastructure\Repositories\EloquentLeadRepository` implementa usando
Eloquent. O binding é registrado no `AppServiceProvider`:

```php
$this->app->bind(LeadRepositoryInterface::class, EloquentLeadRepository::class);
```

**Service Layer**

Casos de uso isolados em `app/Application/Services`: `CriarLeadService`,
`MoverLeadService`, `DashboardService`, `RegistrarHistoricoService`.

**DTO**

`CriarLeadDTO` e `AtualizarLeadDTO` — objetos imutáveis (`readonly`) que
transportam dados validados do Request para o Service, evitando passar
`Request`/arrays soltos para as camadas internas.

**Dependency Injection**

Todas as dependências entram por construtor, o que mantém os Services
testáveis com mocks do repositório.

---

## Banco de dados

```
usuarios
  id, nome, email (unique), senha, perfil, ativo, avatar,
  ultimo_acesso, timestamps, deleted_at

leads
  id, nome, email, telefone, empresa, cargo,
  estagio (novo|contato|proposta|ganho|perdido),
  valor_estimado, origem, observacoes, tags (json),
  responsavel_id → usuarios, criado_por → usuarios,
  data_fechamento, timestamps, deleted_at

tarefas
  id, titulo, descricao, prazo, concluida, concluida_em,
  prioridade (baixa|media|alta),
  lead_id → leads, responsavel_id → usuarios,
  timestamps, deleted_at

historico_leads
  id, lead_id → leads, usuario_id → usuarios,
  tipo, descricao, dados_anteriores (json), dados_novos (json), created_at

arquivos
  id, lead_id → leads, nome_original, caminho, disco,
  tamanho, mime_type, enviado_por → usuarios, timestamps

logs_auditoria
  id, usuario_id → usuarios, acao, modelo, modelo_id,
  dados (json), ip, user_agent, created_at

personal_access_tokens (Sanctum)
```

Índices nas colunas mais consultadas: `leads.estagio`,
`leads(responsavel_id, estagio)`, `leads.created_at`,
`tarefas(lead_id, concluida)`, `tarefas(responsavel_id, prazo)`,
`historico_leads(lead_id, created_at)`.

---

## Endpoints

Base: `/api`. Autenticação por token Bearer (Sanctum), exceto o login.

```
POST   /auth/login                 público (rate limit 5/min)
POST   /auth/logout
GET    /auth/perfil

GET    /leads                      lista paginada + filtros (estagio, busca, responsavel_id, origem)
POST   /leads
GET    /leads/{id}
PUT    /leads/{id}
DELETE /leads/{id}                 soft delete
PATCH  /leads/{id}/estagio         move no pipeline
GET    /leads/{id}/historico
POST   /leads/{id}/arquivos        upload (10 MB, MIME validado)
GET    /pipeline                   leads agrupados por estágio

GET    /tarefas
POST   /tarefas
GET    /tarefas/{id}
PUT    /tarefas/{id}
DELETE /tarefas/{id}
PATCH  /tarefas/{id}/concluir

GET    /dashboard/metricas
GET    /dashboard/atividades
GET    /dashboard/funil

GET    /usuarios                   somente admin/gerente
POST   /usuarios
GET    /usuarios/{id}
PUT    /usuarios/{id}
DELETE /usuarios/{id}              desativa o usuário
```

Documentação interativa em `/api/documentation` (Swagger UI).

---

## Autenticação e segurança

- Token Sanctum com expiração de 30 dias; ao logar, os tokens anteriores do
  usuário são revogados (um token ativo por vez).
- Autorização por perfil via *Gate* `gerenciar-usuarios` (apenas
  `admin`/`gerente` acessam o módulo de usuários).
- Rate limiting: 5 tentativas/min no login, 60 req/min nas demais rotas.
- Validação de entrada com Form Requests; saída com API Resources.
- Senhas com bcrypt; `senha` fica em `$hidden` no model.
- Soft delete em leads e tarefas.
- Upload: tamanho máximo de 10 MB e MIME conferido no servidor;
  no S3 a URL de acesso é assinada com expiração de 1 hora.

---

## Testes

Backend (Pest), em `backend/tests`:

```
Unit/
  Domain/        regras puras (Lead, Tarefa)
  Application/   services com repositório mockado (Criar/Mover/Dashboard)
Feature/
  Auth/          login, logout, perfil
  Leads/         CRUD, filtros, pipeline
  Tarefas/       criação e conclusão
```

Por padrão os testes usam SQLite em memória (`phpunit.xml`), então rodam sem
depender de um MySQL externo. No CI as variáveis de ambiente apontam para o
serviço MySQL do pipeline.

```bash
php artisan test
vendor/bin/pest --coverage --min=80
```

Frontend (Vitest + Testing Library), em `frontend/src`:

```bash
npx vitest run
npm run test:coverage
```

---

## Docker

`docker-compose.yml` sobe: `nginx` (proxy), `backend` (PHP-FPM/Laravel),
`frontend` (Vite), `mysql`, `mysql_test`, `redis` e um `worker` de filas.

| Serviço | Porta |
|---------|-------|
| Nginx (entrada) | 80 |
| Frontend (Vite) | 3000 |
| MySQL | 3306 |
| MySQL (testes) | 3307 |
| Redis | 6379 |

Os Dockerfiles são multi-stage (`desenvolvimento` / `producao`).

---

## CI/CD

`.github/workflows/pipeline.yml`:

1. Lint e análise estática — Pint + PHPStan (backend); ESLint + `tsc` (frontend).
2. Testes — Pest e Vitest, com cobertura enviada ao Codecov.
3. Build — imagens Docker de backend e frontend (branch `main`).
4. Deploy — via SSH no EC2 com migrations automáticas (branch `main`).

---

## Variáveis de ambiente

Backend (`backend/.env`):

```
APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL
DB_CONNECTION (mysql|sqlite), DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
CACHE_STORE, QUEUE_CONNECTION, SESSION_DRIVER
REDIS_HOST, REDIS_PORT
SANCTUM_STATEFUL_DOMAINS, FRONTEND_URL
AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET
```

Frontend (`frontend/.env`):

```
VITE_API_URL=http://localhost/api
VITE_APP_NAME=KoraCRM
```

---

## Rodando localmente

Com Docker (recomendado):

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
docker-compose up -d
docker-compose exec backend composer install
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate --seed
docker-compose exec frontend npm install
```

Sem Docker, o backend roda com SQLite definindo `DB_CONNECTION=sqlite` no
`.env`, criando `backend/database/database.sqlite` e servindo com o servidor
embutido do PHP a partir de `backend/public`.

Acessos: frontend em `http://localhost:3000`, API em `/api`, Swagger em
`/api/documentation`.

Usuários criados pelo seeder:

| Perfil | E-mail | Senha |
|--------|--------|-------|
| admin | admin@koracrm.com.br | admin123456 |
| gerente | gerente@koracrm.com.br | gerente123456 |
| vendedor | carlos@koracrm.com.br | vendedor123456 |
| vendedor | ana@koracrm.com.br | vendedor123456 |

---

## Decisões técnicas

**Clean Architecture em vez de MVC direto** — manter regra de negócio fora do
Controller facilita testar (Services com repositório mockado) e trocar a
camada de persistência sem tocar no domínio.

**Sanctum em vez de JWT manual** — é a solução oficial do Laravel para
autenticação de SPA/API, com revogação de token simples e menos código próprio
para manter.

**Pest em vez de PHPUnit puro** — sintaxe mais enxuta para os testes; ainda
roda sobre o PHPUnit por baixo.

**SQLite em memória nos testes** — suíte roda rápido e sem depender de um
banco externo no ambiente de desenvolvimento; o CI continua exercitando MySQL.

**Repository Pattern** — desacopla o domínio do Eloquent e dá um ponto único
para otimizar consultas (eager loading, agregações do dashboard).
