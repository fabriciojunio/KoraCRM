interface Props {
  claro?: boolean
  compacta?: boolean
}

/* Símbolo: uma ficha com a guia saindo pelo alto, vista de frente. */
export function Simbolo({ tamanho = 22 }: { tamanho?: number }) {
  return (
    <svg
      width={tamanho}
      height={tamanho}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="1.8"
      aria-hidden="true"
    >
      <path d="M4 7h16v13H4z" />
      <path d="M8 7V4h6v3" />
      <path d="M7.5 12h9M7.5 15.5h5.5" strokeWidth="1.3" />
    </svg>
  )
}

export default function Marca({ claro = false, compacta = false }: Props) {
  return (
    <span className={`inline-flex items-center gap-2 ${claro ? 'text-white' : 'text-tinta'}`}>
      <Simbolo tamanho={compacta ? 19 : 22} />
      <span className="font-titulo font-bold tracking-tight text-[17px] leading-none">
        Kora<span className={claro ? 'text-white/55' : 'text-grafite'}>CRM</span>
      </span>
    </span>
  )
}
