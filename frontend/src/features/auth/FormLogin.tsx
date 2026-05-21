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

const recursos = [
  { icone: '📊', texto: 'Pipeline de vendas visual' },
  { icone: '👥', texto: 'Gestão completa de leads' },
  { icone: '📋', texto: 'Tarefas e histórico integrado' },
  { icone: '📈', texto: 'Métricas e relatórios em tempo real' },
]

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
    <div className="min-h-screen flex">
      {/* Painel esquerdo — branding */}
      <div className="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-brand-700 via-brand-600 to-brand-500 flex-col justify-between p-12">
        <div>
          <div className="flex items-center gap-3 mb-12">
            <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <span className="text-white font-black text-lg">K</span>
            </div>
            <span className="text-white font-bold text-2xl">KoraCRM</span>
          </div>

          <h2 className="text-white text-3xl font-bold leading-tight mb-4">
            Gerencie seus relacionamentos com inteligência
          </h2>
          <p className="text-brand-100 text-lg leading-relaxed">
            Transforme leads em clientes com uma plataforma CRM completa e intuitiva.
          </p>
        </div>

        <div className="space-y-4">
          {recursos.map((r) => (
            <div key={r.texto} className="flex items-center gap-4">
              <div className="w-10 h-10 bg-white/15 rounded-lg flex items-center justify-center flex-shrink-0">
                <span className="text-lg">{r.icone}</span>
              </div>
              <span className="text-white/90 font-medium">{r.texto}</span>
            </div>
          ))}
        </div>

        <p className="text-brand-200 text-sm">
          © 2024 KoraCRM · Gestão inteligente de relacionamentos
        </p>
      </div>

      {/* Painel direito — formulário */}
      <div className="w-full lg:w-1/2 flex items-center justify-center bg-gray-50 p-6">
        <div className="w-full max-w-md">
          {/* Logo mobile */}
          <div className="flex items-center gap-2 mb-8 lg:hidden">
            <div className="w-9 h-9 bg-brand-600 rounded-lg flex items-center justify-center">
              <span className="text-white font-black">K</span>
            </div>
            <span className="text-brand-600 font-bold text-xl">KoraCRM</span>
          </div>

          <div className="mb-8">
            <h1 className="text-2xl font-bold text-gray-900">Bem-vindo de volta</h1>
            <p className="text-gray-500 mt-1">Acesse sua conta para continuar</p>
          </div>

          {/* Erro da API */}
          {erro && (
            <div className="mb-5 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm flex items-start gap-2">
              <span>⚠️</span>
              <span>{erro}</span>
            </div>
          )}

          <form onSubmit={handleSubmit(onSubmit)} noValidate className="space-y-5">
            <div>
              <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1.5">
                E-mail
              </label>
              <input
                id="email"
                type="email"
                autoComplete="email"
                {...register('email')}
                className={`w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition text-sm
                  ${errors.email ? 'border-red-400 bg-red-50' : 'border-gray-200'}`}
                placeholder="seu@email.com"
              />
              {errors.email && (
                <p className="mt-1.5 text-xs text-red-600">{errors.email.message}</p>
              )}
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label htmlFor="senha" className="block text-sm font-medium text-gray-700">
                  Senha
                </label>
              </div>
              <input
                id="senha"
                type="password"
                autoComplete="current-password"
                {...register('senha')}
                className={`w-full px-4 py-3 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 transition text-sm
                  ${errors.senha ? 'border-red-400 bg-red-50' : 'border-gray-200'}`}
                placeholder="••••••••"
              />
              {errors.senha && (
                <p className="mt-1.5 text-xs text-red-600">{errors.senha.message}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={carregando}
              className="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 disabled:bg-brand-400
                text-white font-semibold rounded-xl transition focus:outline-none focus:ring-2
                focus:ring-brand-500 focus:ring-offset-2 text-sm"
            >
              {carregando ? 'Entrando...' : 'Entrar na conta'}
            </button>
          </form>

          <div className="mt-6">
            {/* Credenciais de demonstração */}
            <div className="mb-4 p-3 bg-brand-50 border border-brand-200 rounded-xl">
              <p className="text-xs font-semibold text-brand-700 mb-1.5">Credenciais de demonstração</p>
              <div className="space-y-0.5 text-xs text-brand-600 font-mono">
                <p>E-mail: <span className="font-semibold">demo@koracrm.com</span></p>
                <p>Senha: <span className="font-semibold">demo1234</span></p>
              </div>
            </div>

            <div className="relative flex items-center gap-3 mb-4">
              <div className="flex-1 h-px bg-gray-200" />
              <span className="text-xs text-gray-400 whitespace-nowrap">ou acesso instantâneo</span>
              <div className="flex-1 h-px bg-gray-200" />
            </div>

            <button
              type="button"
              onClick={loginDemo}
              className="w-full py-3 px-4 bg-white hover:bg-violet-50 text-violet-700 font-semibold
                rounded-xl border border-violet-200 hover:border-violet-300 transition focus:outline-none
                focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 flex items-center justify-center gap-2 text-sm"
            >
              <span>👤</span>
              <span>Entrar como demonstração</span>
            </button>

            <p className="mt-3 text-center text-xs text-gray-400">
              Acesso completo ao sistema · Sem necessidade de cadastro
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
