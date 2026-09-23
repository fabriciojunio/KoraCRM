import type { ReactNode } from 'react'
import Fichario from './Fichario'

export function Carregando({ rotulo = 'Carregando' }: { rotulo?: string }) {
  return (
    <div role="status" className="flex items-center gap-3 py-16 justify-center text-grafite">
      <span className="h-3 w-3 border-2 border-borda border-t-caneta animate-spin" />
      <span className="etiqueta">{rotulo}</span>
    </div>
  )
}

export function EstadoVazio({
  titulo,
  descricao,
  acao,
}: {
  titulo: string
  descricao: string
  acao?: ReactNode
}) {
  return (
    <div className="ficha flex flex-col items-center text-center px-6 py-12">
      <Fichario className="w-40 opacity-60" />
      <h3 className="mt-6 font-titulo text-base">{titulo}</h3>
      <p className="mt-1 text-sm text-grafite max-w-[46ch]">{descricao}</p>
      {acao && <div className="mt-5">{acao}</div>}
    </div>
  )
}

export function Falha({ mensagem, aoTentar }: { mensagem: string; aoTentar?: () => void }) {
  return (
    <div
      role="alert"
      className="ficha border-l-2 border-l-carimbo px-4 py-3 flex items-center justify-between gap-4"
    >
      <p className="text-sm text-tinta">{mensagem}</p>
      {aoTentar && (
        <button type="button" onClick={aoTentar} className="botao-neutro h-8">
          Tentar de novo
        </button>
      )}
    </div>
  )
}
