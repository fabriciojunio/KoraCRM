/*
  O elemento assinatura do sistema: a caixa de fichas vista de frente, com as
  guias de cartolina em alturas diferentes separando os estágios. É a única
  ilustração do produto e aparece na entrada e nas listas vazias.
*/
export default function Fichario({ className = '' }: { className?: string }) {
  const guias = [
    { x: 16, largura: 46, altura: 16, cor: 'rgb(var(--cor-gaveta-clara))' },
    { x: 70, largura: 46, altura: 24, cor: 'rgb(var(--cor-mostarda))' },
    { x: 124, largura: 46, altura: 32, cor: 'rgb(var(--cor-caneta))' },
    { x: 178, largura: 46, altura: 20, cor: 'rgb(var(--cor-aprovado))' },
  ]

  return (
    <svg viewBox="0 0 240 132" className={className} aria-hidden="true">
      {guias.map((g) => (
        <rect
          key={g.x}
          x={g.x}
          y={44 - g.altura}
          width={g.largura}
          height={g.altura + 8}
          fill={g.cor}
        />
      ))}

      <rect x="8" y="44" width="224" height="80" fill="rgb(var(--cor-ficha))" />
      <rect
        x="8.5"
        y="44.5"
        width="223"
        height="79"
        fill="none"
        stroke="rgb(var(--cor-borda-forte))"
      />

      {[60, 74, 88, 102].map((y, i) => (
        <line
          key={y}
          x1="24"
          y1={y}
          x2={i === 3 ? 140 : 216}
          y2={y}
          stroke="rgb(var(--cor-pauta))"
          strokeWidth="2"
        />
      ))}

      <rect x="0" y="124" width="240" height="8" fill="rgb(var(--cor-borda))" />
    </svg>
  )
}
