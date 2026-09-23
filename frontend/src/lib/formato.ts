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

const SO_DATA = /^\d{4}-\d{2}-\d{2}$/

/*
  "2026-09-23" é lido pelo JavaScript como meia-noite em UTC, que no Brasil é
  nove da noite do dia anterior. Sem este tratamento, um prazo para hoje
  aparece como ontem e a tarefa nasce atrasada. Data com hora vem do servidor
  já com fuso e é usada como está.
*/
function paraData(iso: string): Date {
  if (SO_DATA.test(iso)) {
    const [ano, mes, dia] = iso.split('-').map(Number)
    return new Date(ano, mes - 1, dia)
  }

  return new Date(iso)
}

export const moeda = (valor?: number | null) => real.format(valor ?? 0)
export const moedaCompacta = (valor?: number | null) => realCompacto.format(valor ?? 0)

export const data = (iso?: string | null) => (iso ? dia.format(paraData(iso)) : '—')
export const dataCurta = (iso?: string | null) => (iso ? diaCurto.format(paraData(iso)) : '—')

export function estaAtrasada(prazo?: string | null, concluida = false): boolean {
  if (!prazo || concluida) return false

  const hoje = new Date()
  hoje.setHours(0, 0, 0, 0)

  const vencimento = paraData(prazo)
  vencimento.setHours(0, 0, 0, 0)

  return vencimento < hoje
}

export function primeiroNome(nome?: string | null): string {
  return partes(nome)[0] ?? ''
}

export function iniciais(nome?: string | null): string {
  const pedacos = partes(nome)

  if (pedacos.length === 0) return '?'
  if (pedacos.length === 1) return pedacos[0].slice(0, 2).toUpperCase()

  return (pedacos[0][0] + pedacos[pedacos.length - 1][0]).toUpperCase()
}

export function plural(quantidade: number, singular: string, plural: string): string {
  return `${quantidade} ${quantidade === 1 ? singular : plural}`
}

// split em string vazia devolve [''], e um pedaço vazio passava adiante como
// se fosse nome.
function partes(nome?: string | null): string[] {
  return (nome ?? '').trim().split(/\s+/).filter(Boolean)
}
