# 5. O tipo do histórico é texto, e a lista válida fica no código

Data: 2026-09-23 · Status: aceito

## Contexto

`historico_leads.tipo` era um enum do banco com seis valores. O pedido da LGPD
trouxe o sétimo, e a gravação passou a falhar com violação de restrição, em
SQLite e em MySQL.

## Decisão

A coluna virou `varchar(40)`. A lista válida é `HistoricoLead::TIPOS`, e o
serviço que grava recusa tipo fora dela.

## Consequências

Evento novo não exige mais alteração de estrutura numa tabela que só cresce, e
que em MySQL grande significa janela de manutenção.

O banco deixa de garantir o domínio do campo, e passa a confiar na aplicação.
A troca é aceitável porque só existe um caminho de escrita, o
`RegistrarHistoricoService`, e ele valida. Se um dia houver escrita por fora,
a decisão precisa ser revista.
