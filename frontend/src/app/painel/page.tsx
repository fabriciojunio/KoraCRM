import { useQuery } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { isDemo, DEMO_METRICAS, DEMO_ATIVIDADES } from '../../lib/demoData'
import { GUIAS } from '../../lib/estagios'
import { moeda, data, plural } from '../../lib/formato'
import Painel from '../../components/ui/Painel'
import { Carregando } from '../../components/ui/Estados'
import type { MetricasDashboard } from '../../types'

interface Atividade {
  id: number
  tipo: string
  descricao: string
  lead: string
  usuario: string
  data: string
}

function Numero({
  rotulo,
  valor,
  nota,
  alerta = false,
}: {
  rotulo: string
  valor: string | number
  nota?: string
  alerta?: boolean
}) {
  return (
    <div className="px-4 py-4">
      <p className="etiqueta">{rotulo}</p>
      <p
        className={`numero text-[26px] leading-none mt-2 ${alerta ? 'text-carimbo' : 'text-tinta'}`}
      >
        {valor}
      </p>
      {nota && <p className="text-xs text-grafite mt-1.5">{nota}</p>}
    </div>
  )
}

export default function PaginaPainel() {
  const demo = isDemo()

  const { data: metricas, isLoading } = useQuery({
    queryKey: ['painel', 'metricas'],
    queryFn: async () => {
      if (demo) return DEMO_METRICAS
      const { data: resposta } = await api.get<MetricasDashboard>('/dashboard/metricas')
      return resposta
    },
  })

  const { data: atividades } = useQuery({
    queryKey: ['painel', 'atividades'],
    queryFn: async () => {
      if (demo) return DEMO_ATIVIDADES
      const { data: resposta } = await api.get<Atividade[]>('/dashboard/atividades')
      return resposta
    },
  })

  if (isLoading || !metricas) return <Carregando rotulo="Levantando os números" />

  const maiorEstagio = Math.max(...Object.values(metricas.contagem_por_estagio), 1)

  return (
    <div className="space-y-5">
      <header>
        <h1 className="font-titulo text-xl">Painel</h1>
        <p className="text-sm text-grafite">
          Onde o funil está agora, contando leads abertos e fechados.
        </p>
      </header>

      <div className="ficha grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 divide-x divide-y lg:divide-y-0 divide-pauta">
        <Numero
          rotulo="Leads"
          valor={metricas.total_leads}
          nota={`${metricas.leads_ativos} em aberto`}
        />
        <Numero
          rotulo="Conversão"
          valor={`${metricas.taxa_conversao}%`}
          nota={plural(metricas.leads_ganhos, 'ganho', 'ganhos')}
        />
        <Numero rotulo="Perdidos" valor={metricas.leads_perdidos} />
        <Numero
          rotulo="No funil"
          valor={moeda(metricas.valor_total_pipeline)}
          nota={`${moeda(metricas.valor_ganho)} fechados`}
        />
        <Numero
          rotulo="Tarefas"
          valor={metricas.tarefas_pendentes}
          nota={metricas.tarefas_pendentes === 1 ? 'pendente' : 'pendentes'}
        />
        <Numero
          rotulo="Atrasadas"
          valor={metricas.tarefas_atrasadas}
          alerta={metricas.tarefas_atrasadas > 0}
          nota={
            metricas.tarefas_atrasadas === 0
              ? 'nenhuma'
              : metricas.tarefas_atrasadas === 1
                ? 'passou do prazo'
                : 'passaram do prazo'
          }
        />
      </div>

      <div className="grid gap-5 lg:grid-cols-2 items-start">
        <Painel titulo="Leads por estágio">
          <div className="p-4 space-y-3">
            {GUIAS.map(({ chave, nome, aba }) => {
              const quantidade = metricas.contagem_por_estagio[chave] ?? 0
              const valor = metricas.valor_por_estagio[chave] ?? 0
              return (
                <div key={chave}>
                  <div className="flex items-baseline justify-between text-sm">
                    <span>{nome}</span>
                    <span className="numero text-xs text-grafite">
                      {quantidade} · {moeda(valor)}
                    </span>
                  </div>
                  <div className="mt-1.5 h-2 bg-aco-fundo">
                    <div
                      className={`h-full ${aba}`}
                      style={{ width: `${(quantidade / maiorEstagio) * 100}%` }}
                    />
                  </div>
                </div>
              )
            })}
          </div>
        </Painel>

        <Painel titulo="Últimos movimentos">
          {!atividades?.length ? (
            <p className="p-4 text-sm text-grafite">Nada registrado ainda.</p>
          ) : (
            <ul className="divide-y divide-pauta">
              {atividades.slice(0, 7).map((a) => (
                <li key={a.id} className="px-4 py-2.5">
                  <p className="text-sm">{a.descricao}</p>
                  <p className="etiqueta mt-0.5 normal-case tracking-normal">
                    {a.usuario} · {data(a.data)}
                  </p>
                </li>
              ))}
            </ul>
          )}
        </Painel>
      </div>
    </div>
  )
}
