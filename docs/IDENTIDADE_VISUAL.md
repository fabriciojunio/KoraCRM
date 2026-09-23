# Identidade visual

Guia curto para mexer na aparência do KoraCRM sem desmontar o conjunto. Cor,
fonte e forma estão em um arquivo só: `frontend/src/index.css`.

## De onde vem o visual

A referência é a **caixa de fichas do escritório comercial**, o móvel de aço
que fazia o trabalho do CRM antes de existir CRM: gaveta de aço frio, ficha
branca pautada, guia de cartolina colorida separando um grupo do outro e
etiqueta datilografada dizendo o que tem em cada gaveta.

Dentro disso, quatro decisões fixas:

1. **O funil é um fichário, e a coluna é uma guia.** A barra colorida de 3px
   no alto da coluna é a cartolina da guia aparecendo acima das fichas. É
   dali que vem a cor de cada estágio, e essa é a única cor forte da tela.
2. **O cartão é uma ficha.** Fundo branco, filete de 1px, canto reto e
   nenhuma sombra. Profundidade vem do contorno, não de borrão cinza embaixo
   do elemento.
3. **Número é datilografado.** Valor, contagem, data e código usam IBM Plex
   Mono com algarismo de largura fixa. Numa coluna de valores, número
   proporcional dança e obriga a ler duas vezes.
4. **A gaveta é a moldura.** A barra de topo e o painel da tela de entrada
   usam o aço escuro. É o que emoldura o conteúdo claro, no lugar da barra
   lateral escura que todo painel administrativo tem.

### O que ficou de fora, de propósito

Barra lateral escura com ícone colorido em quadradinho, cartão com canto
arredondado e sombra suave, gradiente violeta ou azul-para-roxo, vidro fosco,
emoji dentro da interface e as fontes Inter, Poppins e DM Sans. A versão
anterior desta tela tinha metade dessa lista, e é exatamente o desenho que
sai pronto de qualquer gerador. Se for mexer, não volte para lá.

## Trocar uma cor

As cores são variáveis CSS em **canais RGB soltos**, não hexadecimal:

```css
--cor-caneta: 27 63 156;
```

É o formato que permite ao Tailwind aplicar opacidade sobre o token
(`border-caneta/30`). Em CSS ou SVG, use `rgb(var(--cor-caneta))`. O
`tailwind.config.js` só embrulha isso em classe, não guarda valor.

| Token | Valor | Onde aparece |
| --- | --- | --- |
| `--cor-aco` | `#e4e7e9` | fundo da página, a chapa do móvel |
| `--cor-aco-fundo` | `#d6dbde` | guia inativa, coluna do funil, botão neutro no hover |
| `--cor-ficha` | `#ffffff` | ficha, painel, tabela |
| `--cor-tinta` | `#16191c` | texto |
| `--cor-tinta-suave` | `#4a5157` | texto secundário |
| `--cor-grafite` | `#6b737a` | apoio, etiqueta, contagem |
| `--cor-pauta` | `#dde1e4` | a linha entre duas linhas da lista |
| `--cor-borda` | `#b7bfc4` | contorno de ficha e de campo |
| `--cor-borda-forte` | `#2b3238` | contorno estrutural: guia ativa, diálogo |
| `--cor-gaveta` | `#151c21` | barra de topo, painel da entrada |
| `--cor-gaveta-clara` | `#273239` | guia do estágio Novo |
| `--cor-caneta` | `#1b3f9c` | ação, foco, estágio Proposta |
| `--cor-carimbo` | `#a8231b` | erro, atraso, estágio Perdido |
| `--cor-aprovado` | `#1c6b45` | estágio Ganho |
| `--cor-mostarda` | `#855f0e` | atenção, estágio Contato |

Três regras que valem a pena manter:

1. **Cor forte só carrega estado.** Azul é ação e foco, vermelho é problema,
   verde é fechado com êxito, mostarda é atenção. Cor nenhuma existe por
   enfeite: se aparecer, é porque significa alguma coisa.
2. **Texto claro só sobre `gaveta` e `gaveta-clara`.** As quatro cores de
   estágio passam contraste como fundo de etiqueta clara ou como filete, não
   como fundo de texto branco.
3. **Contorno não é tinta.** Todo filete usa `borda` ou `borda-forte`. Com
   contorno da cor do texto, uma tabela de dez linhas vira uma grade que
   compete com o conteúdo.

## Trocar a fonte

```css
--fonte-titulo: 'Familjen Grotesk';  /* títulos, nome de guia, marca */
--fonte-corpo:  'Instrument Sans';   /* texto e formulário */
--fonte-mono:   'IBM Plex Mono';     /* número, etiqueta, código */
```

A etiqueta (classe `.etiqueta`) é mono em caixa-alta com espacejamento: é a
fita da etiquetadeira. Usada em rótulo de campo, cabeçalho de tabela e título
de painel. Não use em frase: em caixa-alta, texto corrido fica ilegível a
partir de umas seis palavras.

## As peças

Em `frontend/src/components/ui`:

- `Marca`: a marca, com o símbolo da ficha com guia. `claro` para fundo escuro.
- `Fichario`: o desenho da caixa de fichas. É a única ilustração do produto;
  aparece na entrada e nas listas vazias. Não some pela tela.
- `Painel`: ficha com barra de título.
- `Etiqueta`: a tarja de estado, recebe as classes de cor de `lib/estagios`.
- `Estados`: carregando, lista vazia e falha.

As classes de botão e campo estão em `index.css` (`.botao-acao`,
`.botao-neutro`, `.campo`, `.ficha`, `.etiqueta`, `.numero`). Componente novo
usa essas classes; não invente uma segunda régua de espaçamento.

## Acessibilidade

O contorno de foco é azul de 2px com deslocamento, visível tanto na chapa
clara quanto na gaveta escura. O funil não depende de arrastar: cada ficha tem
um seletor de estágio que funciona no teclado e no leitor de tela. Cor sozinha
não informa nada: o estágio sempre vem escrito junto da tarja.
