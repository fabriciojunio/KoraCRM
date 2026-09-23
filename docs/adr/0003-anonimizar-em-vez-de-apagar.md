# 3. Pedido de exclusão da LGPD anonimiza, não apaga a linha

Data: 2026-09-23 · Status: aceito

## Contexto

O titular pode pedir a exclusão dos dados pessoais (LGPD, artigo 18). A leitura
literal seria `DELETE FROM leads`.

## Decisão

Apagar o que identifica a pessoa (nome, e-mail, telefone, cargo, observações e
os anexos, no banco e no disco) e manter o registro comercial: estágio, valor,
datas e histórico. A linha fica marcada com `anonimizado_em`.

## Consequências

O funil continua fechando. Apagar a linha inteira levaria junto o valor
fechado e a contagem do mês, que são dado da empresa sobre o próprio negócio,
não dado do titular.

O campo de observações vai junto porque é onde alguém anota "ligar no celular
dele, 14 99999-0000". Texto livre é o lugar onde dado pessoal se esconde.

Um pedido repetido não sobrescreve a data do primeiro: o registro do
atendimento é a prova de que a empresa cumpriu, e a data importa.

A operação é restrita a gerente e administrador, e fica gravada no histórico
com autor e data.
