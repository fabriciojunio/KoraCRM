export type PerfilUsuario = 'admin' | 'gerente' | 'vendedor'

export type EstagioLead = 'novo' | 'contato' | 'proposta' | 'ganho' | 'perdido'

export type OrigemLead = 'site' | 'indicacao' | 'linkedin' | 'evento' | 'outros'

export type PrioridadeTarefa = 'baixa' | 'media' | 'alta'

export interface Usuario {
  id: number
  nome: string
  email: string
  perfil: PerfilUsuario
  ativo: boolean
  avatar?: string
  ultimo_acesso?: string
}

export interface Lead {
  id: number
  nome: string
  email?: string
  telefone?: string
  empresa?: string
  cargo?: string
  estagio: EstagioLead
  valor_estimado?: number
  origem?: OrigemLead
  observacoes?: string
  tags: string[]
  esta_fechado: boolean
  responsavel?: Pick<Usuario, 'id' | 'nome' | 'email'>
  criador?: Pick<Usuario, 'id' | 'nome'>
  tarefas_total?: number
  tarefas_pendentes?: number
  data_fechamento?: string
  criado_em: string
  atualizado_em: string
}

export interface Tarefa {
  id: number
  titulo: string
  descricao?: string
  prazo?: string
  concluida: boolean
  concluida_em?: string
  prioridade: PrioridadeTarefa
  lead_id: number
  responsavel: Pick<Usuario, 'id' | 'nome'>
  criado_em: string
}

export interface HistoricoLead {
  id: number
  tipo: 'criacao' | 'atualizacao' | 'mudanca_estagio' | 'comentario' | 'tarefa_criada' | 'arquivo_enviado'
  descricao: string
  dados_anteriores?: Record<string, unknown>
  dados_novos?: Record<string, unknown>
  usuario?: Pick<Usuario, 'id' | 'nome'>
  created_at: string
}

export interface MetricasDashboard {
  total_leads: number
  leads_ativos: number
  leads_ganhos: number
  leads_perdidos: number
  taxa_conversao: number
  valor_total_pipeline: number
  valor_ganho: number
  contagem_por_estagio: Record<EstagioLead, number>
  valor_por_estagio: Record<EstagioLead, number>
  tarefas_pendentes: number
  tarefas_atrasadas: number
}

export interface PaginacaoMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}

export interface RespostaListagem<T> {
  data: T[]
  meta: PaginacaoMeta
  links: {
    first: string
    last: string
    prev?: string
    next?: string
  }
}

export interface ErroAPI {
  message: string
  errors?: Record<string, string[]>
}

export interface TokenPayload {
  token: string
  usuario: Usuario
  expira_em: string
}

export interface FiltrosLead {
  estagio?: EstagioLead
  busca?: string
  responsavel_id?: number
  origem?: OrigemLead
  por_pagina?: number
  pagina?: number
}

export interface CriarLeadPayload {
  nome: string
  email?: string
  telefone?: string
  empresa?: string
  cargo?: string
  valor_estimado?: number
  origem?: OrigemLead
  observacoes?: string
  tags?: string[]
  responsavel_id?: number
}
