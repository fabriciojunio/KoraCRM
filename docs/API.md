# API

Base `/api`, autenticação por token Bearer do Sanctum. A documentação
interativa fica em `/api/documentation` (Swagger), gerada das anotações nos
controllers.

Resposta de erro sempre traz `mensagem`, e a de validação traz `errors` com o
campo. Toda resposta leva `X-Request-Id`, que é o que se procura no log.

## Público

```
GET    /saude                        sonda de saúde (30 req/min)
POST   /auth/login                   e-mail e senha (3 tentativas/min)
```

## Sessão

```
POST   /auth/logout                  invalida o token atual
GET    /auth/perfil                  dados de quem está autenticado
```

## Leads

```
GET    /leads                        lista paginada
                                     filtros: estagio, busca, responsavel_id,
                                     origem, por_pagina, pagina
POST   /leads                        cria (nasce sempre em "novo")
GET    /leads/{id}
PUT    /leads/{id}
DELETE /leads/{id}                   exclusão lógica
PATCH  /leads/{id}/estagio           move no funil; recusa lead fechado
GET    /leads/{id}/historico         trilha de auditoria
POST   /leads/{id}/arquivos          anexo, 10 MB, tipo validado
GET    /pipeline                     leads agrupados por estágio
```

## Dados pessoais (LGPD)

```
GET    /leads/{id}/dados-pessoais    acesso e portabilidade
DELETE /leads/{id}/dados-pessoais    anonimiza; só gerente e administrador
```

## Tarefas

```
GET    /tarefas                      vendedor vê só as próprias
POST   /tarefas                      exige lead_id que o usuário possa ver
GET    /tarefas/{id}
PUT    /tarefas/{id}
DELETE /tarefas/{id}                 exclusão lógica
PATCH  /tarefas/{id}/concluir        responde 409 se já estiver concluída
```

## Painel

```
GET    /dashboard/metricas           números do funil (cache de 5 min)
GET    /dashboard/atividades         últimos movimentos
GET    /dashboard/funil              contagem por estágio
```

## Usuários

Restrito a `admin` e `gerente`, pelo gate `gerenciar-usuarios`.

```
GET    /usuarios
POST   /usuarios
GET    /usuarios/{id}
PUT    /usuarios/{id}
DELETE /usuarios/{id}                desativa, não apaga
```

## Limites

Login aceita 3 tentativas por minuto e por IP. As demais rotas autenticadas
aceitam 60 por minuto. Estourando, a resposta é 429 e o frontend avisa em vez
de tentar de novo sozinho.
