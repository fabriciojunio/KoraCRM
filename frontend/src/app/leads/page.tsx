import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useLeads, useCriarLead, useExcluirLead } from '../../hooks/useLeads'
import { GUIAS, guia, ORIGENS } from '../../lib/estagios'
import { moeda, data, plural } from '../../lib/formato'
import Etiqueta from '../../components/ui/Etiqueta'
import { Carregando, EstadoVazio } from '../../components/ui/Estados'
import type { EstagioLead, Lead } from '../../types'

// Campo de formulário vem como string vazia quando não preenchido, e a API
// espera ausência, não vazio.
const opcional = <T extends z.ZodTypeAny>(esquema: T) =>
  z.preprocess((valor) => (valor === '' ? undefined : valor), esquema.optional())

const esquemaLead = z.object({
  nome: z.string().min(1, 'Nome é obrigatório').max(150),
  email: opcional(z.string().email('E-mail inválido')),
  telefone: opcional(z.string().max(20)),
  empresa: opcional(z.string().max(150)),
  cargo: opcional(z.string().max(100)),
  valor_estimado: opcional(z.coerce.number().min(0, 'Valor não pode ser negativo')),
  origem: opcional(z.enum(['site', 'indicacao', 'linkedin', 'evento', 'outros'])),
  observacoes: opcional(z.string().max(5000)),
})

type FormularioLead = z.infer<typeof esquemaLead>

