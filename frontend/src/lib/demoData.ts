import type { Lead, MetricasDashboard, Tarefa, Usuario } from '../types'

export const TOKEN_DEMO = 'demo-token'

export function isDemo(): boolean {
  return localStorage.getItem('koracrm_token') === TOKEN_DEMO
}

/* As datas são relativas ao dia de hoje: a demonstração não pode parecer
   um arquivo morto de 2024 para quem abre o link meses depois. */
const dias = (quantidade: number): string => {
  const d = new Date()
  d.setDate(d.getDate() + quantidade)
  return d.toISOString()
}

const data = (quantidade: number): string => dias(quantidade).slice(0, 10)

export const DEMO_USUARIO: Usuario = {
  id: 0,
  nome: 'Visitante da demonstração',
  email: 'demonstracao@koracrm.com.br',
  perfil: 'gerente',
  ativo: true,
}

export const DEMO_EQUIPE: Usuario[] = [
  DEMO_USUARIO,
  { id: 1, nome: 'Helena Prado', email: 'helena@koracrm.com.br', perfil: 'admin', ativo: true, ultimo_acesso: dias(-1) },
  { id: 2, nome: 'Rogério Tavares', email: 'rogerio@koracrm.com.br', perfil: 'gerente', ativo: true, ultimo_acesso: dias(-3) },
  { id: 3, nome: 'Carlos Menezes', email: 'carlos@koracrm.com.br', perfil: 'vendedor', ativo: true, ultimo_acesso: dias(0) },
  { id: 4, nome: 'Ana Ribeiro', email: 'ana@koracrm.com.br', perfil: 'vendedor', ativo: true, ultimo_acesso: dias(-2) },
  { id: 5, nome: 'Vitor Salles', email: 'vitor@koracrm.com.br', perfil: 'vendedor', ativo: false, ultimo_acesso: dias(-64) },
]

const responsavel = (id: number) => {
  const pessoa = DEMO_EQUIPE.find((u) => u.id === id) ?? DEMO_USUARIO
  return { id: pessoa.id, nome: pessoa.nome, email: pessoa.email }
}

