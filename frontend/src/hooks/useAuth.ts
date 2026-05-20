import { useState, useCallback } from 'react'
import { useNavigate } from 'react-router-dom'
import { api } from '../lib/api'
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

      navigate('/dashboard')
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

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout')
    } catch {
      // ignora erro de rede — limpamos a sessão local de qualquer forma
    } finally {
      localStorage.removeItem('koracrm_token')
      localStorage.removeItem('koracrm_usuario')
      setEstado({ usuario: null, carregando: false, erro: null })
      navigate('/login')
    }
  }, [navigate])

  return {
    usuario: estado.usuario,
    carregando: estado.carregando,
    erro: estado.erro,
    login,
    logout,
    estaAutenticado: estado.usuario !== null,
    isAdmin: estado.usuario?.perfil === 'admin',
    isGerente: ['admin', 'gerente'].includes(estado.usuario?.perfil ?? ''),
  }
}
