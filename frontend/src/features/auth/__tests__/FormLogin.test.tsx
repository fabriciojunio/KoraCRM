import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { MemoryRouter } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import FormLogin from '../FormLogin'

// Mock do hook useAuth
const mockLogin = vi.fn()
vi.mock('../../../hooks/useAuth', () => ({
  useAuth: () => ({
    login: mockLogin,
    carregando: false,
    erro: null,
    estaAutenticado: false,
  }),
}))

const renderFormLogin = () => {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <FormLogin />
      </MemoryRouter>
    </QueryClientProvider>
  )
}

describe('FormLogin', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renderiza campos de email e senha', () => {
    renderFormLogin()

    expect(screen.getByLabelText(/e-mail/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/senha/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /entrar/i })).toBeInTheDocument()
  })

  it('mostra erro de validação para email inválido', async () => {
    renderFormLogin()
    const user = userEvent.setup()

    await user.type(screen.getByLabelText(/e-mail/i), 'email-invalido')
    await user.click(screen.getByRole('button', { name: /entrar/i }))

    await waitFor(() => {
      expect(screen.getByText(/e-mail inválido/i)).toBeInTheDocument()
    })
  })

  it('mostra erro de validação para senha curta', async () => {
    renderFormLogin()
    const user = userEvent.setup()

    await user.type(screen.getByLabelText(/e-mail/i), 'valido@email.com')
    await user.type(screen.getByLabelText(/senha/i), '123')
    await user.click(screen.getByRole('button', { name: /entrar/i }))

    await waitFor(() => {
      expect(screen.getByText(/mínimo 8 caracteres/i)).toBeInTheDocument()
    })
  })

  it('chama login com credenciais corretas', async () => {
    renderFormLogin()
    const user = userEvent.setup()

    await user.type(screen.getByLabelText(/e-mail/i), 'fabricio@koracrm.com.br')
    await user.type(screen.getByLabelText(/senha/i), 'senha123456')
    await user.click(screen.getByRole('button', { name: /entrar/i }))

    await waitFor(() => {
      expect(mockLogin).toHaveBeenCalledWith(
        'fabricio@koracrm.com.br',
        'senha123456'
      )
    })
  })

  it('não chama login com campos vazios', async () => {
    renderFormLogin()
    const user = userEvent.setup()

    await user.click(screen.getByRole('button', { name: /entrar/i }))

    await waitFor(() => {
      expect(mockLogin).not.toHaveBeenCalled()
    })
  })
})
