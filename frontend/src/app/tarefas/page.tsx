import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { useTarefas } from '../../hooks/useLeads'
import { isDemo, DEMO_TAREFAS } from '../../lib/demoData'
import { PRIORIDADES } from '../../lib/estagios'
import { data, estaAtrasada, primeiroNome } from '../../lib/formato'
import Etiqueta from '../../components/ui/Etiqueta'
import { Carregando, EstadoVazio } from '../../components/ui/Estados'
import type { Tarefa } from '../../types'

type Filtro = 'pendentes' | 'concluidas' | 'todas'

const FILTROS: { chave: Filtro; nome: string }[] = [
  { chave: 'pendentes', nome: 'Em aberto' },
  { chave: 'concluidas', nome: 'Concluídas' },
  { chave: 'todas', nome: 'Todas' },
]

function Linha({ tarefa, aoConcluir }: { tarefa: Tarefa; aoConcluir: (id: number) => void }) {
  const atrasada = estaAtrasada(tarefa.prazo, tarefa.concluida)
  const prioridade = PRIORIDADES[tarefa.prioridade]

  return (
    <li className="flex items-start gap-3 px-4 py-3">
      <input
        type="checkbox"
        checked={tarefa.concluida}
        disabled={tarefa.concluida}
        onChange={() => aoConcluir(tarefa.id)}
        aria-label={`Concluir ${tarefa.titulo}`}
        className="mt-1 h-4 w-4 accent-caneta"
      />

      <div className="flex-1 min-w-0">
        <div className="flex items-start justify-between gap-3">
          <p className={`text-sm ${tarefa.concluida ? 'line-through text-grafite' : ''}`}>
            {tarefa.titulo}
            {tarefa.lead && <span className="text-grafite"> · {tarefa.lead.nome}</span>}
          </p>
          <Etiqueta cor={prioridade.etiqueta}>{prioridade.nome}</Etiqueta>
        </div>

        {tarefa.descricao && (
          <p className="text-xs text-grafite mt-0.5 max-w-leitura">{tarefa.descricao}</p>
        )}

        <p className="etiqueta mt-1 normal-case tracking-normal">
          <span className={atrasada ? 'text-carimbo' : ''}>
            {atrasada ? `Venceu em ${data(tarefa.prazo)}` : `Prazo ${data(tarefa.prazo)}`}
          </span>
          {tarefa.responsavel && <> · {primeiroNome(tarefa.responsavel.nome)}</>}
        </p>
      </div>
    </li>
  )
}

export default function PaginaTarefas() {
  const queryClient = useQueryClient()
  const [filtro, setFiltro] = useState<Filtro>('pendentes')
  const { data: tarefas, isLoading } = useTarefas()

  const concluir = useMutation({
    mutationFn: async (id: number) => {
      if (isDemo()) {
        const tarefa = DEMO_TAREFAS.find((t) => t.id === id)
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
      queryClient.invalidateQueries({ queryKey: ['painel'] })
    },
  })

  const lista = tarefas ?? []
  const emAberto = lista.filter((t) => !t.concluida)
  const atrasadas = emAberto.filter((t) => estaAtrasada(t.prazo)).length

  const visiveis = lista.filter((t) => {
    if (filtro === 'pendentes') return !t.concluida
    if (filtro === 'concluidas') return t.concluida
    return true
  })

  return (
    <div className="space-y-4">
      <header>
        <h1 className="font-titulo text-xl">Tarefas</h1>
        <p className="text-sm text-grafite">
          {emAberto.length} em aberto
          {atrasadas > 0 && <span className="text-carimbo"> · {atrasadas} fora do prazo</span>}
        </p>
      </header>

      <div className="flex border border-borda w-fit bg-ficha">
        {FILTROS.map((f) => (
          <button
            key={f.chave}
            type="button"
            onClick={() => setFiltro(f.chave)}
            aria-pressed={filtro === f.chave}
            className={`h-8 px-3 text-sm border-r border-borda last:border-r-0 transition-colors ${
              filtro === f.chave ? 'bg-gaveta text-white' : 'hover:bg-aco-fundo'
            }`}
          >
            {f.nome}
          </button>
        ))}
      </div>

      {isLoading ? (
        <Carregando rotulo="Buscando tarefas" />
      ) : visiveis.length === 0 ? (
        <EstadoVazio
          titulo="Nada nesta gaveta"
          descricao="As tarefas nascem dentro da ficha do lead, junto com o prazo e o responsável."
        />
      ) : (
        <ul className="ficha divide-y divide-pauta max-w-[760px]">
          {visiveis.map((tarefa) => (
            <Linha key={tarefa.id} tarefa={tarefa} aoConcluir={(id) => concluir.mutate(id)} />
          ))}
        </ul>
      )}
    </div>
  )
}
