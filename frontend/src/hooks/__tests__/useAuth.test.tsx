import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { renderHook, act } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { useAuth } from '../useAuth'
import { api } from '../../lib/api'

// Mock da API
vi.mock('../../lib/api', () => ({
  api: {
    post: vi.fn(),
    get: vi.fn(),
  },
}))

// Mock do react-router-dom navigate
const mockNavigate = vi.fn()
vi.mock('react-router-dom', async () => {
  const original = await vi.importActual('react-router-dom')
  return {
    ...original,
    useNavigate: () => mockNavigate,
  }
})

const wrapper = ({ children }: { children: React.ReactNode }) => (
  <MemoryRouter>{children}</MemoryRouter>
)

const usuarioMock = {
  id: 1,
  nome: 'Fabrício Júnio',
  email: 'fabricio@koracrm.com.br',
  perfil: 'vendedor' as const,
  ativo: true,
}

describe('useAuth', () => {
  beforeEach(() => {
    localStorage.clear()
    vi.clearAllMocks()
  })

  afterEach(() => {
    localStorage.clear()
  })

  it('inicia sem usuário autenticado quando localStorage está vazio', () => {
    const { result } = renderHook(() => useAuth(), { wrapper })

    expect(result.current.usuario).toBeNull()
    expect(result.current.estaAutenticado).toBe(false)
  })

  it('inicia com usuário quando há dados no localStorage', () => {
    localStorage.setItem('koracrm_usuario', JSON.stringify(usuarioMock))

    const { result } = renderHook(() => useAuth(), { wrapper })

    expect(result.current.usuario).toEqual(usuarioMock)
    expect(result.current.estaAutenticado).toBe(true)
  })

  it('realiza login com sucesso e salva token', async () => {
    vi.mocked(api.post).mockResolvedValueOnce({
      data: {
        token: 'token_teste_123',
        usuario: usuarioMock,
        expira_em: new Date().toISOString(),
      },
    })

    const { result } = renderHook(() => useAuth(), { wrapper })

    await act(async () => {
      await result.current.login('fabricio@koracrm.com.br', 'senha123456')
    })

    expect(result.current.usuario).toEqual(usuarioMock)
    expect(result.current.estaAutenticado).toBe(true)
    expect(localStorage.getItem('koracrm_token')).toBe('token_teste_123')
    expect(mockNavigate).toHaveBeenCalledWith('/dashboard')
  })

  it('define erro quando login falha', async () => {
    vi.mocked(api.post).mockRejectedValueOnce({
      response: { data: { message: 'Credenciais inválidas.' } },
    })

    const { result } = renderHook(() => useAuth(), { wrapper })

    await act(async () => {
      await result.current.login('errado@email.com', 'senhaerrada')
    })

    expect(result.current.usuario).toBeNull()
    expect(result.current.erro).toBe('Credenciais inválidas.')
    expect(result.current.estaAutenticado).toBe(false)
  })

  it('realiza logout e limpa localStorage', async () => {
    localStorage.setItem('koracrm_token', 'token_123')
    localStorage.setItem('koracrm_usuario', JSON.stringify(usuarioMock))

    vi.mocked(api.post).mockResolvedValueOnce({ data: {} })

    const { result } = renderHook(() => useAuth(), { wrapper })

    await act(async () => {
      await result.current.logout()
    })

    expect(result.current.usuario).toBeNull()
    expect(localStorage.getItem('koracrm_token')).toBeNull()
    expect(localStorage.getItem('koracrm_usuario')).toBeNull()
    expect(mockNavigate).toHaveBeenCalledWith('/login')
  })

  it('limpa dados locais mesmo quando API de logout falha', async () => {
    localStorage.setItem('koracrm_token', 'token_123')
    localStorage.setItem('koracrm_usuario', JSON.stringify(usuarioMock))

    vi.mocked(api.post).mockRejectedValueOnce(new Error('Erro de rede'))

    const { result } = renderHook(() => useAuth(), { wrapper })

    await act(async () => {
      await result.current.logout()
    })

    // Mesmo com erro, dados devem ser limpos
    expect(result.current.usuario).toBeNull()
    expect(localStorage.getItem('koracrm_token')).toBeNull()
  })

  it('isAdmin retorna true apenas para admin', () => {
    localStorage.setItem(
      'koracrm_usuario',
      JSON.stringify({ ...usuarioMock, perfil: 'admin' })
    )

    const { result } = renderHook(() => useAuth(), { wrapper })

    expect(result.current.isAdmin).toBe(true)
    expect(result.current.isGerente).toBe(true)
  })

  it('isGerente retorna true para gerente e admin', () => {
    localStorage.setItem(
      'koracrm_usuario',
      JSON.stringify({ ...usuarioMock, perfil: 'gerente' })
    )

    const { result } = renderHook(() => useAuth(), { wrapper })

    expect(result.current.isAdmin).toBe(false)
    expect(result.current.isGerente).toBe(true)
  })

  it('define carregando como true durante o login', async () => {
    let resolverPromise: (value: unknown) => void
    const promiseControlada = new Promise((resolve) => {
      resolverPromise = resolve
    })

    vi.mocked(api.post).mockReturnValueOnce(promiseControlada as never)

    const { result } = renderHook(() => useAuth(), { wrapper })

    act(() => {
      result.current.login('teste@email.com', 'senha123456')
    })

    expect(result.current.carregando).toBe(true)

    await act(async () => {
      resolverPromise!({
        data: { token: 'tok', usuario: usuarioMock, expira_em: '' },
      })
    })

    expect(result.current.carregando).toBe(false)
  })
})
