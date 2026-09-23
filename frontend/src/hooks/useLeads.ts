import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import { isDemo, DEMO_LEADS, DEMO_TAREFAS } from '../lib/demoData'
import { guia } from '../lib/estagios'
import type {
  CriarLeadPayload,
  EstagioLead,
  FiltrosLead,
  Lead,
  RespostaListagem,
  Tarefa,
} from '../types'

export const CHAVES_LEAD = {
  todos: ['leads'] as const,
  lista: (filtros: FiltrosLead) => ['leads', 'lista', filtros] as const,
  detalhe: (id: number) => ['leads', 'detalhe', id] as const,
  historico: (id: number) => ['leads', 'historico', id] as const,
  pipeline: ['leads', 'pipeline'] as const,
}

const ESTAGIOS_VAZIOS = (): Record<EstagioLead, Lead[]> => ({
  novo: [],
  contato: [],
  proposta: [],
  ganho: [],
  perdido: [],
})

export function useLeads(filtros: FiltrosLead = {}) {
  return useQuery({
    queryKey: CHAVES_LEAD.lista(filtros),
    queryFn: async (): Promise<RespostaListagem<Lead>> => {
      if (isDemo()) {
        const busca = filtros.busca?.toLowerCase().trim() ?? ''
        const leads = DEMO_LEADS.filter((lead) => {
          const alvo = `${lead.nome} ${lead.empresa ?? ''} ${lead.email ?? ''}`.toLowerCase()
          return (
            (!busca || alvo.includes(busca)) &&
            (!filtros.estagio || lead.estagio === filtros.estagio)
          )
        })

        return {
          data: leads,
          meta: {
            current_page: 1,
            from: 1,
            last_page: 1,
            per_page: leads.length,
            to: leads.length,
            total: leads.length,
          },
          links: { first: '', last: '' },
        }
      }

      const { data } = await api.get<RespostaListagem<Lead>>('/leads', { params: filtros })
      return data
    },
    staleTime: 30_000,
  })
}

export function useLead(id: number) {
  return useQuery({
    queryKey: CHAVES_LEAD.detalhe(id),
    queryFn: async () => {
      const { data } = await api.get<Lead>(`/leads/${id}`)
      return data
    },
    enabled: id > 0,
  })
}

export function usePipeline() {
  return useQuery({
    queryKey: CHAVES_LEAD.pipeline,
    queryFn: async (): Promise<Record<EstagioLead, Lead[]>> => {
      if (isDemo()) {
        const funil = ESTAGIOS_VAZIOS()
        DEMO_LEADS.forEach((lead) => funil[lead.estagio].push(lead))
        return funil
      }
      const { data } = await api.get<Record<EstagioLead, Lead[]>>('/pipeline')
      return data
    },
    staleTime: 15_000,
  })
}

export function useTarefas() {
  return useQuery({
    queryKey: ['tarefas'],
    queryFn: async (): Promise<Tarefa[]> => {
      if (isDemo()) return DEMO_TAREFAS
      const { data } = await api.get<Tarefa[]>('/tarefas')
      return data
    },
    staleTime: 30_000,
  })
}

export function useCriarLead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (payload: CriarLeadPayload): Promise<Lead> => {
      if (isDemo()) {
        const lead: Lead = {
          ...payload,
          id: Math.max(0, ...DEMO_LEADS.map((l) => l.id)) + 1,
          estagio: 'novo',
          tags: [],
          esta_fechado: false,
          tarefas_total: 0,
          tarefas_pendentes: 0,
          criado_em: new Date().toISOString(),
          atualizado_em: new Date().toISOString(),
        }
        DEMO_LEADS.unshift(lead)
        return lead
      }

      const { data } = await api.post<Lead>('/leads', payload)
      return data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos }),
  })
}

export function useAtualizarLead(id: number) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (payload: Partial<CriarLeadPayload>) => {
      const { data } = await api.put<Lead>(`/leads/${id}`, payload)
      return data
    },
    onSuccess: (lead) => {
      queryClient.setQueryData(CHAVES_LEAD.detalhe(id), lead)
      queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos })
    },
  })
}

export function useMoverLead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({ id, estagio }: { id: number; estagio: EstagioLead }) => {
      if (isDemo()) {
        const lead = DEMO_LEADS.find((l) => l.id === id)
        if (lead) {
          lead.estagio = estagio
          lead.esta_fechado = guia(estagio).fechado
          lead.atualizado_em = new Date().toISOString()
        }
        return lead as Lead
      }

      const { data } = await api.patch<Lead>(`/leads/${id}/estagio`, { estagio })
      return data
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos }),
  })
}

export function useExcluirLead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id: number) => {
      if (isDemo()) {
        const posicao = DEMO_LEADS.findIndex((l) => l.id === id)
        if (posicao >= 0) DEMO_LEADS.splice(posicao, 1)
        return
      }
      await api.delete(`/leads/${id}`)
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos }),
  })
}