export const DEMO_LEADS: Lead[] = [
  { id: 1, nome: 'Marina Aguiar', email: 'contato@techsolutions.com.br', telefone: '(14) 3456-7890', empresa: 'Tech Solutions Ltda', cargo: 'Diretora de TI', estagio: 'proposta', valor_estimado: 18500, origem: 'linkedin', observacoes: 'Quer o módulo de automação junto.', tags: ['tecnologia'], esta_fechado: false, responsavel: responsavel(3), tarefas_total: 3, tarefas_pendentes: 1, criado_em: dias(-13), atualizado_em: dias(-1) },
  { id: 2, nome: 'Marcos Bianchi', email: 'marcos@gruponexus.com.br', telefone: '(14) 99876-5432', empresa: 'Grupo Nexus', cargo: 'Diretor-geral', estagio: 'proposta', valor_estimado: 32000, origem: 'indicacao', observacoes: 'Indicação da Alfa Sistemas.', tags: ['grande porte'], esta_fechado: false, responsavel: responsavel(4), tarefas_total: 5, tarefas_pendentes: 2, criado_em: dias(-18), atualizado_em: dias(-1) },
  { id: 3, nome: 'Ana Lima', email: 'ana.lima@inovacorp.com.br', telefone: '(14) 3210-0000', empresa: 'Inovacorp', cargo: 'Gerente de inovação', estagio: 'contato', valor_estimado: 12000, origem: 'site', tags: ['tecnologia'], esta_fechado: false, responsavel: responsavel(3), tarefas_total: 2, tarefas_pendentes: 2, criado_em: dias(-11), atualizado_em: dias(-4) },
  { id: 4, nome: 'Pedro Santos', email: 'pedro.santos@databridge.com.br', telefone: '(14) 2233-4455', empresa: 'DataBridge', cargo: 'Gerente comercial', estagio: 'contato', valor_estimado: 9800, origem: 'evento', tags: ['dados'], esta_fechado: false, responsavel: responsavel(4), tarefas_total: 1, tarefas_pendentes: 0, criado_em: dias(-15), atualizado_em: dias(-4) },
  { id: 5, nome: 'Juliana Prates', email: 'financeiro@alfasistemas.com.br', telefone: '(14) 4567-8901', empresa: 'Alfa Sistemas', cargo: 'Diretor financeiro', estagio: 'ganho', valor_estimado: 25000, origem: 'linkedin', tags: ['recorrente'], esta_fechado: true, responsavel: responsavel(3), tarefas_total: 4, tarefas_pendentes: 0, data_fechamento: data(-5), criado_em: dias(-38), atualizado_em: dias(-5) },
  { id: 6, nome: 'Wagner Dutra', email: 'obras@construcoesbeta.com.br', telefone: '(14) 3344-5566', empresa: 'Construções Beta S.A.', cargo: 'Compras', estagio: 'novo', valor_estimado: 7200, origem: 'site', tags: ['construção'], esta_fechado: false, responsavel: responsavel(4), tarefas_total: 0, tarefas_pendentes: 0, criado_em: dias(-6), atualizado_em: dias(-6) },
  { id: 7, nome: 'Talita Moreno', email: 'ti@logisprime.com.br', telefone: '(14) 9876-1234', empresa: 'LogisPrime', cargo: 'Coordenador de TI', estagio: 'novo', valor_estimado: 14500, origem: 'indicacao', tags: ['logística'], esta_fechado: false, responsavel: responsavel(3), tarefas_total: 1, tarefas_pendentes: 1, criado_em: dias(-7), atualizado_em: dias(-7) },
  { id: 8, nome: 'Renata Coutinho', email: 'parceiros@medtechbr.com.br', telefone: '(14) 2233-0099', empresa: 'MedTech Brasil', cargo: 'Diretora executiva', estagio: 'contato', valor_estimado: 22000, origem: 'evento', tags: ['saúde'], esta_fechado: false, responsavel: responsavel(4), tarefas_total: 2, tarefas_pendentes: 1, criado_em: dias(-14), atualizado_em: dias(-8) },
  { id: 9, nome: 'Sérgio Bastos', email: 'comercial@moveisaurora.com.br', telefone: '(14) 3232-1010', empresa: 'Móveis Aurora', cargo: 'Sócio', estagio: 'perdido', valor_estimado: 6400, origem: 'site', observacoes: 'Fechou com concorrente por preço.', tags: [], esta_fechado: true, responsavel: responsavel(3), tarefas_total: 1, tarefas_pendentes: 0, data_fechamento: data(-9), criado_em: dias(-31), atualizado_em: dias(-9) },
]

const soma = (estagio: string, campo: 'contagem' | 'valor') =>
  DEMO_LEADS.filter((l) => l.estagio === estagio).reduce(
    (total, l) => total + (campo === 'contagem' ? 1 : (l.valor_estimado ?? 0)),
    0
  )

export const DEMO_METRICAS: MetricasDashboard = {
  total_leads: DEMO_LEADS.length,
  leads_ativos: DEMO_LEADS.filter((l) => !l.esta_fechado).length,
  leads_ganhos: soma('ganho', 'contagem'),
  leads_perdidos: soma('perdido', 'contagem'),
  taxa_conversao: 11.1,
  valor_total_pipeline: DEMO_LEADS.filter((l) => !l.esta_fechado).reduce(
    (total, l) => total + (l.valor_estimado ?? 0),
    0
  ),
  valor_ganho: soma('ganho', 'valor'),
  contagem_por_estagio: {
    novo: soma('novo', 'contagem'),
    contato: soma('contato', 'contagem'),
    proposta: soma('proposta', 'contagem'),
    ganho: soma('ganho', 'contagem'),
    perdido: soma('perdido', 'contagem'),
  },
  valor_por_estagio: {
    novo: soma('novo', 'valor'),
    contato: soma('contato', 'valor'),
    proposta: soma('proposta', 'valor'),
    ganho: soma('ganho', 'valor'),
    perdido: soma('perdido', 'valor'),
  },
  tarefas_pendentes: 7,
  tarefas_atrasadas: 2,
}

