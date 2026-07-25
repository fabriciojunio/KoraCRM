# Política de Segurança

## Versões com suporte

O suporte de segurança acompanha a branch `main` e a última release estável.
Correções são aplicadas apenas na versão mais recente.

## Como reportar uma vulnerabilidade

Não abra issues públicas para falhas de segurança.

Envie os detalhes para **fabricioad444@gmail.com** com:

- descrição da falha e do impacto;
- passos para reproduzir (ou prova de conceito);
- versão/commit afetado.

O prazo de primeira resposta é de até 72 horas. Após a confirmação,
combinamos uma janela para correção e divulgação coordenada.

## Práticas adotadas no projeto

- Autenticação por token (Laravel Sanctum) com expiração e um token ativo por usuário.
- Autorização por perfil (`admin`/`gerente`/`vendedor`) via Policies e Gates, negando por padrão.
- Senhas com hash bcrypt; nunca trafegam nem são retornadas em respostas.
- Consultas via Eloquent com bindings; buscas com `LIKE` escapam curingas.
- Validação de entrada em Form Requests e saída padronizada em API Resources.
- Rate limiting no login e nas demais rotas.
- Upload com validação de MIME e tamanho; nomes de arquivo gerados pelo servidor.
- Cabeçalhos de segurança HTTP aplicados na aplicação e reforçados no nginx.
- `display_errors` e `expose_php` desabilitados; versões de stack não expostas.
- Segredos apenas em `.env` (fora do versionamento).

## Recomendações de operação

- Rodar `composer audit` e `npm audit` periodicamente e aplicar atualizações de segurança.
- Servir sempre atrás de HTTPS (o HSTS é emitido apenas sob conexão segura).
- Revisar e rotacionar as credenciais de demonstração antes de qualquer uso real.
