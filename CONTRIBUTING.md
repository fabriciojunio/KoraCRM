# Como mexer neste projeto

## Começar

```bash
git clone https://github.com/fabriciojunio/KoraCRM.git
cd KoraCRM
cp .env.example .env && cp backend/.env.example backend/.env   # preencha as senhas
make subir
make banco
```

Sem Docker, dá para trabalhar só no frontend: `cd frontend && npm ci && npm run dev`.
A demonstração roda no navegador e não precisa de API.

## Antes de abrir uma proposta de mudança

```bash
make revisar   # Pint, PHPStan, ESLint e checagem de tipo
make testar    # Pest e Vitest
make e2e       # Playwright, só quando mexer na interface
```

É o mesmo que o CI cobra. Rodar antes economiza uma volta.

## Padrões que valem aqui

**Português no domínio.** Classe, método, variável, rota e coluna usam o
vocabulário de quem usa o sistema: `lead`, `estagio`, `tarefa`, `responsavel`.
Não existe `LeadService` com `stage`. O que fica em inglês é o que o framework
impõe (`index`, `store`, `handle`) e termo técnico consagrado.

**Acento certo.** Em texto de tela, em comentário, em mensagem de erro e em
mensagem de commit. `não`, `estágio`, `histórico`. Identificador de código
continua sem acento, porque é identificador.

**Comentário explica decisão, não repete o código.** Se o comentário diz o que
a linha abaixo faz, ele sobra. Se diz por que foi feito assim e o que foi
descartado, fica.

**Regra de negócio no domínio, não na tela.** Esconder o botão é conveniência.
A recusa acontece no servidor, com teste.

## Testes

- Unidade (`tests/Unit`) para regra de domínio e caso de uso, com repositório
  falso, sem banco.
- Integração (`tests/Feature`) para o caminho HTTP inteiro, em SQLite na
  memória. O CI roda a mesma bateria contra MySQL 8.
- Ponta a ponta (`frontend/e2e`) com Playwright sobre o modo de demonstração,
  no desktop e no celular.

Teste descreve comportamento, não implementação. `não move lead já fechado` é
nome de teste; `testaMoverLead2` não é.

Correção de defeito entra com o teste que pega o defeito. Se o teste passa
antes da correção, ele está olhando para o lugar errado.

## Commits

Conventional Commits, com o escopo em ASCII e a prosa em português acentuado:

```
fix(funil): recusa mover lead já fechado
feat(lgpd): anonimiza dados pessoais do lead a pedido do titular
```

O corpo explica o porquê e o que foi descartado. Quem lê daqui a seis meses
quer o motivo, não o diff, que já está ali do lado.

Nada de travessão nas mensagens.

## Proposta de mudança

Branch a partir da `main`, uma mudança por proposta, CI verde. Na descrição:
o que muda, por que, e como testar na mão. Se mexeu na aparência, uma captura
de tela poupa muita discussão.
