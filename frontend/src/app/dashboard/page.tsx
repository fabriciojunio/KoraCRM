import { useQuery } from '@tanstack/react-query'
import { api } from '../../lib/api'
import type { MetricasDashboard } from '../../types'

const estagioLabel: Record<string, string> = {
  novo: 'Novo',
  contato: 'Contato',
  proposta: 'Proposta',
  ganho: 'Ganho',
  perdido: 'Perdido',
}

const estagioColor: Record<string, string> = {
  novo: 'bg-blue-500',
  contato: 'bg-amber-500',
  proposta: 'bg-violet-500',
  ganho: 'bg-emerald-600',
  perdido: 'bg-rose-500',
}

function Stat({
  rotulo,
  valor,
  sub,
}: {
  rotulo: string
  valor: string | number
  sub?: string
}) {
  return (
    <div className="stat">
      <p className="stat-label">{rotulo}</p>
      <p className="stat-value">{valor}</p>
      {sub && <p className="stat-sub">{sub}</p>}
    </div>
  )
}

export default function Dashboard() {
  const { data: metricas, isLoading } = useQuery({
    queryKey: ['dashboard', 'metricas'],
    queryFn: async () => {
      const { data } = await api.get<MetricasDashboard>('/dashboard/metricas')
      return data
    },
  })

  const { data: atividades } = useQuery({
    queryKey: ['dashboard', 'atividades'],
    queryFn: async () => {
      const { data } = await api.get<Array<{
        id: number
        tipo: string
        descricao: string
        lead: string
        usuario: string
        data: string
      }>>('/dashboard/atividades')
      return data
    },
  })

  const moeda = (v: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v)

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-7 w-7 border-2 border-gray-200 border-t-brand-600" />
      </div>
    )
  }

  return (
    <div className="space-y-7">
      <header>
        <h1 className="text-xl font-semibold text-gray-900">Dashboard</h1>
        <p className="text-sm text-gray-500 mt-0.5">
          Visão geral do pipeline de vendas
        </p>
      </header>

      <section className="grid grid-cols-2 lg:grid-cols-3 gap-3">
        <Stat
          rotulo="Total de leads"
          valor={metricas?.total_leads ?? 0}
          sub={`${metricas?.leads_ativos ?? 0} ativos`}
        />
        <Stat
          rotulo="Taxa de conversão"
          valor={`${metricas?.taxa_conversao ?? 0}%`}
          sub={`${metricas?.leads_ganhos ?? 0} ganhos`}
        />
        <Stat
          rotulo="Leads perdidos"
          valor={metricas?.leads_perdidos ?? 0}
        />
        <Stat
          rotulo="Valor no pipeline"
          valor={moeda(metricas?.valor_total_pipeline ?? 0)}
          sub={`Ganho: ${moeda(metricas?.valor_ganho ?? 0)}`}
        />
        <Stat
          rotulo="Tarefas pendentes"
          valor={metricas?.tarefas_pendentes ?? 0}
        />
        <Stat
          rotulo="Tarefas atrasadas"
          valor={metricas?.tarefas_atrasadas ?? 0}
          sub="Precisam de atenção"
        />
      </section>

      <section className="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div className="card">
          <h2 className="text-[13px] font-semibold uppercase tracking-wider text-gray-500 mb-4">
            Leads por estágio
          </h2>
          <div className="space-y-3.5">
            {metricas &&
              Object.entries(metricas.contagem_por_estagio).map(([estagio, qtd]) => {
                const total = metricas.total_leads || 1
                const pct = Math.round((qtd / total) * 100)
                return (
                  <div key={estagio}>
                    <div className="flex justify-between text-sm mb-1.5">
                      <span className="text-gray-600">{estagioLabel[estagio]}</span>
                      <span className="font-medium text-gray-900 tabular-nums">{qtd}</span>
                    </div>
                    <div className="h-1.5 bg-gray-100 rounded-sm overflow-hidden">
                      <div
                        className={`h-full rounded-sm ${estagioColor[estagio]}`}
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                  </div>
                )
              })}
          </div>
        </div>

        <div className="card">
          <h2 className="text-[13px] font-semibold uppercase tracking-wider text-gray-500 mb-4">
            Atividades recentes
          </h2>
          {!atividades?.length ? (
            <p className="text-sm text-gray-400 py-10 text-center">
              Nenhuma atividade ainda.
            </p>
          ) : (
            <ul className="divide-y divide-gray-100">
              {atividades.slice(0, 8).map((a) => (
                <li key={a.id} className="py-2.5 first:pt-0 last:pb-0">
                  <p className="text-sm text-gray-800">{a.descricao}</p>
                  <p className="text-xs text-gray-400 mt-0.5">
                    {a.lead} · {a.usuario} ·{' '}
                    {new Date(a.data).toLocaleDateString('pt-BR')}
                  </p>
                </li>
              ))}
            </ul>
          )}
        </div>
      </section>
    </div>
  )
}