function FichaNova({ aoFechar }: { aoFechar: () => void }) {
  const criarLead = useCriarLead()
  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<FormularioLead>({ resolver: zodResolver(esquemaLead) })

  const enviar = async (dados: FormularioLead) => {
    await criarLead.mutateAsync(dados)
    aoFechar()
  }

  return (
    <div className="fixed inset-0 z-40 bg-gaveta/60 flex items-start justify-center overflow-y-auto p-4 sm:p-8">
      <div
        role="dialog"
        aria-modal="true"
        aria-label="Nova ficha de lead"
        className="w-full max-w-[560px] bg-ficha border border-borda-forte"
      >
        <header className="ficha-titulo border-b border-borda-forte">
          <h2 className="font-titulo text-base">Nova ficha</h2>
          <button type="button" onClick={aoFechar} className="botao-discreto h-7 px-2">
            Fechar
          </button>
        </header>

        <form onSubmit={handleSubmit(enviar)} className="p-5 space-y-4">
          <div>
            <label htmlFor="nome" className="campo-rotulo">
              Nome ou razão social
            </label>
            <input id="nome" className="campo" {...register('nome')} />
            {errors.nome && <p className="campo-erro">{errors.nome.message}</p>}
          </div>

          <div className="grid sm:grid-cols-2 gap-4">
            <div>
              <label htmlFor="email" className="campo-rotulo">
                E-mail
              </label>
              <input id="email" type="email" className="campo" {...register('email')} />
              {errors.email && <p className="campo-erro">{errors.email.message}</p>}
            </div>
            <div>
              <label htmlFor="telefone" className="campo-rotulo">
                Telefone
              </label>
              <input id="telefone" className="campo" {...register('telefone')} />
            </div>
            <div>
              <label htmlFor="empresa" className="campo-rotulo">
                Empresa
              </label>
              <input id="empresa" className="campo" {...register('empresa')} />
            </div>
            <div>
              <label htmlFor="cargo" className="campo-rotulo">
                Cargo do contato
              </label>
              <input id="cargo" className="campo" {...register('cargo')} />
            </div>
            <div>
              <label htmlFor="valor" className="campo-rotulo">
                Valor estimado
              </label>
              <input
                id="valor"
                type="number"
                min="0"
                step="0.01"
                className="campo numero"
                {...register('valor_estimado')}
              />
            </div>
            <div>
              <label htmlFor="origem" className="campo-rotulo">
                Origem
              </label>
              <select id="origem" className="campo" {...register('origem')}>
                <option value="">Não informada</option>
                {Object.entries(ORIGENS).map(([valor, nome]) => (
                  <option key={valor} value={valor}>
                    {nome}
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div>
            <label htmlFor="observacoes" className="campo-rotulo">
              Observações
            </label>
            <textarea
              id="observacoes"
              rows={3}
              className="campo h-auto py-2 resize-none"
              {...register('observacoes')}
            />
          </div>

          <div className="flex justify-end gap-2 pt-1">
            <button type="button" onClick={aoFechar} className="botao-neutro">
              Cancelar
            </button>
            <button type="submit" disabled={criarLead.isPending} className="botao-acao">
              {criarLead.isPending ? 'Gravando' : 'Gravar ficha'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

function Linha({ lead, aoExcluir }: { lead: Lead; aoExcluir: (id: number) => void }) {
  const g = guia(lead.estagio)

  return (
    <tr className="border-t border-pauta hover:bg-aco/40">
      <td className="px-4 py-2.5">
        <p className="font-medium leading-tight">{lead.nome}</p>
        <p className="text-xs text-grafite">{lead.empresa ?? lead.email ?? '—'}</p>
      </td>
      <td className="px-4 py-2.5">
        <Etiqueta cor={g.etiqueta}>{g.nome}</Etiqueta>
      </td>
      <td className="px-4 py-2.5 numero text-sm text-right whitespace-nowrap">
        {lead.valor_estimado ? moeda(lead.valor_estimado) : '—'}
      </td>
      <td className="px-4 py-2.5 text-sm text-tinta-suave hidden md:table-cell">
        {lead.origem ? ORIGENS[lead.origem] : '—'}
      </td>
      <td className="px-4 py-2.5 text-sm hidden lg:table-cell">
        {lead.tarefas_pendentes ? (
          <span className="text-mostarda">
            {plural(lead.tarefas_pendentes, 'pendente', 'pendentes')}
          </span>
        ) : (
          <span className="text-grafite">—</span>
        )}
      </td>
      <td className="px-4 py-2.5 numero text-xs text-grafite hidden sm:table-cell">
        {data(lead.criado_em)}
      </td>
      <td className="px-2 py-2.5 text-right">
        <button
          type="button"
          onClick={() => aoExcluir(lead.id)}
          className="botao-discreto h-7 px-2 text-xs hover:text-carimbo"
        >
          Excluir
        </button>
      </td>
    </tr>
  )
}

export default function PaginaLeads() {
  const [busca, setBusca] = useState('')
  const [estagio, setEstagio] = useState<EstagioLead | ''>('')
  const [criando, setCriando] = useState(false)

  const { data: resposta, isLoading } = useLeads({
    busca: busca || undefined,
    estagio: estagio || undefined,
  })
  const excluirLead = useExcluirLead()

  const excluir = async (id: number) => {
    if (confirm('Excluir esta ficha? O lead sai das listas, mas o histórico fica gravado.')) {
      await excluirLead.mutateAsync(id)
    }
  }

  const leads = resposta?.data ?? []

  return (
    <div className="space-y-4">
      <header className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="font-titulo text-xl">Leads</h1>
          <p className="text-sm text-grafite">
            {plural(resposta?.meta?.total ?? 0, 'ficha no arquivo', 'fichas no arquivo')}.
          </p>
        </div>
        <button type="button" onClick={() => setCriando(true)} className="botao-acao">
          Nova ficha
        </button>
      </header>

      <div className="flex flex-wrap gap-2">
        <input
          value={busca}
          onChange={(e) => setBusca(e.target.value)}
          placeholder="Buscar por nome, e-mail ou empresa"
          aria-label="Buscar leads"
          className="campo flex-1 min-w-[220px]"
        />
        <select
          value={estagio}
          onChange={(e) => setEstagio(e.target.value as EstagioLead | '')}
          aria-label="Filtrar por estágio"
          className="campo w-auto"
        >
          <option value="">Todos os estágios</option>
          {GUIAS.map((g) => (
            <option key={g.chave} value={g.chave}>
              {g.nome}
            </option>
          ))}
        </select>
      </div>

      {isLoading ? (
        <Carregando rotulo="Buscando fichas" />
      ) : leads.length === 0 ? (
        <EstadoVazio
          titulo="Nenhuma ficha por aqui"
          descricao="Ajuste a busca ou abra uma ficha nova para começar a acompanhar o lead."
          acao={
            <button type="button" onClick={() => setCriando(true)} className="botao-acao">
              Nova ficha
            </button>
          }
        />
      ) : (
        <div className="ficha overflow-x-auto">
          <table className="w-full text-left">
            <thead>
              <tr className="etiqueta">
                <th className="px-4 py-2 font-normal">Lead</th>
                <th className="px-4 py-2 font-normal">Estágio</th>
                <th className="px-4 py-2 font-normal text-right">Valor</th>
                <th className="px-4 py-2 font-normal hidden md:table-cell">Origem</th>
                <th className="px-4 py-2 font-normal hidden lg:table-cell">Tarefas</th>
                <th className="px-4 py-2 font-normal hidden sm:table-cell">Aberta em</th>
                <th />
              </tr>
            </thead>
            <tbody>
              {leads.map((lead) => (
                <Linha key={lead.id} lead={lead} aoExcluir={excluir} />
              ))}
            </tbody>
          </table>
        </div>
      )}

      {criando && <FichaNova aoFechar={() => setCriando(false)} />}
    </div>
  )
}
