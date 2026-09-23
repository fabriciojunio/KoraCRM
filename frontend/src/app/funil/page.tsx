import { useState } from 'react'
import { usePipeline, useMoverLead } from '../../hooks/useLeads'
import { GUIAS } from '../../lib/estagios'
import { moedaCompacta, plural, primeiroNome } from '../../lib/formato'
import { Carregando, Falha } from '../../components/ui/Estados'
import { isDemo } from '../../lib/demoData'
import type { EstagioLead, Lead } from '../../types'

function CartaoLead({
  lead,
  aoArrastar,
  aoMover,
}: {
  lead: Lead
  aoArrastar: (lead: Lead) => void
  aoMover: (lead: Lead, destino: EstagioLead) => void
}) {
  return (
    <article
      draggable={!lead.esta_fechado}
      onDragStart={() => aoArrastar(lead)}
      className="bg-ficha border border-borda p-2.5 cursor-grab active:cursor-grabbing"
    >
      <h4 className="text-sm font-medium leading-tight">{lead.nome}</h4>
      {lead.empresa && <p className="text-xs text-grafite truncate">{lead.empresa}</p>}

      <div className="flex items-center justify-between mt-2 text-xs">
        <span className="numero text-tinta-suave">
          {lead.valor_estimado ? moedaCompacta(lead.valor_estimado) : '—'}
        </span>
        {lead.responsavel && (
          <span className="text-grafite">{primeiroNome(lead.responsavel.nome)}</span>
        )}
      </div>

      {lead.tarefas_pendentes ? (
        <p className="mt-1.5 text-xs text-mostarda">
          {plural(lead.tarefas_pendentes, 'tarefa em aberto', 'tarefas em aberto')}
        </p>
      ) : null}

      {!lead.esta_fechado && (
        <label className="mt-2 block">
          <span className="sr-only">Mover {lead.nome} para outro estágio</span>
          <select
            value={lead.estagio}
            onChange={(e) => aoMover(lead, e.target.value as EstagioLead)}
            className="w-full h-7 bg-aco border border-borda px-1.5 text-xs"
          >
            {GUIAS.map((g) => (
              <option key={g.chave} value={g.chave}>
                {g.nome}
              </option>
            ))}
          </select>
        </label>
      )}
    </article>
  )
}

export default function PaginaFunil() {
  const { data: funil, isLoading } = usePipeline()
  const moverLead = useMoverLead()
  const [arrastando, setArrastando] = useState<Lead | null>(null)
  const [alvo, setAlvo] = useState<EstagioLead | null>(null)
  const [erro, setErro] = useState<string | null>(null)
  const demo = isDemo()

  const mover = async (lead: Lead, destino: EstagioLead) => {
    if (lead.estagio === destino) return
    if (lead.esta_fechado) {
      setErro('Lead fechado não volta para o funil. Abra uma ficha nova.')
      return
    }

    setErro(null)
    try {
      await moverLead.mutateAsync({ id: lead.id, estagio: destino })
    } catch (falha: unknown) {
      const resposta = (falha as { response?: { data?: { mensagem?: string } } })?.response?.data
      setErro(resposta?.mensagem ?? 'Não foi possível mover o lead.')
    }
  }

  if (isLoading || !funil) return <Carregando rotulo="Montando o funil" />

  return (
    <div className="space-y-4">
      <header>
        <h1 className="font-titulo text-xl">Funil</h1>
        <p className="text-sm text-grafite">
          Arraste a ficha para a guia seguinte, ou use o seletor dentro dela.
          {demo && ' Na demonstração o movimento vale só nesta sessão.'}
        </p>
      </header>

      {erro && <Falha mensagem={erro} />}

      <div className="flex gap-2 overflow-x-auto pb-2 items-start">
        {GUIAS.map((g) => {
          const leads = funil[g.chave] ?? []
          const total = leads.reduce((soma, l) => soma + (l.valor_estimado ?? 0), 0)

          return (
            <section
              key={g.chave}
              onDragOver={(e) => {
                e.preventDefault()
                setAlvo(g.chave)
              }}
              onDragLeave={() => setAlvo(null)}
              onDrop={() => {
                setAlvo(null)
                if (arrastando) mover(arrastando, g.chave)
                setArrastando(null)
              }}
              className={`w-[216px] shrink-0 border ${
                alvo === g.chave ? 'border-caneta bg-caneta-clara' : 'border-borda bg-aco-fundo'
              }`}
            >
              <div className={`h-[3px] ${g.aba}`} />
              <header className="px-3 py-2 flex items-baseline justify-between border-b border-borda">
                <h2 className="font-titulo text-sm">{g.nome}</h2>
                <span className="numero text-xs text-grafite">{leads.length}</span>
              </header>
              <p className="px-3 py-1.5 numero text-xs text-tinta-suave border-b border-borda">
                {moedaCompacta(total)}
              </p>

              <div className="p-2 space-y-2 min-h-[160px]">
                {leads.map((lead) => (
                  <CartaoLead
                    key={lead.id}
                    lead={lead}
                    aoArrastar={setArrastando}
                    aoMover={mover}
                  />
                ))}
                {leads.length === 0 && (
                  <p className="text-xs text-grafite px-1 py-6 text-center">Guia vazia</p>
                )}
              </div>
            </section>
          )
        })}
      </div>
    </div>
  )
}
