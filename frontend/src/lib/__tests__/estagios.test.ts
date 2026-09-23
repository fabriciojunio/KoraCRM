import { describe, it, expect } from 'vitest'
import { GUIAS, guia, ORIGENS, PRIORIDADES } from '../estagios'
import type { EstagioLead } from '../../types'

describe('guias do funil', () => {
  it('tem os cinco estágios, na ordem do funil', () => {
    expect(GUIAS.map((g) => g.chave)).toEqual([
      'novo',
      'contato',
      'proposta',
      'ganho',
      'perdido',
    ])
  })

  it('só ganho e perdido fecham o lead', () => {
    const fechados = GUIAS.filter((g) => g.fechado).map((g) => g.chave)

    expect(fechados).toEqual(['ganho', 'perdido'])
  })

  it('devolve a guia pela chave', () => {
    expect(guia('proposta').nome).toBe('Proposta')
  })

  it('chave desconhecida cai na primeira guia em vez de quebrar a tela', () => {
    expect(guia('inventado' as EstagioLead).chave).toBe('novo')
  })

  it('cada guia tem cor de aba e de etiqueta', () => {
    for (const g of GUIAS) {
      expect(g.aba).not.toBe('')
      expect(g.etiqueta).not.toBe('')
    }
  })
})

describe('vocabulário', () => {
  it('as origens têm rótulo em português', () => {
    expect(ORIGENS.indicacao).toBe('Indicação')
    expect(ORIGENS.site).toBe('Site')
  })

  it('as três prioridades têm rótulo e cor', () => {
    expect(Object.keys(PRIORIDADES)).toEqual(['baixa', 'media', 'alta'])
    expect(PRIORIDADES.media.nome).toBe('Média')
  })
})
