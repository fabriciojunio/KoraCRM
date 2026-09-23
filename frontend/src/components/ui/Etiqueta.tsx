import type { ReactNode } from 'react'

export default function Etiqueta({
  children,
  cor = 'bg-aco-fundo text-tinta-suave border-borda',
}: {
  children: ReactNode
  cor?: string
}) {
  return (
    <span
      className={`inline-flex items-center border px-1.5 py-px font-mono text-[10px]
        uppercase tracking-[0.08em] leading-[18px] ${cor}`}
    >
      {children}
    </span>
  )
}
