import { useState } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import { isDemo } from '../../lib/demoData'
import { iniciais } from '../../lib/formato'
import Marca from '../ui/Marca'

const GUIAS: { nome: string; href: string; somenteGestao?: boolean }[] = [
  { nome: 'Painel', href: '/painel' },
  { nome: 'Leads', href: '/leads' },
  { nome: 'Funil', href: '/funil' },
  { nome: 'Tarefas', href: '/tarefas' },
  { nome: 'Equipe', href: '/equipe', somenteGestao: true },
]

function Guia({ nome, href }: { nome: string; href: string }) {
  return (
    <NavLink
      to={href}
      className={({ isActive }) =>
        `relative -mb-px px-4 py-2 border border-b-0 font-titulo text-sm whitespace-nowrap
         transition-colors ${
           isActive
             ? 'bg-ficha border-borda-forte text-tinta'
             : 'bg-aco-fundo border-borda text-tinta-suave hover:bg-ficha/70'
         }`
      }
    >
      {({ isActive }) => (
        <>
          <span
            className={`absolute inset-x-0 top-0 h-[3px] ${isActive ? 'bg-caneta' : 'bg-transparent'}`}
          />
          {nome}
        </>
      )}
    </NavLink>
  )
}

export default function Layout() {
  const { usuario, logout, isGerente } = useAuth()
  const [menuAberto, setMenuAberto] = useState(false)
  const demo = isDemo()

  const guias = GUIAS.filter((g) => !g.somenteGestao || isGerente)

  return (
    <div className="min-h-full flex flex-col">
      <header className="bg-gaveta text-white">
        <div className="mx-auto max-w-[1180px] px-4 h-[52px] flex items-center justify-between">
          <Marca claro />

          <div className="relative">
            <button
              type="button"
              onClick={() => setMenuAberto((v) => !v)}
              className="flex items-center gap-2.5 h-9 pl-1.5 pr-2.5 text-left hover:bg-white/10"
              aria-expanded={menuAberto}
            >
              <span className="h-7 w-7 bg-white/10 font-mono text-[11px] flex items-center justify-center">
                {iniciais(usuario?.nome)}
              </span>
              <span className="hidden sm:block leading-tight">
                <span className="block text-[13px]">{usuario?.nome}</span>
                <span className="block etiqueta text-white/50 text-[10px]">{usuario?.perfil}</span>
              </span>
            </button>

            {menuAberto && (
              <div className="absolute right-0 top-full mt-1 z-30 w-44 bg-ficha border border-borda-forte">
                <p className="px-3 py-2 border-b border-pauta text-xs text-grafite truncate">
                  {usuario?.email}
                </p>
                <button
                  type="button"
                  onClick={logout}
                  className="w-full text-left px-3 py-2 text-sm text-tinta hover:bg-aco-fundo"
                >
                  Sair da conta
                </button>
              </div>
            )}
          </div>
        </div>
      </header>

      {demo && (
        <div className="bg-mostarda-clara border-b border-mostarda/30">
          <div className="mx-auto max-w-[1180px] px-4 py-1.5 flex items-center gap-2">
            <span className="etiqueta text-mostarda">Demonstração</span>
            <p className="text-[13px] text-tinta-suave">
              Os dados desta sessão são fictícios e nada é gravado no servidor.
            </p>
          </div>
        </div>
      )}

      <div className="mx-auto w-full max-w-[1180px] px-4 pt-4">
        <nav className="flex gap-1 overflow-x-auto border-b border-borda-forte" aria-label="Seções">
          {guias.map((g) => (
            <Guia key={g.href} nome={g.nome} href={g.href} />
          ))}
        </nav>
      </div>

      <main className="flex-1 mx-auto w-full max-w-[1180px] px-4 py-6">
        <Outlet />
      </main>

      <footer className="border-t border-borda">
        <div className="mx-auto max-w-[1180px] px-4 py-4 flex flex-wrap gap-x-4 gap-y-1 justify-between etiqueta">
          <span>KoraCRM · Gestão comercial</span>
          <span>Laravel 11 · React 18</span>
        </div>
      </footer>
    </div>
  )
}
