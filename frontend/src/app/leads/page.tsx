import { useState } from 'react'
import { useLeads, useCriarLead, useExcluirLead } from '../../hooks/useLeads'
import type { Lead, EstagioLead, CriarLeadPayload } from '../../types'
import { Plus, Search, Filter, Trash2, ChevronRight } from 'lucide-react'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'

const estagioLabel: Record<EstagioLead, string> = {
  novo: 'Novo',
  contato: 'Contato',
  proposta: 'Proposta',
  ganho: 'Ganho',
  perdido: 'Perdido',
}

const estagioColor: Record<EstagioLead, string> = {
  novo: 'bg-blue-100 text-blue-800',
  contato: 'bg-yellow-100 text-yellow-800',
  proposta: 'bg-purple-100 text-purple-800',
  ganho: 'bg-green-100 text-green-800',
  perdido: 'bg-red-100 text-red-800',
}

const esquemaCriarLead = z.object({
  nome: z.string().min(1, 'Nome é obrigatório').max(150),
  email: z.string().email('E-mail inválido').optional().or(z.literal('')),
  telefone: z.string().max(20).optional(),
  empresa: z.string().max(150).optional(),
  cargo: z.string().max(100).optional(),
  valor_estimado: z.coerce.number().min(0).optional(),
  origem: z.enum(['site', 'indicacao', 'linkedin', 'evento', 'outros']).optional(),
  observacoes: z.string().max(5000).optional(),
})

type FormLead = z.infer<typeof esquemaCriarLead>

