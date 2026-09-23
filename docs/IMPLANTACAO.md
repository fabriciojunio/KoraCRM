# Implantação

O que está no ar hoje, e o que é preciso para subir o sistema inteiro.

## O que está publicado

Só o frontend, em <https://koracrm-frontend.vercel.app>, com a demonstração
rodando no navegador. A API não está publicada: um Laravel com MySQL e Redis
não cabe em camada gratuita do mesmo jeito que um site estático. Quem abre o
link vê o sistema funcionando com dados de exemplo.

A publicação é automática: `publicar-frontend.yml` dispara quando o CI fecha
em verde na `main`. Ela depende do segredo `VERCEL_TOKEN` no repositório e,
sem ele, o fluxo termina em verde com um aviso em vez de falhar:

```bash
gh secret set VERCEL_TOKEN
```

Sem o segredo, publicar é na mão, de dentro de `frontend/`:

```bash
npx vercel deploy --prod
```

## Subir o sistema inteiro

### Com Docker, que é o caminho curto

```bash
cp .env.example .env            # preencha as duas senhas
cp backend/.env.example backend/.env
docker compose up -d
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate --seed
```

Depois disso: interface em <http://localhost:3000>, API em
<http://localhost/api>, documentação em
<http://localhost/api/documentation> e a sonda em
<http://localhost/api/saude>.

O `.env` da raiz não tem valor padrão para senha, de propósito: subir um MySQL
com senha conhecida na porta 3306 da máquina é deixar a porta encostada.

### Exercitar o S3 sem conta na AWS

```bash
docker compose --profile aws up -d localstack
docker compose exec backend php artisan s3:preparar-local
```

E no `backend/.env`:

```
FILESYSTEM_DISK=s3
AWS_ENDPOINT=http://localstack:4566
AWS_ACCESS_KEY_ID=teste
AWS_SECRET_ACCESS_KEY=teste
AWS_USE_PATH_STYLE_ENDPOINT=true
```

A partir daí o anexo do lead vai para o bucket, a leitura passa a ser por URL
assinada de uma hora e a exclusão da LGPD apaga o objeto de verdade. O comando
recusa rodar sem `AWS_ENDPOINT`, para não criar bucket na Amazon por engano.

## Kubernetes

Os manifestos estão em [`k8s/`](../k8s/), na ordem em que se aplicam:

```bash
kubectl apply -f k8s/00-base.yaml     # namespace, configmap e o gabarito do segredo
kubectl apply -f k8s/10-api.yaml      # API (2 réplicas) e worker de fila
kubectl apply -f k8s/20-web.yaml      # frontend, service e ingress
```

O `Secret` do `00-base.yaml` é um gabarito com valor vazio. Quem publica
preenche pelo gerenciador de segredo do cluster. Segredo de verdade não entra
em repositório, e o conferidor do CI reprova se alguém gravar um.

`python k8s/conferir-manifestos.py` roda no CI e cobra o que costuma escapar
em revisão: container como root, imagem em `latest`, deployment sem limite de
recurso e porta exposta sem sonda.

### O que falta para ser produção de verdade

Os manifestos cobrem a aplicação. Banco e cache aparecem como nome de serviço
(`koracrm-mysql`, `koracrm-redis`) e a expectativa é que sejam gerenciados,
RDS e ElastiCache, ou equivalente. Rodar banco com estado dentro do cluster é
decisão separada, e não é a recomendada aqui.

Também não entram no repositório: certificado (fica com o cert-manager,
referenciado no ingress), política de rede e regra de autoescala, que dependem
do cluster.

## Migração em produção

`php artisan migrate --force` roda antes de trocar a imagem da API. As
migrações existentes são aditivas: a coluna `anonimizado_em` entra como nula,
e a troca do tipo do histórico de enum para texto aceita o que já estava
gravado. Versão antiga e nova convivem durante a troca, que é o que permite a
atualização sem parada com `maxUnavailable: 0`.
