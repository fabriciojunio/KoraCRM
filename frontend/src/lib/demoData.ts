import type { MetricasDashboard, Lead, Tarefa } from '../types'

export const DEMO_METRICAS: MetricasDashboard = {
  total_leads: 47,
  leads_ativos: 38,
  leads_ganhos: 12,
  leads_perdidos: 5,
  taxa_conversao: 24.5,
  valor_total_pipeline: 186500,
  valor_ganho: 54200,
  contagem_por_estagio: {
    novo: 14,
    contato: 11,
    proposta: 8,
    ganho: 12,
    perdido: 5,
  },
  valor_por_estagio: {
    novo: 28000,
    contato: 42500,
    proposta: 61200,
    ganho: 54200,
    perdido: 600,
  },
  tarefas_pendentes: 9,
  tarefas_atrasadas: 2,
}

export const DEMO_ATIVIDADES = [
  { id: 1, tipo: 'criacao', descricao: 'Lead criado: Tech Solutions Ltda', lead: 'Tech Solutions Ltda', usuario: 'Usuário Demo', data: '2024-05-20T10:30:00Z' },
  { id: 2, tipo: 'mudanca_estagio', descricao: 'Lead avançou para Proposta', lead: 'Grupo Nexus', usuario: 'Usuário Demo', data: '2024-05-20T09:15:00Z' },
  { id: 3, tipo: 'tarefa_criada', descricao: 'Tarefa criada: Enviar proposta comercial', lead: 'Inovacorp', usuario: 'Usuário Demo', data: '2024-05-19T16:45:00Z' },
  { id: 4, tipo: 'comentario', descricao: 'Reunião de alinhamento agendada para sexta-feira', lead: 'DataBridge', usuario: 'Usuário Demo', data: '2024-05-19T14:20:00Z' },
  { id: 5, tipo: 'mudanca_estagio', descricao: 'Lead marcado como Ganho — contrato assinado', lead: 'Alfa Sistemas', usuario: 'Usuário Demo', data: '2024-05-18T11:00:00Z' },
  { id: 6, tipo: 'criacao', descricao: 'Lead criado: Construções Beta S.A.', lead: 'Construções Beta S.A.', usuario: 'Usuário Demo', data: '2024-05-17T08:30:00Z' },
]

