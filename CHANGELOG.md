# Mudanças

Formato baseado no [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/).

## [Não publicado]

### Adicionado

- Atendimento a pedido de LGPD: duas rotas para acesso e exclusão dos dados
  pessoais do lead, com anonimização que preserva o histórico comercial.
- Sonda de saúde em `/api/saude`, sem autenticação e sem expor infraestrutura.
- `X-Request-Id` em toda resposta, também no contexto do log.
- Tela de Equipe, mostrando o alcance de cada perfil.
- 22 testes de ponta a ponta com Playwright sobre o modo de demonstração, no
  desktop e no celular.
- Manifestos do Kubernetes com conferidor rodando no CI.
- Perfil `aws` no docker compose com LocalStack, e comando de artisan que cria
  o bucket de uploads local.
- Documentação: arquitetura, identidade visual, implantação, runbook,
  demonstração, LGPD, contribuição e cinco ADRs.

### Alterado

- Visual refeito em cima da caixa de fichas do escritório comercial. A
  navegação virou guia de fichário no topo, e as rotas foram para português.
- O modo de demonstração passou a funcionar na escrita: criar, mover e excluir
  alteram os dados da sessão.
- O CI passou a cobrar formatação, análise estática, cobertura, lint, tipo,
  teste de navegador, construção das imagens e conferência dos manifestos. A
  publicação na Vercel saiu para um fluxo próprio.
- O tipo do histórico do lead deixou de ser enum no banco.

### Corrigido

- A ficha nova não gravava quando a origem ficava em branco, e não dizia por quê.
- O painel mostrava número velho por até cinco minutos depois de mover um lead.
- As imagens de produção não construíam: a do frontend não instalava o Vite e
  procurava um nginx.conf inexistente; a do backend rodava como root.
- Cinco testes do formulário de login quebrados por busca ambígua pelo botão.
- O PHPStan não rodava: o caminho da extensão do Larastan estava errado.

## [1.0.0] em 25/07/2026

Primeira versão: leads, funil, tarefas, painel, autenticação com perfis,
trilha de auditoria e upload de anexo.
