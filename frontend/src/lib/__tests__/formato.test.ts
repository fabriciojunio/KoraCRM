import { describe, it, expect, afterEach, vi } from 'vitest'
import {
  moeda,
  moedaCompacta,
  data,
  dataCurta,
  estaAtrasada,
  primeiroNome,
  iniciais,
  plural,
} from '../formato'

// O Intl separa o "R$" do número com espaço inquebrável, que parece igual ao
// espaço comum e não é. Comparar com igualdade exata deixa o teste falhando
// com duas strings idênticas na tela, então a conferência é por expressão,
// onde \s aceita os dois.
describe('moeda', () => {
  it('formata em real, sem centavos', () => {
    expect(moeda(18500)).toMatch(/^R\$\s18\.500$/)
  })

  it('trata ausência de valor como zero', () => {
    expect(moeda(undefined)).toMatch(/^R\$\s0$/)
    expect(moeda(null)).toMatch(/^R\$\s0$/)
  })

  it('compacta valor grande', () => {
    expect(moedaCompacta(32000)).toMatch(/32/)
    expect(moedaCompacta(32000)).toMatch(/mil/)
  })
})

describe('data', () => {
  it('formata no padrão daqui', () => {
    expect(data('2026-09-23T12:00:00Z')).toBe('23/09/2026')
  })

  it('data sem hora não volta um dia por causa do fuso', () => {
    expect(data('2026-09-23')).toBe('23/09/2026')
  })

  it('devolve travessão quando não tem data', () => {
    expect(data(undefined)).toBe('—')
    expect(dataCurta(null)).toBe('—')
  })
})

describe('estaAtrasada', () => {
  afterEach(() => {
    vi.useRealTimers()
  })

  it('tarefa sem prazo nunca está atrasada', () => {
    expect(estaAtrasada(undefined)).toBe(false)
  })

  it('tarefa concluída não conta como atrasada', () => {
    expect(estaAtrasada('2020-01-01', true)).toBe(false)
  })

  it('prazo vencido está atrasado', () => {
    expect(estaAtrasada('2020-01-01')).toBe(true)
  })

  it('o prazo de hoje ainda não venceu', () => {
    vi.useFakeTimers()
    vi.setSystemTime(new Date('2026-09-23T15:00:00'))

    expect(estaAtrasada('2026-09-23')).toBe(false)
  })
})

describe('nome', () => {
  it('pega o primeiro nome', () => {
    expect(primeiroNome('Marina Aguiar Souza')).toBe('Marina')
  })

  it('devolve vazio quando não tem nome', () => {
    expect(primeiroNome(undefined)).toBe('')
  })

  it('monta as iniciais com o primeiro e o último nome', () => {
    expect(iniciais('Marina Aguiar Souza')).toBe('MS')
  })

  it('com um nome só, usa as duas primeiras letras', () => {
    expect(iniciais('Marina')).toBe('MA')
  })

  it('sem nome, devolve interrogação', () => {
    expect(iniciais('')).toBe('?')
    expect(iniciais('   ')).toBe('?')
  })
})

describe('plural', () => {
  it('usa o singular no um', () => {
    expect(plural(1, 'ficha', 'fichas')).toBe('1 ficha')
  })

  it('usa o plural no zero e no resto', () => {
    expect(plural(0, 'ficha', 'fichas')).toBe('0 fichas')
    expect(plural(9, 'ficha', 'fichas')).toBe('9 fichas')
  })
})
