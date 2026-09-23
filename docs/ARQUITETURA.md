# Arquitetura

O KoraCRM é um monólito modular: uma API em Laravel e uma aplicação de página
única em React, cada uma na sua pasta, com Docker amarrando tudo para rodar.

Não é micro-serviço, e é de propósito. O domínio inteiro cabe em cinco
entidades (usuário, lead, tarefa, histórico e arquivo) e o volume é o de uma
equipe comercial pequena. Quebrar isso em serviço separado hoje adicionaria
transação distribuída e nenhuma vantagem.

## As quatro camadas do backend

```
Http            Controllers · Form Requests · Resources · Middleware
Application     Services (casos de uso) · DTOs
Domain          Interfaces · regras que não dependem de framework
Infrastructure  Repositories (Eloquent)
```

A regra que sustenta a separação: **a camada de aplicação não conhece
Eloquent**. `CriarLeadService` recebe um DTO e fala com
`LeadRepositoryInterface`. Quem resolve a interface para
`EloquentLeadRepository` é o container, no `AppServiceProvider`. É o que
permite testar o caso de uso com um repositório de mentira, sem banco.

Onde a regra é afrouxada, e por quê: consultas de leitura simples
(`DashboardService` contando tarefa, `TarefaController` listando) falam direto
com o modelo. Criar repositório para `count()` seria cerimônia sem ganho. A
fronteira é firme na escrita, que é onde mora regra de negócio.

### As regras que moram no domínio

- Lead nasce sempre em `novo`, qualquer que seja o dado enviado.
- `ganho` e `perdido` são estados terminais: lead fechado não volta ao funil.
- Tarefa concluída não conclui de novo (responde 409, não 200).
- Vendedor enxerga só as fichas em que é responsável ou criador. A regra vale
  na policy, no servidor. A tela esconder o botão é conveniência, não
  segurança.

## Fluxo de uma requisição

```
navegador → Nginx → PHP-FPM → middleware → Controller → Service → Repository → MySQL
                                   ↓
                         IdentificadorRequisicao (X-Request-Id no log)
                         SecurityHeaders (CSP, HSTS, nosniff)
```

Toda resposta sai com `X-Request-Id`. É o número que o cliente lê na tela e
que permite achar a linha exata no log, sem procurar por horário.

## Cache

As métricas do painel ficam cinco minutos no Redis. Um observador em `Lead` e
`Tarefa` derruba a chave quando qualquer uma das duas muda, senão o painel
mostra número velho e parece que a alteração se perdeu. É a troca clássica:
cache serve para aguentar quem fica com o painel aberto, não para atrasar
informação.

## Frontend

```
src/
  app/           uma pasta por tela (painel, leads, funil, tarefas, equipe)
  components/    layout e as peças de interface
  features/      autenticação
  hooks/         sessão e consultas do lead
  lib/           cliente HTTP, formatação, vocabulário dos estágios, demonstração
  types/         os contratos que a API devolve
```

O React Query cuida de cache, recarga e estado de carregamento. Não existe
Redux nem contexto global de dados: estado de servidor fica no React Query, e
estado de tela fica no `useState` da tela.

O vocabulário de estágio (nome, cor, se fecha o lead) mora em
`lib/estagios.ts`. Antes estava repetido em três telas, com cores diferentes
em cada uma.

## Modo de demonstração

Entrar pela demonstração grava um token fixo no navegador. A partir dele, os
hooks respondem com dados de mentira em vez de chamar a API, inclusive na
escrita: criar, mover e excluir alteram o conjunto da sessão. Serve porque a
API não está publicada, e porque uma demonstração que não deixa clicar em nada
não mostra o sistema.

As datas são calculadas a partir de hoje. Data fixa envelhece e, meses depois,
o link parece arquivo morto.

## Banco

Cinco tabelas. `leads` guarda índice em `estagio`, em
`(responsavel_id, estagio)` e em `created_at`, que são os três caminhos de
leitura da listagem e do funil. Lead e tarefa usam exclusão lógica: no
comercial, apagar por engano é comum e o histórico precisa continuar fechando.

O tipo do histórico é texto, não enum. Com enum, cada evento novo virava
alteração de estrutura numa tabela que só cresce. A lista válida está em
`HistoricoLead::TIPOS` e é conferida antes da gravação.

## Decisões registradas

Em [`adr/`](adr/), uma por arquivo, com o contexto e o que foi descartado.
