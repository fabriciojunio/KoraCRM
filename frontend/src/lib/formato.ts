const real = new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
  maximumFractionDigits: 0,
})

const realCompacto = new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
  notation: 'compact',
  maximumFractionDigits: 1,
})

const dia = new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' })
const diaCurto = new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: 'short' })

export const moeda = (valor?: number | null) => real.format(valor ?? 0)
export const moedaCompacta = (valor?: number | null) => realCompacto.format(valor ?? 0)

export const data = (iso?: string | null) => (iso ? dia.format(new Date(iso)) : '—')
export const dataCurta = (iso?: string | null) => (iso ? diaCurto.format(new Date(iso)) : '—')

export function estaAtrasada(prazo?: string | null, concluida = false): boolean {
  if (!prazo || concluida) return false
  const hoje = new Date()
  hoje.setHours(0, 0, 0, 0)
  return new Date(prazo) < hoje
}

export function primeiroNome(nome?: string | null): string {
  return nome?.trim().split(/\s+/)[0] ?? ''
}

export function iniciais(nome?: string | null): string {
  const partes = nome?.trim().split(/\s+/) ?? []
  if (partes.length === 0) return '?'
  if (partes.length === 1) return partes[0].slice(0, 2).toUpperCase()
  return (partes[0][0] + partes[partes.length - 1][0]).toUpperCase()
}

export function plural(quantidade: number, singular: string, plural: string): string {
  return `${quantidade} ${quantidade === 1 ? singular : plural}`
}
