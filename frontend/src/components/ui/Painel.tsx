import type { ReactNode } from 'react'

interface Props {
  titulo: string
  acao?: ReactNode
  children: ReactNode
  className?: string
}

export default function Painel({ titulo, acao, children, className = '' }: Props) {
  return (
    <section className={`ficha ${className}`}>
      <header className="ficha-titulo">
        <h2 className="etiqueta text-tinta-suave">{titulo}</h2>
        {acao}
      </header>
      {children}
    </section>
  )
}
