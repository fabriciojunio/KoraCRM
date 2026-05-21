import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useAuth } from '../../hooks/useAuth'

const esquemaLogin = z.object({
  email: z
    .string()
    .min(1, 'E-mail é obrigatório')
    .email('E-mail inválido'),
  senha: z
    .string()
    .min(8, 'Mínimo 8 caracteres'),
})

type DadosLogin = z.infer<typeof esquemaLogin>

export default function FormLogin() {
  const { login, loginDemo, carregando, erro } = useAuth()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<DadosLogin>({
    resolver: zodResolver(esquemaLogin),
  })

  const onSubmit = async (dados: DadosLogin) => {
    await login(dados.email, dados.senha)
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        {/* Logo e título */}
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-brand-600">KoraCRM</h1>
          <p className="text-gray-500 mt-2">Gestão inteligente de relacionamentos</p>
        </div>

        {/* Erro da API */}
        {erro && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
            {erro}
          </div>
        )}

        <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-5">
          {/* Campo e-mail */}
          <div>
            <label
              htmlFor="email"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              E-mail
            </label>
            <input
              id="email"
              type="email"
              autoComplete="email"
              {...register('email')}
              className={`w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 transition
                ${errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300'}`}
              placeholder="seu@email.com"
            />
            {errors.email && (
              <p className="mt-1 text-sm text-red-600">{errors.email.message}</p>
            )}
          </div>

          {/* Campo senha */}
          <div>
            <label
              htmlFor="senha"
              className="block text-sm font-medium text-gray-700 mb-1"
            >
              Senha
            </label>
            <input
              id="senha"
              type="password"
              autoComplete="current-password"
              {...register('senha')}
              className={`w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 transition
                ${errors.senha ? 'border-red-400 bg-red-50' : 'border-gray-300'}`}
              placeholder="••••••••"
            />
            {errors.senha && (
              <p className="mt-1 text-sm text-red-600">{errors.senha.message}</p>
            )}
          </div>

          {/* Botão */}
          <button
            type="submit"
            disabled={carregando}
            className="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-700 disabled:bg-brand-400
              text-white font-semibold rounded-lg transition focus:outline-none focus:ring-2
              focus:ring-brand-500 focus:ring-offset-2"
          >
            {carregando ? 'Entrando...' : 'Entrar'}
          </button>
        </form>

        <div className="mt-6 pt-5 border-t border-gray-100 flex flex-col items-center gap-3">
          <p className="text-xs text-gray-400">Quer explorar sem cadastro?</p>
          <button
            type="button"
            onClick={loginDemo}
            className="w-full py-2.5 px-4 bg-violet-50 hover:bg-violet-100 text-violet-700
              font-semibold rounded-lg transition border border-violet-200 focus:outline-none
              focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 flex items-center justify-center gap-2"
          >
            <span className="text-base">👤</span>
            <span>Entrar como demonstração</span>
          </button>
        </div>
      </div>
    </div>
  )
}
