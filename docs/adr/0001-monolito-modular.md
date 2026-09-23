# 1. Monólito modular, não micro-serviço

Data: 2026-09-23 · Status: aceito

## Contexto

O KoraCRM tem cinco entidades e atende uma equipe comercial pequena. A
tentação, num projeto de portfólio, é quebrar em serviço de lead, serviço de
tarefa e serviço de usuário, porque fica bonito no diagrama.

## Decisão

Um monólito com camadas separadas: Http, Application, Domain e Infrastructure.
A separação acontece dentro do processo, por interface e injeção de
dependência.

## Consequências

Mover um lead e gravar o histórico acontece numa transação só do MySQL. Não
existe outbox, nem saga, nem consistência eventual para explicar.

O preço é que a separação depende de disciplina, não do compilador: nada
impede alguém de chamar Eloquent dentro de um Service. O que segura é a
revisão e o teste de unidade do caso de uso, que usa um repositório falso e
quebra na hora se alguém puxar o banco para dentro.

Se um dia a parte de relatório precisar escalar sozinha, ela sai primeiro: já
está isolada atrás do `LeadRepositoryInterface`.
