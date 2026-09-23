# LGPD

O que o KoraCRM guarda de dado pessoal, por quanto tempo e como atender um
pedido de titular.

## Que dado pessoal existe aqui

O sistema guarda dado de duas categorias de pessoa:

**O lead**, que é a pessoa do outro lado da negociação: nome, e-mail,
telefone, empresa, cargo e o que o vendedor anotou em observações. É dado de
contato profissional, coletado no exercício da atividade comercial.

**O usuário**, que é quem trabalha na empresa: nome, e-mail, perfil de acesso
e último acesso. A senha fica com hash bcrypt e não é devolvida por nenhuma
resposta da API.

Não há dado sensível na definição do artigo 5º, inciso II: nada de saúde,
biometria, origem racial, convicção religiosa ou opinião política. Se alguém
anotar isso em observações, o campo passa a conter dado sensível, e é por isso
que observações é apagado inteiro num pedido de exclusão.

## Base legal

O tratamento se apoia no legítimo interesse (artigo 7º, inciso IX) para a
atividade comercial, e na execução de contrato quando o lead vira cliente. O
controlador é a empresa que opera a instalação, não este repositório.

## Direitos do titular

Duas rotas atendem os pedidos, ambas restritas a gerente e administrador:

```
GET    /api/leads/{id}/dados-pessoais    acesso e portabilidade
DELETE /api/leads/{id}/dados-pessoais    exclusão
```

O acesso devolve em JSON tudo que o sistema guarda sobre a pessoa, incluindo o
histórico de interações e a lista de anexos.

A exclusão **anonimiza**: apaga nome, e-mail, telefone, cargo, observações,
tags e os arquivos anexados, no banco e no disco. Mantém estágio, valor,
datas e histórico, que são dado da empresa sobre o próprio negócio. A linha
fica marcada com `anonimizado_em`, e o pedido é gravado no histórico com autor
e data. O raciocínio está no [ADR 3](docs/adr/0003-anonimizar-em-vez-de-apagar.md).

Correção de dado é o `PUT /api/leads/{id}` de sempre, e fica registrada no
histórico.

## Retenção

Lead excluído usa exclusão lógica e fica no banco com `deleted_at` preenchido:
apagar por engano é comum no comercial e o histórico precisa continuar
fechando. A limpeza definitiva é decisão de quem opera, e a recomendação é
purgar o que está excluído há mais de dois anos.

O log da aplicação carrega o identificador da requisição e o id do usuário.
Não carrega corpo de requisição, então não guarda dado de lead. A retenção
recomendada é de 90 dias.

## Segurança do tratamento

Acesso por token com expiração, autorização por perfil negando por padrão,
senha com bcrypt, tráfego sob HTTPS com HSTS, anexo em bucket privado com URL
assinada de uma hora, e trilha de auditoria de toda alteração de lead com
autor e data.

## Incidente

Em caso de vazamento, a ANPD e os titulares precisam ser comunicados em prazo
razoável, que a orientação vigente trata como dois dias úteis. O caminho de
comunicação e o responsável ficam com a empresa que opera a instalação. Falha
de segurança neste código se reporta pelo [SECURITY.md](SECURITY.md).
