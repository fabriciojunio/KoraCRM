import { useQuery } from '@tanstack/react-query'
import { api } from '../../lib/api'
import { isDemo, DEMO_EQUIPE } from '../../lib/demoData'
import { data, iniciais } from '../../lib/formato'
import Etiqueta from '../../components/ui/Etiqueta'
import { Carregando } from '../../components/ui/Estados'
import type { Usuario } from '../../types'

const PERFIS: Record<string, { nome: string; cor: string; pode: string }> = {
  admin: {
    nome: 'Administrador',
    cor: 'bg-caneta-clara text-caneta border-caneta/30',
    pode: 'Tudo, inclusive gerenciar a equipe',
  },
  gerente: {
    nome: 'Gerente',
    cor: 'bg-aprovado-clara text-aprovado border-aprovado/30',
    pode: 'Vê todos os leads e gerencia a equipe',
  },
  vendedor: {
    nome: 'Vendedor',
    cor: 'bg-aco-fundo text-tinta-suave border-borda',
    pode: 'Trabalha as próprias fichas',
  },
}

export default function PaginaEquipe() {
  const { data: equipe, isLoading } = useQuery({
    queryKey: ['usuarios'],
    queryFn: async (): Promise<Usuario[]> => {
      if (isDemo()) return DEMO_EQUIPE
      const { data: resposta } = await api.get<{ data: Usuario[] } | Usuario[]>('/usuarios')
      return Array.isArray(resposta) ? resposta : resposta.data
    },
  })

  if (isLoading || !equipe) return <Carregando rotulo="Buscando a equipe" />

  return (
    <div className="space-y-4">
      <header>
        <h1 className="font-titulo text-xl">Equipe</h1>
        <p className="text-sm text-grafite">
          Quem tem acesso ao sistema e até onde vai cada perfil.
        </p>
      </header>

      <div className="ficha overflow-x-auto">
        <table className="w-full text-left">
          <thead>
            <tr className="etiqueta">
              <th className="px-4 py-2 font-normal">Pessoa</th>
              <th className="px-4 py-2 font-normal">Perfil</th>
              <th className="px-4 py-2 font-normal hidden md:table-cell">Alcance</th>
              <th className="px-4 py-2 font-normal hidden sm:table-cell">Último acesso</th>
              <th className="px-4 py-2 font-normal">Situação</th>
            </tr>
          </thead>
          <tbody>
            {equipe.map((pessoa) => {
              const perfil = PERFIS[pessoa.perfil]
              return (
                <tr key={pessoa.id} className="border-t border-pauta">
                  <td className="px-4 py-2.5">
                    <div className="flex items-center gap-2.5">
                      <span className="h-7 w-7 shrink-0 bg-aco-fundo border border-borda font-mono text-[11px] flex items-center justify-center">
                        {iniciais(pessoa.nome)}
                      </span>
                      <span>
                        <span className="block text-sm leading-tight">{pessoa.nome}</span>
                        <span className="block text-xs text-grafite">{pessoa.email}</span>
                      </span>
                    </div>
                  </td>
                  <td className="px-4 py-2.5">
                    <Etiqueta cor={perfil.cor}>{perfil.nome}</Etiqueta>
                  </td>
                  <td className="px-4 py-2.5 text-sm text-tinta-suave hidden md:table-cell">
                    {perfil.pode}
                  </td>
                  <td className="px-4 py-2.5 numero text-xs text-grafite hidden sm:table-cell">
                    {data(pessoa.ultimo_acesso)}
                  </td>
                  <td className="px-4 py-2.5 text-sm">
                    {pessoa.ativo ? (
                      <span className="text-tinta-suave">Ativo</span>
                    ) : (
                      <span className="text-carimbo">Desativado</span>
                    )}
                  </td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>

      <p className="text-xs text-grafite max-w-leitura">
        O vendedor só enxerga as fichas em que é responsável. A regra vale no
        servidor, nas policies do Laravel, e não apenas nesta tela.
      </p>
    </div>
  )
}
