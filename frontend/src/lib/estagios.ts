import type { EstagioLead, OrigemLead, PrioridadeTarefa } from '../types'

export interface Guia {
  chave: EstagioLead
  nome: string
  fechado: boolean
  /* Classe da aba de cartolina que encima a coluna e a etiqueta do lead. */
  aba: string
  etiqueta: string
}

export const GUIAS: Guia[] = [
  {
    chave: 'novo',
    nome: 'Novo',
    fechado: false,
    aba: 'bg-gaveta-clara',
    etiqueta: 'bg-aco-fundo text-tinta-suave border-borda',
  },
  {
    chave: 'contato',
    nome: 'Contato',
    fechado: false,
    aba: 'bg-mostarda',
    etiqueta: 'bg-mostarda-clara text-mostarda border-mostarda/30',
  },
  {
    chave: 'proposta',
    nome: 'Proposta',
    fechado: false,
    aba: 'bg-caneta',
    etiqueta: 'bg-caneta-clara text-caneta border-caneta/30',
  },
  {
    chave: 'ganho',
    nome: 'Ganho',
    fechado: true,
    aba: 'bg-aprovado',
    etiqueta: 'bg-aprovado-clara text-aprovado border-aprovado/30',
  },
  {
    chave: 'perdido',
    nome: 'Perdido',
    fechado: true,
    aba: 'bg-carimbo',
    etiqueta: 'bg-carimbo-clara text-carimbo border-carimbo/30',
  },
]

export const guia = (chave: EstagioLead): Guia =>
  GUIAS.find((g) => g.chave === chave) ?? GUIAS[0]

export const ORIGENS: Record<OrigemLead, string> = {
  site: 'Site',
  indicacao: 'Indicação',
  linkedin: 'LinkedIn',
  evento: 'Evento',
  outros: 'Outros',
}

export const PRIORIDADES: Record<PrioridadeTarefa, { nome: string; etiqueta: string }> = {
  baixa: { nome: 'Baixa', etiqueta: 'bg-aco-fundo text-tinta-suave border-borda' },
  media: { nome: 'Média', etiqueta: 'bg-mostarda-clara text-mostarda border-mostarda/30' },
  alta: { nome: 'Alta', etiqueta: 'bg-carimbo-clara text-carimbo border-carimbo/30' },
}
