import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import { isDemo, DEMO_LEADS, DEMO_TAREFAS } from '../lib/demoData'
import type {
  Lead,
  CriarLeadPayload,
  FiltrosLead,
  RespostaListagem,
  EstagioLead,
  Tarefa,
} from '../types'

export const CHAVES_LEAD = {
  todos: ['leads'] as const,
  lista: (filtros: FiltrosLead) => ['leads', 'lista', filtros] as const,
  detalhe: (id: number) => ['leads', 'detalhe', id] as const,
  historico: (id: number) => ['leads', 'historico', id] as const,
  pipeline: ['leads', 'pipeline'] as const,
}

export function useLeads(filtros: FiltrosLead = {}) {
  return useQuery({
    queryKey: CHAVES_LEAD.lista(filtros),
    queryFn: async (): Promise<RespostaListagem<Lead>> => {
      if (isDemo()) {
        const busca = filtros.busca?.toLowerCase() ?? ''
        const leads = DEMO_LEADS.filter((l) => {
          const matchBusca = !busca || l.nome.toLowerCase().includes(busca) || (l.empresa ?? '').toLowerCase().includes(busca)
          const matchEstagio = !filtros.estagio || l.estagio === filtros.estagio
          return matchBusca && matchEstagio
        })
        return { data: leads, meta: { current_page: 1, from: 1, last_page: 1, per_page: 50, to: leads.length, total: leads.length }, links: { first: '', last: '' } }
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
        const pipeline: Record<EstagioLead, Lead[]> = { novo: [], contato: [], proposta: [], ganho: [], perdido: [] }
        DEMO_LEADS.forEach((l) => pipeline[l.estagio].push(l))
        return pipeline
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
    mutationFn: async (payload: CriarLeadPayload) => {
      const { data } = await api.post<Lead>('/leads', payload)
      return data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos })
    },
  })
}

export function useAtualizarLead(id: number) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (payload: Partial<CriarLeadPayload>) => {
      const { data } = await api.put<Lead>(`/leads/${id}`, payload)
      return data
    },
    onSuccess: (leadAtualizado) => {
      queryClient.setQueryData(CHAVES_LEAD.detalhe(id), leadAtualizado)
      queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos })
    },
  })
}

export function useMoverLead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async ({
      id,
      estagio,
    }: {
      id: number
      estagio: EstagioLead
    }) => {
      const { data } = await api.patch<Lead>(`/leads/${id}/estagio`, { estagio })
      return data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos })
    },
  })
}

export function useExcluirLead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: async (id: number) => {
      await api.delete(`/leads/${id}`)
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: CHAVES_LEAD.todos })
    },
  })
}