function ModalCriarLead({ onFechar }: { onFechar: () => void }) {
  const criarLead = useCriarLead()
  const { register, handleSubmit, formState: { errors } } = useForm<FormLead>({
    resolver: zodResolver(esquemaCriarLead),
  })

  const onSubmit = async (dados: FormLead) => {
    await criarLead.mutateAsync(dados as CriarLeadPayload)
    onFechar()
  }

  return (
    <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div className="flex items-center justify-between p-6 border-b">
          <h2 className="text-lg font-semibold text-gray-900">Novo Lead</h2>
          <button onClick={onFechar} className="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="p-6 space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input {...register('nome')} className="input" placeholder="Nome do lead ou empresa" />
            {errors.nome && <p className="text-red-600 text-xs mt-1">{errors.nome.message}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
              <input {...register('email')} type="email" className="input" placeholder="email@exemplo.com" />
              {errors.email && <p className="text-red-600 text-xs mt-1">{errors.email.message}</p>}
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
              <input {...register('telefone')} className="input" placeholder="(11) 99999-0000" />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
              <input {...register('empresa')} className="input" placeholder="Nome da empresa" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
              <input {...register('cargo')} className="input" placeholder="Cargo do contato" />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Valor Estimado</label>
              <input {...register('valor_estimado')} type="number" min="0" step="0.01" className="input" placeholder="0,00" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Origem</label>
              <select {...register('origem')} className="input">
                <option value="">Selecionar</option>
                <option value="site">Site</option>
                <option value="indicacao">Indicação</option>
                <option value="linkedin">LinkedIn</option>
                <option value="evento">Evento</option>
                <option value="outros">Outros</option>
              </select>
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea {...register('observacoes')} className="input min-h-[80px] resize-none" placeholder="Notas adicionais..." />
          </div>

          <div className="flex gap-3 pt-2">
            <button type="button" onClick={onFechar} className="btn-secondary flex-1">Cancelar</button>
            <button type="submit" disabled={criarLead.isPending} className="btn-primary flex-1">
              {criarLead.isPending ? 'Salvando...' : 'Criar Lead'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

function CardLead({ lead, onExcluir }: { lead: Lead; onExcluir: (id: number) => void }) {
  const valorFormatado = lead.valor_estimado
    ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(lead.valor_estimado)
    : null

  return (
    <div className="card hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between mb-3">
        <div className="flex-1 min-w-0">
          <h3 className="font-semibold text-gray-900 truncate">{lead.nome}</h3>
          {lead.empresa && <p className="text-sm text-gray-500 truncate">{lead.empresa}</p>}
        </div>
        <span className={`badge ml-2 shrink-0 ${estagioColor[lead.estagio]}`}>
          {estagioLabel[lead.estagio]}
        </span>
      </div>

      <div className="space-y-1 text-sm text-gray-500">
        {lead.email && <p className="truncate">{lead.email}</p>}
        {lead.telefone && <p>{lead.telefone}</p>}
        {valorFormatado && <p className="font-medium text-gray-700">{valorFormatado}</p>}
      </div>

      {lead.tags && lead.tags.length > 0 && (
        <div className="flex flex-wrap gap-1 mt-3">
          {lead.tags.map((tag) => (
            <span key={tag} className="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">{tag}</span>
          ))}
        </div>
      )}

      <div className="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
        <p className="text-xs text-gray-400">
          {new Date(lead.criado_em).toLocaleDateString('pt-BR')}
        </p>
        <div className="flex items-center gap-2">
          <button
            onClick={() => onExcluir(lead.id)}
            className="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
          >
            <Trash2 size={14} />
          </button>
          <button className="p-1.5 text-gray-400 hover:text-brand-600 hover:bg-brand-50 rounded transition-colors">
            <ChevronRight size={14} />
          </button>
        </div>
      </div>
    </div>
  )
}

export default function LeadsPage() {
  const [busca, setBusca] = useState('')
  const [estagioFiltro, setEstagioFiltro] = useState<EstagioLead | ''>('')
  const [modalAberto, setModalAberto] = useState(false)

  const { data, isLoading } = useLeads({
    busca: busca || undefined,
    estagio: estagioFiltro || undefined,
  })

  const excluirLead = useExcluirLead()

  const handleExcluir = async (id: number) => {
    if (confirm('Excluir este lead?')) {
      await excluirLead.mutateAsync(id)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Leads</h1>
          <p className="text-gray-500 text-sm mt-1">
            {data?.meta?.total ?? 0} leads encontrados
          </p>
        </div>
        <button onClick={() => setModalAberto(true)} className="btn-primary flex items-center gap-2">
          <Plus size={16} />
          Novo Lead
        </button>
      </div>

      {/* Filtros */}
      <div className="flex flex-col sm:flex-row gap-3">
        <div className="relative flex-1">
          <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            value={busca}
            onChange={(e) => setBusca(e.target.value)}
            placeholder="Buscar por nome, e-mail ou empresa..."
            className="input pl-9"
          />
        </div>
        <div className="flex items-center gap-2">
          <Filter size={16} className="text-gray-400" />
          <select
            value={estagioFiltro}
            onChange={(e) => setEstagioFiltro(e.target.value as EstagioLead | '')}
            className="input w-auto"
          >
            <option value="">Todos os estágios</option>
            {Object.entries(estagioLabel).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </div>
      </div>

      {/* Lista de leads */}
      {isLoading ? (
        <div className="flex justify-center py-16">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600" />
        </div>
      ) : !data?.data?.length ? (
        <div className="text-center py-16 text-gray-400">
          <Users size={40} className="mx-auto mb-3 opacity-50" />
          <p className="font-medium">Nenhum lead encontrado</p>
          <p className="text-sm mt-1">Crie seu primeiro lead para começar</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {data.data.map((lead) => (
            <CardLead key={lead.id} lead={lead} onExcluir={handleExcluir} />
          ))}
        </div>
      )}

      {modalAberto && <ModalCriarLead onFechar={() => setModalAberto(false)} />}
    </div>
  )
}

function Users({ size, className }: { size: number; className?: string }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width={size} height={size} viewBox="0 0 24 24"
      fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round"
      className={className}>
      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </svg>
  )
}
