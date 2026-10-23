# Demonstração

<https://koracrm-frontend.vercel.app> → **Entrar como demonstração**.

Não precisa de cadastro, e nada sai do navegador. A faixa amarela no topo fica
visível o tempo todo declarando o modo.

## Roteiro de cinco minutos

1. **Painel.** Os seis números do topo e, abaixo, o funil por estágio e os
   últimos movimentos com autor e data. Repare em "Atrasadas", que fica em
   vermelho quando existe tarefa fora do prazo.

2. **Leads.** Nove fichas. Busque por `nexus`: a busca olha nome, e-mail e
   empresa. Filtre por "Ganho" e você fica com a Alfa Sistemas. Busque por
   algo que não existe e aparece a lista vazia com o desenho do fichário, não
   uma tabela em branco.

3. **Nova ficha.** Botão no topo. Só o nome é obrigatório. Gravando, o lead
   aparece na lista e na primeira guia do funil, porque lead nasce sempre em
   `novo`, independente do que for enviado.

4. **Funil.** Arraste uma ficha para a guia seguinte, ou use o seletor dentro
   dela, que é o caminho que funciona no teclado. Tente mover a Alfa Sistemas,
   que está em Ganho: a ficha não tem seletor e o arrastar é recusado, porque
   `ganho` e `perdido` são estados terminais. A recusa é do domínio, e o mesmo
   teste existe no backend.

5. **Tarefas.** Duas estão vencidas e aparecem em vermelho com a data. Marque
   uma como concluída: ela sai de "Em aberto" e reaparece em "Concluídas".

6. **Equipe.** A tabela de perfis e o que cada um alcança. Vendedor enxerga só
   as próprias fichas, e a regra vale na policy do servidor, não na tela.

## O que o link não mostra

O back-end não está publicado, então upload de anexo, geração de URL assinada,
trilha de auditoria gravada e os dois endpoints da LGPD só rodam com o
ambiente local no ar. O caminho está em [IMPLANTACAO.md](IMPLANTACAO.md), e
leva três comandos.

## Contas do ambiente local

As contas são criadas por `php artisan migrate --seed`. O seeder não usa senhas
fixas: por padrão, gera uma senha aleatória forte por perfil e a mostra uma vez
no console. Guarde-a ao executar o seed. Para escolher senhas em ambiente local,
defina `SEED_ADMIN_PASSWORD`, `SEED_GERENTE_PASSWORD` e `SEED_VENDEDOR_PASSWORD`
no `backend/.env` antes de criar as contas.

| Perfil | E-mail |
| --- | --- |
| Administrador | `admin@koracrm.com.br` |
| Gerente | `gerente@koracrm.com.br` |
| Vendedor | `carlos@koracrm.com.br` |
| Vendedor | `ana@koracrm.com.br` |

Para ver a diferença entre perfis, entre com a conta de vendedor criada para o
ambiente local e repare que a guia Equipe some e a lista de leads encolhe.
