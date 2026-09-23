import { useState, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
import { DEMO_USUARIO, TOKEN_DEMO } from '../lib/demoData'
import type { Usuario, TokenPayload } from '../types'

interface EstadoAuth {
  usuario: Usuario | null
  carregando: boolean
  erro: string | null
}

interface RetornoAuth {
  usuario: Usuario | null
  carregando: boolean
  erro: string | null
  login: (email: string, senha: string) => Promise<void>
  loginDemo: () => void
  logout: () => Promise<void>
  estaAutenticado: boolean
  isAdmin: boolean
  isGerente: boolean
}

export function useAuth(): RetornoAuth {
  const navigate = useNavigate()

  const [estado, setEstado] = useState<EstadoAuth>(() => {
    const dadosSalvos = localStorage.getItem('koracrm_usuario')
    return {
      usuario: dadosSalvos ? JSON.parse(dadosSalvos) : null,
      carregando: false,
      erro: null,
    }
  })

  const login = useCallback(async (email: string, senha: string) => {
    setEstado((prev) => ({ ...prev, carregando: true, erro: null }))

    try {
      const { data } = await api.post<TokenPayload>('/auth/login', {
        email,
        senha,
      })

      localStorage.setItem('koracrm_token', data.token)
      localStorage.setItem('koracrm_usuario', JSON.stringify(data.usuario))

      setEstado({
        usuario: data.usuario,
        carregando: false,
        erro: null,
      })

      navigate('/painel')
    } catch (err: unknown) {
      const mensagem =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message
        ?? 'Erro ao realizar login. Tente novamente.'

      setEstado({
        usuario: null,
        carregando: false,
        erro: mensagem,
      })
    }
  }, [navigate])

  const loginDemo = useCallback(() => {
    localStorage.setItem('koracrm_token', TOKEN_DEMO)
    localStorage.setItem('koracrm_usuario', JSON.stringify(DEMO_USUARIO))
    setEstado({ usuario: DEMO_USUARIO, carregando: false, erro: null })
    navigate('/painel')
  }, [navigate])

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout')
    } catch {
      // ignora erro de rede — limpamos a sessão local de qualquer forma
    } finally {
      localStorage.removeItem('koracrm_token')
      localStorage.removeItem('koracrm_usuario')
      setEstado({ usuario: null, carregando: false, erro: null })
      navigate('/entrar')
    }
  }, [navigate])

  return {
    usuario: estado.usuario,
    carregando: estado.carregando,
    erro: estado.erro,
    login,
    loginDemo,
    logout,
    estaAutenticado: estado.usuario !== null,
    isAdmin: estado.usuario?.perfil === 'admin',
    isGerente: ['admin', 'gerente'].includes(estado.usuario?.perfil ?? ''),
  }
}
