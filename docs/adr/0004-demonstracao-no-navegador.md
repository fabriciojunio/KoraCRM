# 4. A demonstração roda no navegador, sem servidor

Data: 2026-09-23 · Status: aceito

## Contexto

Só o frontend está publicado. Sem back-end no ar, quem abre o link encontraria
uma tela de login que não aceita ninguém.

## Decisão

Um botão de entrada na demonstração grava um token fixo no navegador. Os hooks
de dados conferem esse token e respondem com um conjunto de exemplo, incluindo
nas operações de escrita, que alteram o conjunto em memória da sessão.

## Consequências

Dá para percorrer o sistema inteiro, criar ficha, mover no funil e concluir
tarefa, sem cadastro e sem servidor. Os testes de ponta a ponta rodam sobre
esse modo, o que dá 22 testes de navegador no CI sem subir banco.

Em troca, existe um caminho de dado que não é o de produção, e ele precisa ser
lembrado ao mexer nos hooks. A faixa amarela no topo declara o modo, e a
demonstração nunca fica ligada por padrão: só depois do clique.

As datas do conjunto são relativas ao dia de hoje. Com data fixa, o link
envelhece e a tela parece um arquivo abandonado.
