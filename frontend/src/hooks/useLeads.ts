import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../lib/api'
import type {
  Lead,
  CriarLeadPayload,
  FiltrosLead,
  RespostaListagem,
  EstagioLead,
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
    queryFn: async () => {
      const { data } = await api.get<RespostaListagem<Lead>>('/leads', {
        params: filtros,
      })
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
    queryFn: async () => {
      const { data } = await api.get<Record<EstagioLead, Lead[]>>('/pipeline')
      return data
    },
    staleTime: 15_000,
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