export const DEMO_LEADS: Lead[] = [
  { id: 1, nome: 'Tech Solutions Ltda', email: 'contato@techsolutions.com.br', telefone: '(11) 3456-7890', empresa: 'Tech Solutions Ltda', cargo: 'Diretora de TI', estagio: 'proposta', valor_estimado: 18500, origem: 'linkedin', observacoes: 'Interesse em módulo de automação', tags: ['enterprise', 'tech'], esta_fechado: false, tarefas_total: 3, tarefas_pendentes: 1, criado_em: '2024-05-10T09:00:00Z', atualizado_em: '2024-05-20T10:30:00Z' },
  { id: 2, nome: 'Grupo Nexus', email: 'marcos@gruponexus.com', telefone: '(21) 99876-5432', empresa: 'Grupo Nexus', cargo: 'CEO', estagio: 'proposta', valor_estimado: 32000, origem: 'indicacao', observacoes: 'Referência do cliente Alfa Sistemas', tags: ['vip', 'grande-porte'], esta_fechado: false, tarefas_total: 5, tarefas_pendentes: 2, criado_em: '2024-05-05T14:00:00Z', atualizado_em: '2024-05-20T09:15:00Z' },
  { id: 3, nome: 'Inovacorp', email: 'ana.lima@inovacorp.io', telefone: '(31) 3210-0000', empresa: 'Inovacorp', cargo: 'Head de Inovação', estagio: 'contato', valor_estimado: 12000, origem: 'site', tags: ['startup', 'tech'], esta_fechado: false, tarefas_total: 2, tarefas_pendentes: 2, criado_em: '2024-05-12T11:30:00Z', atualizado_em: '2024-05-19T16:45:00Z' },
  { id: 4, nome: 'DataBridge', email: 'pedro.santos@databridge.com.br', telefone: '(41) 2233-4455', empresa: 'DataBridge', cargo: 'Gerente Comercial', estagio: 'contato', valor_estimado: 9800, origem: 'evento', tags: ['dados', 'BI'], esta_fechado: false, tarefas_total: 1, tarefas_pendentes: 0, criado_em: '2024-05-08T16:00:00Z', atualizado_em: '2024-05-19T14:20:00Z' },
  { id: 5, nome: 'Alfa Sistemas', email: 'financeiro@alfasistemasas.com.br', telefone: '(11) 4567-8901', empresa: 'Alfa Sistemas', cargo: 'CFO', estagio: 'ganho', valor_estimado: 25000, origem: 'linkedin', tags: ['enterprise', 'recorrente'], esta_fechado: true, tarefas_total: 4, tarefas_pendentes: 0, criado_em: '2024-04-15T10:00:00Z', atualizado_em: '2024-05-18T11:00:00Z' },
  { id: 6, nome: 'Construções Beta S.A.', email: 'obras@construcoesbeta.com', telefone: '(61) 3344-5566', empresa: 'Construções Beta S.A.', cargo: 'Responsável de Compras', estagio: 'novo', valor_estimado: 7200, origem: 'site', tags: ['construção'], esta_fechado: false, tarefas_total: 0, tarefas_pendentes: 0, criado_em: '2024-05-17T08:30:00Z', atualizado_em: '2024-05-17T08:30:00Z' },
  { id: 7, nome: 'LogisPrime', email: 'ti@logisprime.com.br', telefone: '(19) 9876-1234', empresa: 'LogisPrime', cargo: 'CTO', estagio: 'novo', valor_estimado: 14500, origem: 'indicacao', tags: ['logística', 'saas'], esta_fechado: false, tarefas_total: 1, tarefas_pendentes: 1, criado_em: '2024-05-16T13:00:00Z', atualizado_em: '2024-05-16T13:00:00Z' },
  { id: 8, nome: 'MedTech Brasil', email: 'parceiros@medtechbr.com', telefone: '(11) 2233-0099', empresa: 'MedTech Brasil', cargo: 'Diretora Executiva', estagio: 'contato', valor_estimado: 22000, origem: 'evento', tags: ['saúde', 'tech'], esta_fechado: false, tarefas_total: 2, tarefas_pendentes: 1, criado_em: '2024-05-09T09:45:00Z', atualizado_em: '2024-05-15T14:00:00Z' },
]

export const DEMO_TAREFAS: Tarefa[] = [
  { id: 1, titulo: 'Enviar proposta comercial', descricao: 'Preparar proposta detalhada com cronograma de implantação', prazo: '2024-05-24', concluida: false, prioridade: 'alta', lead_id: 1, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-19T16:45:00Z' },
  { id: 2, titulo: 'Reunião de alinhamento técnico', prazo: '2024-05-22', concluida: false, prioridade: 'alta', lead_id: 2, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-15T11:00:00Z' },
  { id: 3, titulo: 'Follow-up após demo', prazo: '2024-05-21', concluida: false, prioridade: 'media', lead_id: 3, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-12T09:00:00Z' },
  { id: 4, titulo: 'Enviar contrato para assinatura', prazo: '2024-05-19', concluida: false, prioridade: 'alta', lead_id: 7, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-13T14:30:00Z' },
  { id: 5, titulo: 'Ligar para confirmar interesse', prazo: '2024-05-23', concluida: false, prioridade: 'media', lead_id: 8, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-14T10:00:00Z' },
  { id: 6, titulo: 'Apresentação de onboarding', prazo: '2024-05-20', concluida: true, concluida_em: '2024-05-20T15:00:00Z', prioridade: 'alta', lead_id: 5, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-01T09:00:00Z' },
  { id: 7, titulo: 'Enviar materiais de suporte', prazo: '2024-05-25', concluida: false, prioridade: 'baixa', lead_id: 4, responsavel: { id: 0, nome: 'Usuário Demo', email: 'demo@koracrm.com' }, criado_em: '2024-05-16T16:00:00Z' },
]

export function isDemo(): boolean {
  return localStorage.getItem('koracrm_token') === 'demo-token'
}
