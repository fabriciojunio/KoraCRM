.DEFAULT_GOAL := ajuda
.PHONY: ajuda subir descer logs instalar banco testar testar-backend testar-frontend e2e revisar formatar imagens

ajuda: ## Lista os alvos
	@grep -E '^[a-z-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-18s %s\n", $$1, $$2}'

subir: ## Sobe o ambiente local
	docker compose up -d

descer: ## Derruba o ambiente e mantém o volume do banco
	docker compose down

logs: ## Acompanha o log da API
	docker compose logs -f backend

instalar: ## Instala as dependências das duas pontas
	cd backend && composer install
	cd frontend && npm ci

banco: ## Roda as migrações e popula com dados de exemplo
	docker compose exec backend php artisan migrate --seed

testar: testar-backend testar-frontend ## Roda a bateria inteira, menos a de ponta a ponta

testar-backend: ## Pest, em SQLite na memória
	cd backend && php artisan test

testar-frontend: ## Vitest
	cd frontend && npx vitest run

e2e: ## Playwright sobre o modo de demonstração
	cd frontend && npx playwright test

revisar: ## O mesmo que o CI cobra
	cd backend && vendor/bin/pint --test
	cd backend && vendor/bin/phpstan analyse --memory-limit=1G
	cd frontend && npm run lint
	cd frontend && npm run type-check

formatar: ## Aplica o Pint no backend
	cd backend && vendor/bin/pint

imagens: ## Constrói as imagens de produção
	docker build -f docker/backend/Dockerfile --target producao -t koracrm-api ./backend
	docker build -f docker/frontend/Dockerfile --target producao -t koracrm-web .