export const DEMO_ATIVIDADES = [
  { id: 1, tipo: 'mudanca_estagio', descricao: 'Grupo Nexus avançou para Proposta', lead: 'Grupo Nexus', usuario: 'Ana Ribeiro', data: dias(-1) },
  { id: 2, tipo: 'criacao', descricao: 'Ficha aberta: Construções Beta S.A.', lead: 'Construções Beta', usuario: 'Ana Ribeiro', data: dias(-6) },
  { id: 3, tipo: 'tarefa_criada', descricao: 'Tarefa criada: enviar proposta comercial', lead: 'Tech Solutions', usuario: 'Carlos Menezes', data: dias(-3) },
  { id: 4, tipo: 'comentario', descricao: 'Reunião de alinhamento marcada para sexta', lead: 'DataBridge', usuario: 'Ana Ribeiro', data: dias(-4) },
  { id: 5, tipo: 'mudanca_estagio', descricao: 'Alfa Sistemas marcado como Ganho, contrato assinado', lead: 'Alfa Sistemas', usuario: 'Carlos Menezes', data: dias(-5) },
  { id: 6, tipo: 'mudanca_estagio', descricao: 'Móveis Aurora marcado como Perdido', lead: 'Móveis Aurora', usuario: 'Carlos Menezes', data: dias(-9) },
  { id: 7, tipo: 'criacao', descricao: 'Ficha aberta: LogisPrime', lead: 'LogisPrime', usuario: 'Carlos Menezes', data: dias(-7) },
]

export const DEMO_TAREFAS: Tarefa[] = [
  { id: 1, titulo: 'Enviar proposta comercial', descricao: 'Incluir cronograma de implantação e as duas opções de suporte.', prazo: data(2), concluida: false, prioridade: 'alta', lead_id: 1, lead: { id: 1, nome: 'Tech Solutions Ltda' }, responsavel: responsavel(3), criado_em: dias(-3) },
  { id: 2, titulo: 'Reunião de alinhamento técnico', prazo: data(1), concluida: false, prioridade: 'alta', lead_id: 2, lead: { id: 2, nome: 'Grupo Nexus' }, responsavel: responsavel(4), criado_em: dias(-5) },
  { id: 3, titulo: 'Retomar contato depois da demonstração', prazo: data(-2), concluida: false, prioridade: 'media', lead_id: 3, lead: { id: 3, nome: 'Inovacorp' }, responsavel: responsavel(3), criado_em: dias(-10) },
  { id: 4, titulo: 'Enviar contrato para assinatura', prazo: data(-1), concluida: false, prioridade: 'alta', lead_id: 7, lead: { id: 7, nome: 'LogisPrime' }, responsavel: responsavel(3), criado_em: dias(-8) },
  { id: 5, titulo: 'Ligar para confirmar interesse', prazo: data(3), concluida: false, prioridade: 'media', lead_id: 8, lead: { id: 8, nome: 'MedTech Brasil' }, responsavel: responsavel(4), criado_em: dias(-6) },
  { id: 6, titulo: 'Apresentação de implantação', prazo: data(-6), concluida: true, concluida_em: dias(-6), prioridade: 'alta', lead_id: 5, lead: { id: 5, nome: 'Alfa Sistemas' }, responsavel: responsavel(3), criado_em: dias(-20) },
  { id: 7, titulo: 'Enviar material de apoio', prazo: data(5), concluida: false, prioridade: 'baixa', lead_id: 4, lead: { id: 4, nome: 'DataBridge' }, responsavel: responsavel(4), criado_em: dias(-4) },
  { id: 8, titulo: 'Revisar valores com o financeiro', prazo: data(4), concluida: false, prioridade: 'baixa', lead_id: 2, lead: { id: 2, nome: 'Grupo Nexus' }, responsavel: responsavel(4), criado_em: dias(-2) },
]
