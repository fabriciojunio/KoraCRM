# KoraCRM

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://www.docker.com)

CRM fullstack para gestão de leads, pipeline de vendas e relacionamento com clientes.

**No ar:** [koracrm-frontend.vercel.app](https://koracrm-frontend.vercel.app) — só a
interface. O Laravel não está publicado, então o que dá para ver é a navegação e as
telas, não os dados.

---

## Sobre

O **KoraCRM** é um sistema web fullstack para gestão de **leads**, **pipeline de vendas (Kanban)**,
**tarefas** e **relacionamento com clientes**, com painel de métricas e controle de acesso por perfil.

Backend em Clean Architecture com Laravel 11, frontend React + TypeScript, testes unitários e de integração, CI/CD com GitHub Actions e deploy no EC2.

### Módulos

| Módulo | Descrição |
|--------|-----------|
| **Autenticação** | Login via token (Laravel Sanctum) com RBAC: `admin`, `gerente`, `vendedor` |
| **Dashboard** | KPIs, taxa de conversão, funil de vendas e atividades recentes |
| **Leads** | CRUD com filtros, busca, tags, histórico de ações e upload de arquivos |
| **Pipeline Kanban** | Arrastar e soltar leads entre estágios (`novo → contato → proposta → ganho/perdido`) |
| **Tarefas** | Tarefas vinculadas a leads, com prazo, prioridade e conclusão |
| **Usuários** | Gestão da equipe (restrito a `admin`/`gerente`) |
| **Auditoria** | Registro histórico de todas as ações sobre os leads |

---

## Stack

| Camada | Tecnologias |
|--------|-------------|
| **Backend** | PHP 8.2 · Laravel 11 · Laravel Sanctum · Eloquent ORM |
| **Banco / Cache** | MySQL 8.0 · Redis 7 (cache e filas) |
| **Testes backend** | Pest PHP · PHPStan/Larastan · Laravel Pint |
| **Documentação API** | L5-Swagger (OpenAPI) |
| **Frontend** | React 18 · TypeScript 5 · Vite 5 · Tailwind CSS 3 |
| **Estado / Forms** | TanStack React Query 5 · React Hook Form 7 · Zod |
| **Testes frontend** | Vitest · React Testing Library · ESLint |
| **Infra** | Docker · Docker Compose · Nginx |
| **Cloud** | AWS S3 (uploads) · AWS EC2 (deploy) |
| **CI/CD** | GitHub Actions |

---

## Arquitetura

O backend segue **Clean Architecture** com separação estrita de responsabilidades em 4 camadas:

```
Http Layer        →  Controllers · Form Requests · API Resources
Application Layer  →  Services (casos de uso) · DTOs
Domain Layer       →  Interfaces (contratos) · regras de negócio
Infrastructure     →  Repositories (Eloquent)
```

**Design patterns aplicados:** Repository · Service Layer · DTO · Dependency Injection.

> A interface `LeadRepositoryInterface` (Domain) é resolvida para `EloquentLeadRepository`
> (Infrastructure) via container de DI no `AppServiceProvider`: os Services nunca
> conhecem o Eloquent diretamente.

A documentação técnica completa está em [`docs/documentacao_tecnica.md`](docs/documentacao_tecnica.md).

---

## Pré-requisitos

- **Docker** e **Docker Compose** instalados
- Portas livres: `80`, `3000`, `3306`, `3307`, `6379`

---

## Como rodar

```bash
# 1. Entrar na pasta do projeto
cd koracrm

# 2. Copiar as variáveis de ambiente
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# 3. Subir todos os containers
docker-compose up -d

# 4. Instalar dependências do backend (aguarde o MySQL ficar saudável)
docker-compose exec backend composer install

# 5. Gerar a chave da aplicação
docker-compose exec backend php artisan key:generate

# 6. Rodar migrations e seeds
docker-compose exec backend php artisan migrate --seed

# 7. Instalar dependências do frontend
docker-compose exec frontend npm install
```

### Acessos

| Serviço | URL |
|---------|-----|
| Frontend | http://localhost:3000 |
| API | http://localhost/api |
| Documentação Swagger | http://localhost/api/documentation |

---

## Credenciais de acesso

Criadas automaticamente pelo seeder (`php artisan migrate --seed`):

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Admin | `admin@koracrm.com.br` | `admin123456` |
| Gerente | `gerente@koracrm.com.br` | `gerente123456` |
| Vendedor | `carlos@koracrm.com.br` | `vendedor123456` |
| Vendedor | `ana@koracrm.com.br` | `vendedor123456` |

---

## API

Documentação interativa (Swagger UI): **http://localhost/api/documentation**

### Principais endpoints

**Autenticação**
```
POST   /api/auth/login              Login (e-mail + senha)
POST   /api/auth/logout             Logout (invalida o token atual)
GET    /api/auth/perfil             Dados do usuário autenticado
```

**Leads**
```
GET    /api/leads                   Lista com filtros e paginação
POST   /api/leads                   Cria um lead
GET    /api/leads/{id}              Detalhes do lead
PUT    /api/leads/{id}              Atualiza o lead
DELETE /api/leads/{id}              Exclui (soft delete)
PATCH  /api/leads/{id}/estagio      Move o lead no pipeline
GET    /api/leads/{id}/historico    Histórico de ações do lead
POST   /api/leads/{id}/arquivos     Upload de arquivo anexo
GET    /api/pipeline                Leads agrupados por estágio (Kanban)
```

**Tarefas**
```
GET    /api/tarefas                 Lista as tarefas do usuário
POST   /api/tarefas                 Cria uma tarefa
GET    /api/tarefas/{id}            Detalhes da tarefa
PUT    /api/tarefas/{id}            Atualiza a tarefa
DELETE /api/tarefas/{id}            Exclui (soft delete)
PATCH  /api/tarefas/{id}/concluir   Marca como concluída
```

**Dashboard**
```
GET    /api/dashboard/metricas      KPIs e métricas gerais
GET    /api/dashboard/atividades    Atividades recentes
GET    /api/dashboard/funil         Dados do funil de conversão
```

**Usuários** *(restrito a `admin`/`gerente`)*
```
GET    /api/usuarios                Lista usuários
POST   /api/usuarios                Cria usuário
GET    /api/usuarios/{id}           Detalhes do usuário
PUT    /api/usuarios/{id}           Atualiza usuário
DELETE /api/usuarios/{id}           Desativa usuário
```

---

## Testes

### Backend: Pest PHP

```bash
docker-compose exec backend php artisan test

# Com cobertura mínima de 80%
docker-compose exec backend vendor/bin/pest --coverage --min=80
```

Cobre testes **unitários** (Domain e Application, com mocks dos repositórios)
e de **integração** (Feature: autenticação, leads, tarefas via HTTP).

### Frontend: Vitest

```bash
docker-compose exec frontend npm test          # modo watch
docker-compose exec frontend npx vitest run    # execução única
docker-compose exec frontend npm run test:coverage
```

### Qualidade de código

```bash
# Backend
docker-compose exec backend vendor/bin/pint --test     # formatação
docker-compose exec backend vendor/bin/phpstan analyse # análise estática

# Frontend
docker-compose exec frontend npm run lint              # ESLint
docker-compose exec frontend npm run type-check        # TypeScript
```

---

## Estrutura do projeto

```
koracrm/
├── backend/                     # API Laravel 11
│   ├── app/
│   │   ├── Application/          # Services e DTOs (casos de uso)
│   │   ├── Domain/              # Interfaces e regras de negócio
│   │   ├── Http/                # Controllers, Requests, Resources
│   │   ├── Infrastructure/      # Repositories (Eloquent)
│   │   └── Models/              # Entidades Eloquent
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   └── tests/
│       ├── Unit/                # Domain + Application
│       └── Feature/             # Endpoints HTTP
├── frontend/                    # SPA React + TypeScript
│   └── src/
│       ├── app/                 # Páginas (dashboard, leads, pipeline, tarefas)
│       ├── components/          # Componentes compartilhados (Layout)
│       ├── features/            # Módulos por funcionalidade
│       ├── hooks/               # useAuth, useLeads…
│       ├── lib/                 # Cliente Axios
│       └── types/               # Tipos TypeScript
├── docker/                      # Dockerfiles e configs (nginx, php, mysql)
├── docs/                        # Documentação técnica
├── .github/workflows/           # Pipeline CI/CD
└── docker-compose.yml
```

---

## CI/CD

Pipeline em **GitHub Actions** ([`.github/workflows/pipeline.yml`](.github/workflows/pipeline.yml)):

1. **Lint / análise estática**: Pint + PHPStan (backend) · ESLint + `tsc` (frontend)
2. **Testes**: Pest com cobertura (backend) · Vitest com cobertura (frontend) → Codecov
3. **Build**: imagens Docker de backend e frontend (push para Docker Hub, branch `main`)
4. **Deploy**: deploy via SSH no EC2 + migrations automáticas (branch `main`)

---

## Segurança

- Autenticação por token (Laravel Sanctum) com expiração de 30 dias
- RBAC por perfil (`admin`/`gerente`/`vendedor`) via *Gate* de autorização
- Rate limiting: **5 tentativas/min** no login, **60 req/min** nas demais rotas
- Validação de toda entrada via Form Requests; saída via API Resources
- Senhas com bcrypt
- Soft delete em leads e tarefas
- Upload com validação de MIME e limite de 10 MB; URLs assinadas no S3 (expiração de 1h)
- Variáveis sensíveis apenas em `.env`

---

## Licença

Distribuído sob a licença **MIT**.
