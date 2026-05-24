import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { isDemo, DEMO_TAREFAS } from '../../lib/demoData'
import type { Tarefa } from '../../types'
import { CheckCircle2, Circle, AlertTriangle, Clock } from 'lucide-react'
import { useState } from 'react'

const prioridadeConfig = {
  baixa: { label: 'Baixa', cor: 'text-blue-600 bg-blue-50' },
  media: { label: 'Média', cor: 'text-yellow-600 bg-yellow-50' },
  alta: { label: 'Alta', cor: 'text-red-600 bg-red-50' },
}

function CardTarefa({ tarefa, onConcluir }: { tarefa: Tarefa; onConcluir: (id: number) => void }) {
  const prazo = tarefa.prazo ? new Date(tarefa.prazo) : null
  const atrasada = prazo && !tarefa.concluida && prazo < new Date()

  return (
    <div className={`card flex items-start gap-4 transition-opacity ${tarefa.concluida ? 'opacity-60' : ''}`}>
      <button
        onClick={() => !tarefa.concluida && onConcluir(tarefa.id)}
        disabled={tarefa.concluida}
        className={`mt-0.5 shrink-0 transition-colors ${tarefa.concluida ? 'text-green-500' : 'text-gray-300 hover:text-brand-500'}`}
      >
        {tarefa.concluida
          ? <CheckCircle2 size={22} />
          : <Circle size={22} />
        }
      </button>

      <div className="flex-1 min-w-0">
        <div className="flex items-start justify-between gap-2">
          <h3 className={`font-medium text-sm ${tarefa.concluida ? 'line-through text-gray-400' : 'text-gray-900'}`}>
            {tarefa.titulo}
          </h3>
          <span className={`badge shrink-0 text-xs ${prioridadeConfig[tarefa.prioridade].cor}`}>
            {prioridadeConfig[tarefa.prioridade].label}
          </span>
        </div>

        {tarefa.descricao && (
          <p className="text-xs text-gray-500 mt-1 line-clamp-2">{tarefa.descricao}</p>
        )}

        <div className="flex items-center gap-3 mt-2 text-xs">
          {prazo && (
            <div className={`flex items-center gap-1 ${atrasada ? 'text-red-600 font-medium' : 'text-gray-400'}`}>
              {atrasada ? <AlertTriangle size={12} /> : <Clock size={12} />}
              {prazo.toLocaleDateString('pt-BR')}
              {atrasada && ' (atrasada)'}
            </div>
          )}
          {tarefa.responsavel && (
            <span className="text-gray-400">{tarefa.responsavel.nome}</span>
          )}
        </div>
      </div>
    </div>
  )
}

export default function TarefasPage() {
  const queryClient = useQueryClient()
  const [filtro, setFiltro] = useState<'todas' | 'pendentes' | 'concluidas'>('pendentes')

  const { data: tarefas, isLoading } = useQuery({
    queryKey: ['tarefas'],
    queryFn: async (): Promise<Tarefa[]> => {
      if (isDemo()) return DEMO_TAREFAS
      const { data } = await api.get<Tarefa[]>('/tarefas')
      return data
    },
  })

  const concluirTarefa = useMutation({
    mutationFn: async (id: number) => {
      if (isDemo()) {
        const tarefa = DEMO_TAREFAS.find(t => t.id === id)
        if (tarefa) {
          tarefa.concluida = true
          tarefa.concluida_em = new Date().toISOString()
        }
        return
      }
      await api.patch(`/tarefas/${id}/concluir`)
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tarefas'] })
      queryClient.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })

  const tarefasFiltradas = tarefas?.filter((t) => {
    if (filtro === 'pendentes') return !t.concluida
    if (filtro === 'concluidas') return t.concluida
    return true
  }) ?? []

  const atrasadas = tarefas?.filter(
    (t) => !t.concluida && t.prazo && new Date(t.prazo) < new Date()
  ).length ?? 0

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Tarefas</h1>
          <p className="text-gray-500 text-sm mt-1">
            {tarefas?.filter(t => !t.concluida).length ?? 0} pendentes
            {atrasadas > 0 && (
              <span className="ml-2 text-red-600 font-medium">
                · {atrasadas} atrasada{atrasadas > 1 ? 's' : ''}
              </span>
            )}
          </p>
        </div>
      </div>

      {/* Filtros */}
      <div className="flex gap-2">
        {([
          { key: 'pendentes', label: 'Pendentes' },
          { key: 'concluidas', label: 'Concluídas' },
          { key: 'todas', label: 'Todas' },
        ] as const).map((f) => (
          <button
            key={f.key}
            onClick={() => setFiltro(f.key)}
            className={`px-4 py-1.5 rounded-full text-sm font-medium transition-colors
              ${filtro === f.key
                ? 'bg-brand-600 text-white'
                : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
              }`}
          >
            {f.label}
          </button>
        ))}
      </div>

      {/* Lista */}
      {isLoading ? (
        <div className="flex justify-center py-16">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600" />
        </div>
      ) : !tarefasFiltradas.length ? (
        <div className="text-center py-16 text-gray-400">
          <CheckCircle2 size={40} className="mx-auto mb-3 opacity-40" />
          <p className="font-medium">Nenhuma tarefa {filtro === 'pendentes' ? 'pendente' : filtro === 'concluidas' ? 'concluída' : ''}</p>
          <p className="text-sm mt-1">As tarefas são criadas dentro de cada lead</p>
        </div>
      ) : (
        <div className="space-y-3 max-w-3xl">
          {tarefasFiltradas.map((tarefa) => (
            <CardTarefa
              key={tarefa.id}
              tarefa={tarefa}
              onConcluir={(id) => concluirTarefa.mutate(id)}
            />
          ))}
        </div>
      )}
    </div>
  )
}
