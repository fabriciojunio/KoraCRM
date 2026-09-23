# KoraCRM

[![CI](https://github.com/fabriciojunio/KoraCRM/actions/workflows/ci.yml/badge.svg)](https://github.com/fabriciojunio/KoraCRM/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black)](https://react.dev)
[![License](https://img.shields.io/badge/license-MIT-black)](LICENSE)

A CRM for small sales teams: leads, sales funnel, tasks and an audit trail,
with role-based access control.

**Live:** [koracrm-frontend.vercel.app](https://koracrm-frontend.vercel.app).
Click *Entrar como demonstração* (enter the demo). No sign-up. The front end
is the only part deployed, so the demo runs entirely in the browser with
sample data.

The interface, the domain vocabulary and the rest of the documentation are in
Portuguese, which is the language of the people who would use this system.
This page is the short version for anyone who does not read it.

---

## What it does

| Module | What it solves |
| --- | --- |
| **Leads** | Contact card with value, source and notes; search and stage filter |
| **Funnel** | Five stages as index-card tabs, by drag or by the card's selector |
| **Tasks** | Attached to a lead, with due date, priority and overdue marking |
| **Dashboard** | Funnel numbers, value per stage and the latest moves with author |
| **Team** | Who has access and how far each role reaches |
| **Audit** | Every change to a lead recorded with author, date and diff |
| **Privacy** | Access and erasure of a data subject's personal data (Brazilian LGPD) |

Two domain rules the system will not let you break, tested on both sides: a
lead always starts at `novo`, and a lead in `ganho` or `perdido` never goes
back into the funnel.

## Stack

PHP 8.2 with Laravel 11 and Sanctum, MySQL 8, Redis 7, React 18 with
TypeScript, Vite and Tailwind. Tests with Pest, Vitest and Playwright; static
analysis with PHPStan and Larastan. Docker, Kubernetes manifests, S3 for
attachments.

## Running it

```bash
cp .env.example .env              # fill in the two passwords
cp backend/.env.example backend/.env
docker compose up -d
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```

Interface at <http://localhost:3000>, API at <http://localhost/api>, Swagger at
<http://localhost/api/documentation>, health probe at
<http://localhost/api/saude>.

## Architecture

A modular monolith in four layers: Http, Application, Domain and
Infrastructure. The rule that holds it together is that **the application
layer never touches Eloquent**: services take a DTO and talk to
`LeadRepositoryInterface`, resolved by the container. That is what makes use
cases testable without a database.

Details in [docs/ARQUITETURA.md](docs/ARQUITETURA.md); the decisions, with the
context and what was rejected, in [docs/adr/](docs/adr/).

## Tests

81 backend tests (Pest), 15 front-end unit tests (Vitest) and 22 end-to-end
tests (Playwright, desktop and mobile) running against the demo mode. CI also
runs the backend suite against a real MySQL 8, because production is not
SQLite.

```bash
make testar && make e2e && make revisar
```

## License

MIT. See [LICENSE](LICENSE).
