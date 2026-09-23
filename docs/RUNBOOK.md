# Runbook

O que fazer quando o sistema está no ar e alguma coisa vai mal. Escrito para
quem pega o chamado, não para quem escreveu o código.

## Antes de qualquer coisa: o identificador

Toda resposta da API sai com `X-Request-Id`. Se o cliente mandou uma captura
de tela com o código, é por ele que se procura:

```bash
kubectl logs -n koracrm -l app=koracrm-api --since=6h | grep <identificador>
```

Se o cliente não tem o código, peça a hora com minuto e o e-mail da conta: o
log leva o usuário no contexto.

## A sonda

```bash
curl -s https://crm.exemplo.com.br/api/saude | jq
```

- `status: no ar` com as duas dependências em `true`: aplicação inteira.
- `status: degradado` e `banco: false`: a API subiu e o MySQL não responde.
  Olhe o banco antes de mexer na aplicação.
- `status: degradado` e `cache: false`: Redis fora. A aplicação continua
  respondendo, mais devagar, porque o painel perde o cache. Não é urgência de
  madrugada.
- Não responde nada: nenhum pod de pé, ou o ingress caiu. `kubectl get pods -n
  koracrm`.

## Sintomas conhecidos

### "Movi o lead e o painel não mudou"

Era o defeito mais comum antes do cache passar a ser invalidado. Se voltar a
acontecer, o observador parou de rodar:

```bash
kubectl exec -n koracrm deploy/koracrm-api -- php artisan tinker \
  --execute="Cache::forget('painel.metricas');"
```

Isso resolve o sintoma na hora. A causa é o observador de `Lead` e `Tarefa`
não estar registrado no `AppServiceProvider`, e existe teste cobrindo.

### "O sistema me desconectou sozinho"

Token do Sanctum vale 30 dias. Passou disso, a API responde 401, o frontend
limpa a sessão e leva para a entrada. É comportamento esperado.

Se acontecer com quem entrou hoje, confira se `APP_KEY` mudou entre réplicas:
chave diferente invalida token de todo mundo.

### "Não consigo anexar arquivo"

Na ordem:

1. Tamanho acima de 10 MB e tipo fora da lista são recusados com mensagem
   clara. Confirme com o cliente qual arquivo é.
2. `client_max_body_size` no ingress (`proxy-body-size: 10m`) e
   `upload_max_filesize` no php.ini precisam bater com o limite da aplicação.
   Se um for menor, o erro é 413 e vem do Nginx, sem passar pelo Laravel.
3. Com `FILESYSTEM_DISK=s3`, credencial vencida dá erro na hora do envio. Olhe
   o log pelo identificador.

### "Está muito lento"

O caminho caro é a listagem de leads com busca: `LIKE '%termo%'` não usa
índice. Com base pequena não aparece; passando de algumas dezenas de milhares
de linhas, aparece. A saída é índice de texto completo no MySQL, e está
registrada como dívida, não implementada.

### Fila parada

O worker encerra sozinho de hora em hora (`--max-time=3600`) e o Kubernetes
sobe outro. Se a fila acumular:

```bash
kubectl logs -n koracrm deploy/koracrm-worker --tail=100
kubectl rollout restart -n koracrm deploy/koracrm-worker
```

## Atualizar versão

1. CI verde na `main`.
2. `php artisan migrate --force` com a imagem nova, antes de trocar a antiga.
   As migrações são aditivas e as duas versões convivem.
3. `kubectl set image` na API e no frontend. A atualização é sem parada
   (`maxUnavailable: 0`).
4. Confira a sonda e olhe o log dos cinco primeiros minutos.

Para voltar atrás: `kubectl rollout undo`. Migração não volta sozinha, e nenhuma
das existentes derruba coluna, então a versão anterior roda com o banco novo.

## Pedido de LGPD

Chegou pedido de titular pelo canal oficial:

```bash
# o que a empresa guarda sobre a pessoa
curl -H "Authorization: Bearer <token de gerente>" \
     https://crm.exemplo.com.br/api/leads/<id>/dados-pessoais

# exclusão
curl -X DELETE -H "Authorization: Bearer <token de gerente>" \
     https://crm.exemplo.com.br/api/leads/<id>/dados-pessoais
```

A exclusão apaga nome, contato, cargo, observações e os anexos, e mantém
estágio, valor e histórico. Fica registrada com autor e data. Ver
[LGPD.md](../LGPD.md).
