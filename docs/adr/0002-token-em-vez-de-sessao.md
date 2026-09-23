# 2. Token do Sanctum, e não sessão com cookie

Data: 2026-09-23 · Status: aceito

## Contexto

O frontend é uma aplicação de página única, publicada separada da API. Sanctum
oferece os dois modos: sessão com cookie (SPA) e token pessoal.

## Decisão

Token pessoal (`createToken`), guardado pelo navegador e enviado no cabeçalho
`Authorization`.

## Consequências

A API fica sem estado, o que permite duas réplicas atrás do balanceador sem
compartilhar sessão. Também facilita a vida de quem for consumir a API de
outro lugar, um robô ou um aplicativo de celular.

O custo é conhecido: token em `localStorage` é alcançável por JavaScript, o
que transforma qualquer XSS em roubo de sessão. O que cobre isso é a política
de conteúdo restritiva, o React escapando por padrão e a validação de toda
entrada. Cookie `HttpOnly` seria mais seguro nesse ponto específico, e traria
CSRF junto, além de exigir domínio comum entre as duas pontas, que não existe
quando o frontend está na Vercel.

A expiração é de 30 dias. É longo para um banco, e razoável para uma
ferramenta comercial interna onde relogar todo dia irrita mais do que protege.
