import { useMoverLead } from '../../hooks/useLeads'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'
import type { Lead, EstagioLead } from '../../types'
import { useState } from 'react'
import { DollarSign, User } from 'lucide-react'

const ESTAGIOS: { key: EstagioLead; label: string; cor: string; fundo: string }[] = [
  { key: 'novo', label: 'Novo', cor: 'border-blue-400', fundo: 'bg-blue-50' },
  { key: 'contato', label: 'Contato', cor: 'border-yellow-400', fundo: 'bg-yellow-50' },
  { key: 'proposta', label: 'Proposta', cor: 'border-purple-400', fundo: 'bg-purple-50' },
  { key: 'ganho', label: 'Ganho', cor: 'border-green-400', fundo: 'bg-green-50' },
  { key: 'perdido', label: 'Perdido', cor: 'border-red-400', fundo: 'bg-red-50' },
]

function CardKanban({
  lead,
  onDragStart,
}: {
  lead: Lead
  onDragStart: (lead: Lead) => void
}) {
  const valorFormatado = lead.valor_estimado
    ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', notation: 'compact' }).format(lead.valor_estimado)
    : null

  return (
    <div
      draggable
      onDragStart={() => onDragStart(lead)}
      className="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
    >
      <h4 className="font-medium text-gray-900 text-sm truncate">{lead.nome}</h4>
      {lead.empresa && (
        <p className="text-xs text-gray-500 truncate mt-0.5">{lead.empresa}</p>
      )}

      <div className="flex items-center justify-between mt-2">
        {valorFormatado ? (
          <div className="flex items-center gap-1 text-xs text-gray-600">
            <DollarSign size={11} />
            {valorFormatado}
          </div>
        ) : <span />}

        {lead.responsavel && (
          <div className="flex items-center gap-1 text-xs text-gray-400">
            <User size={11} />
            {lead.responsavel.nome.split(' ')[0]}
          </div>
        )}
      </div>

      {lead.tarefas_pendentes ? (
        <div className="mt-2 text-xs text-orange-600 font-medium">
          {lead.tarefas_pendentes} tarefa{lead.tarefas_pendentes > 1 ? 's' : ''} pendente{lead.tarefas_pendentes > 1 ? 's' : ''}
        </div>
      ) : null}
    </div>
  )
}

function Coluna({
  estagio,
  leads,
  onDragStart,
  onDrop,
  onDragOver,
}: {
  estagio: typeof ESTAGIOS[0]
  leads: Lead[]
  onDragStart: (lead: Lead) => void
  onDrop: (estagio: EstagioLead) => void
  onDragOver: (e: React.DragEvent) => void
}) {
  const [isDragOver, setIsDragOver] = useState(false)

  const total = leads.reduce((acc, l) => acc + (l.valor_estimado ?? 0), 0)
  const totalFormatado = total > 0
    ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', notation: 'compact' }).format(total)
    : null

  return (
    <div
      className={`flex flex-col rounded-xl border-t-4 ${estagio.cor} ${estagio.fundo} min-w-[240px] w-64 shrink-0`}
      onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); onDragOver(e) }}
      onDragLeave={() => setIsDragOver(false)}
      onDrop={() => { setIsDragOver(false); onDrop(estagio.key) }}
    >
      {/* Header da coluna */}
      <div className="px-3 py-2.5 flex items-center justify-between">
        <div>
          <h3 className="font-semibold text-gray-800 text-sm">{estagio.label}</h3>
          {totalFormatado && <p className="text-xs text-gray-500">{totalFormatado}</p>}
        </div>
        <span className="bg-white/80 text-gray-700 font-medium rounded-full px-2 py-0.5 text-xs">
          {leads.length}
        </span>
      </div>

      {/* Cards */}
      <div className={`flex-1 px-3 pb-3 space-y-2 min-h-[200px] transition-colors rounded-b-xl ${isDragOver ? 'bg-white/50' : ''}`}>
        {leads.map((lead) => (
          <CardKanban key={lead.id} lead={lead} onDragStart={onDragStart} />
        ))}
        {!leads.length && (
          <div className="flex items-center justify-center h-24 text-xs text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
            Arraste um lead aqui
          </div>
        )}
      </div>
    </div>
  )
}

export default function PipelinePage() {
  const queryClient = useQueryClient()
  const moverLead = useMoverLead()
  const [leadArrastando, setLeadArrastando] = useState<Lead | null>(null)

  const { data: pipeline, isLoading } = useQuery({
    queryKey: ['pipeline'],
    queryFn: async () => {
      // Busca todos os leads e agrupa por estágio
      const { data } = await api.get<{ data: Lead[] }>('/leads', {
        params: { por_pagina: 200 }
      })
      const agrupado: Record<EstagioLead, Lead[]> = {
        novo: [], contato: [], proposta: [], ganho: [], perdido: []
      }
      data.data.forEach((lead) => {
        agrupado[lead.estagio].push(lead)
      })
      return agrupado
    },
    staleTime: 15_000,
  })

  const handleDrop = async (novoEstagio: EstagioLead) => {
    if (!leadArrastando || leadArrastando.estagio === novoEstagio) return
    if (leadArrastando.esta_fechado) {
      alert('Este lead já está fechado e não pode ser movido.')
      return
    }

    try {
      await moverLead.mutateAsync({ id: leadArrastando.id, estagio: novoEstagio })
      queryClient.invalidateQueries({ queryKey: ['pipeline'] })
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { mensagem?: string } } })?.response?.data?.mensagem
      alert(msg ?? 'Não foi possível mover o lead.')
    } finally {
      setLeadArrastando(null)
    }
  }

  if (isLoading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600" />
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900">Pipeline</h1>
        <p className="text-gray-500 text-sm mt-1">Arraste os cards para mover leads entre estágios</p>
      </div>

      <div className="overflow-x-auto pb-4">
        <div className="flex gap-4 min-w-max">
          {ESTAGIOS.map((estagio) => (
            <Coluna
              key={estagio.key}
              estagio={estagio}
              leads={pipeline?.[estagio.key] ?? []}
              onDragStart={(lead) => setLeadArrastando(lead)}
              onDrop={handleDrop}
              onDragOver={(e) => e.preventDefault()}
            />
          ))}
        </div>
      </div>
    </div>
  )
}
