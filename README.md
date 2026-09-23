# KoraCRM

[![CI](https://github.com/fabriciojunio/KoraCRM/actions/workflows/ci.yml/badge.svg)](https://github.com/fabriciojunio/KoraCRM/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![Licença](https://img.shields.io/badge/licença-MIT-black)](LICENSE)

CRM para equipe comercial pequena: lead, funil de vendas, tarefa e histórico de
atendimento, com controle de acesso por perfil.

**No ar:** [koracrm-frontend.vercel.app](https://koracrm-frontend.vercel.app).
Clique em **Entrar como demonstração**, sem cadastro. É só a interface: a API não está
publicada, e a demonstração roda no próprio navegador com dados de exemplo.
O roteiro de cinco minutos está em [docs/DEMONSTRACAO.md](docs/DEMONSTRACAO.md).

---

## O que o sistema faz

| Módulo | O que resolve |
| --- | --- |
| **Leads** | Ficha com contato, valor, origem e observações; busca e filtro por estágio |
| **Funil** | Cinco estágios em guias, arrastando ou pelo seletor da ficha |
| **Tarefas** | Vinculadas ao lead, com prazo, prioridade e marcação de atraso |
| **Painel** | Números do funil, valor por estágio e os últimos movimentos com autor |
| **Equipe** | Quem tem acesso e até onde vai cada perfil |
| **Auditoria** | Toda alteração de lead gravada com autor, data e o que mudou |
| **LGPD** | Acesso e exclusão dos dados pessoais do titular, sem perder o histórico |

Duas regras de domínio que o sistema não deixa furar, e que têm teste dos dois
lados: lead nasce sempre em `novo`, e lead em `ganho` ou `perdido` não volta
para o funil.

## Stack

| Camada | O quê |
| --- | --- |
| **API** | PHP 8.2 · Laravel 11 · Sanctum · Eloquent |
| **Banco e cache** | MySQL 8 · Redis 7 (cache e fila) |
| **Interface** | React 18 · TypeScript 5 · Vite 5 · Tailwind 3 · React Query |
| **Testes** | Pest · Vitest · Playwright · PHPStan/Larastan · Pint · ESLint |
| **Infra** | Docker · Kubernetes · Nginx · S3 (anexos) |

## Rodar

```bash
cp .env.example .env              # preencha as duas senhas
cp backend/.env.example backend/.env
docker compose up -d
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```

Interface em <http://localhost:3000>, API em <http://localhost/api>, Swagger em
<http://localhost/api/documentation>, sonda em <http://localhost/api/saude>.
As rotas estão listadas em [docs/API.md](docs/API.md).

As contas de exemplo estão em [docs/DEMONSTRACAO.md](docs/DEMONSTRACAO.md).
O `make` lista o resto dos comandos.

## Arquitetura

Monólito com quatro camadas no backend:

```
Http            Controllers · Form Requests · Resources · Middleware
Application     Services (casos de uso) · DTOs
Domain          Interfaces · regras de negócio
Infrastructure  Repositories (Eloquent)
```

A regra que sustenta tudo: **a camada de aplicação não conhece Eloquent**.
`CriarLeadService` recebe um DTO e conversa com `LeadRepositoryInterface`; quem
resolve a interface é o container. É o que permite testar caso de uso sem
banco.

Detalhes em [docs/ARQUITETURA.md](docs/ARQUITETURA.md), e as decisões com o
contexto de cada uma em [docs/adr/](docs/adr/).

## Testes

```bash
make testar        # Pest (81) e Vitest (15)
make e2e           # Playwright (22, desktop e celular)
make revisar       # Pint, PHPStan, ESLint, tipos
```

Três níveis: unidade para regra de domínio com repositório falso, integração
para o caminho HTTP inteiro, e ponta a ponta no navegador sobre o modo de
demonstração. O CI roda a bateria do backend também contra um MySQL 8 de
verdade, porque produção não é SQLite.

Foi um teste de ponta a ponta que achou o defeito da ficha que não gravava
quando a origem ficava em branco.

## Segurança

Token do Sanctum com expiração, perfis (`admin`/`gerente`/`vendedor`) em
policies que negam por padrão, limite de tentativa no login, validação de toda
entrada, senha com bcrypt, cabeçalho de segurança e política de conteúdo na
API e no Nginx, anexo com validação de tipo e tamanho e URL assinada de uma
hora no S3. Imagens de produção rodam sem privilégio.

O que fazer ao encontrar uma falha: [SECURITY.md](SECURITY.md).
Dado pessoal, base legal e direitos do titular: [LGPD.md](LGPD.md).

## Visual

A referência é a caixa de fichas do escritório comercial: chapa de aço, ficha
branca pautada, guia de cartolina separando os estágios e etiqueta
datilografada. Cor forte só aparece carregando estado. O guia, com o que ficou
de fora de propósito, está em [docs/IDENTIDADE_VISUAL.md](docs/IDENTIDADE_VISUAL.md).

## Operação

[docs/IMPLANTACAO.md](docs/IMPLANTACAO.md) para subir, inclusive no Kubernetes
e com o S3 local do LocalStack. [docs/RUNBOOK.md](docs/RUNBOOK.md) para quando
alguma coisa vai mal com o sistema no ar.

## Licença

MIT. Ver [LICENSE](LICENSE).
