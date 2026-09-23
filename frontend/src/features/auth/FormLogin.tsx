import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { useAuth } from '../../hooks/useAuth'
import Marca from '../../components/ui/Marca'
import Fichario from '../../components/ui/Fichario'

const esquemaLogin = z.object({
  email: z.string().min(1, 'E-mail é obrigatório').email('E-mail inválido'),
  senha: z.string().min(8, 'Mínimo 8 caracteres'),
})

type DadosLogin = z.infer<typeof esquemaLogin>

export default function FormLogin() {
  const { login, loginDemo, carregando, erro } = useAuth()

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<DadosLogin>({ resolver: zodResolver(esquemaLogin) })

  return (
    <div className="min-h-full grid lg:grid-cols-[1.05fr_1fr]">
      <section className="hidden lg:flex flex-col justify-between bg-gaveta text-white p-10">
        <Marca claro />

        <div>
          <h1 className="font-titulo text-[28px] leading-tight text-white max-w-[22ch]">
            O fichário de vendas, com quem ligou e o que ficou combinado.
          </h1>
          <p className="mt-3 text-white/60 text-[15px] max-w-[46ch]">
            Lead, estágio, tarefa e histórico no mesmo lugar. Cada movimento do
            funil fica registrado com autor, data e valor.
          </p>

          <Fichario className="w-[340px] mt-10" />
        </div>

        <dl className="grid grid-cols-3 gap-6 border-t border-white/15 pt-5">
          {[
            ['Estágios', 'Cinco'],
            ['Perfis de acesso', 'Três'],
            ['Trilha de auditoria', 'Completa'],
          ].map(([rotulo, valor]) => (
            <div key={rotulo}>
              <dt className="etiqueta text-white/45">{rotulo}</dt>
              <dd className="font-titulo text-lg mt-0.5">{valor}</dd>
            </div>
          ))}
        </dl>
      </section>

      <section className="flex items-center justify-center p-6">
        <div className="w-full max-w-[380px]">
          <div className="lg:hidden mb-8">
            <Marca />
          </div>

          <h2 className="font-titulo text-[22px]">Entrar</h2>
          <p className="text-sm text-grafite mt-1">
            Use a conta da sua equipe comercial.
          </p>

          {erro && (
            <p
              role="alert"
              className="mt-5 border border-carimbo/40 bg-carimbo-clara px-3 py-2 text-sm text-carimbo"
            >
              {erro}
            </p>
          )}

          <form
            onSubmit={handleSubmit((dados) => login(dados.email, dados.senha))}
            noValidate
            className="mt-6 space-y-4"
          >
            <div>
              <label htmlFor="email" className="campo-rotulo">
                E-mail
              </label>
              <input
                id="email"
                type="email"
                autoComplete="email"
                className="campo"
                placeholder="voce@empresa.com.br"
                {...register('email')}
              />
              {errors.email && <p className="campo-erro">{errors.email.message}</p>}
            </div>

            <div>
              <label htmlFor="senha" className="campo-rotulo">
                Senha
              </label>
              <input
                id="senha"
                type="password"
                autoComplete="current-password"
                className="campo"
                {...register('senha')}
              />
              {errors.senha && <p className="campo-erro">{errors.senha.message}</p>}
            </div>

            <button type="submit" disabled={carregando} className="botao-acao w-full">
              {carregando ? 'Entrando' : 'Entrar na conta'}
            </button>
          </form>

          <div className="mt-8 border-t border-pauta pt-6">
            <p className="etiqueta">Sem instalar nada</p>
            <button type="button" onClick={loginDemo} className="botao-neutro w-full mt-2">
              Entrar como demonstração
            </button>
            <p className="mt-2 text-xs text-grafite">
              Abre o sistema inteiro com dados fictícios, no seu navegador. Nada
              é enviado ao servidor.
            </p>
          </div>
        </div>
      </section>
    </div>
  )
}
