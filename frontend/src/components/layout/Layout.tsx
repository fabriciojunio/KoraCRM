import { Outlet, NavLink } from 'react-router-dom'
import { useAuth } from '../../hooks/useAuth'
import { LayoutDashboard, Users, Kanban, CheckSquare, LogOut, Menu, X } from 'lucide-react'
import { useState } from 'react'

const navegacao = [
  { nome: 'Dashboard', href: '/dashboard', icone: LayoutDashboard },
  { nome: 'Leads', href: '/leads', icone: Users },
  { nome: 'Pipeline', href: '/pipeline', icone: Kanban },
  { nome: 'Tarefas', href: '/tarefas', icone: CheckSquare },
]

export default function Layout() {
  const { usuario, logout } = useAuth()
  const [aberta, setAberta] = useState(false)

  return (
    <div className="flex h-screen">
      {aberta && (
        <div
          className="fixed inset-0 bg-black/40 z-20 lg:hidden"
          onClick={() => setAberta(false)}
        />
      )}

      <aside
        className={`fixed inset-y-0 left-0 z-30 w-60 flex flex-col transform transition-transform
          lg:static lg:translate-x-0 ${aberta ? 'translate-x-0' : '-translate-x-full'}`}
        style={{ backgroundColor: 'var(--sidebar-bg)' }}
      >
        <div className="flex items-center justify-between h-14 px-5">
          <div className="flex items-center gap-2">
            <span className="h-2.5 w-2.5 rounded-sm bg-brand-500" />
            <span className="text-[15px] font-semibold tracking-tight text-white">
              KoraCRM
            </span>
          </div>
          <button
            className="lg:hidden text-white/50 hover:text-white"
            onClick={() => setAberta(false)}
          >
            <X size={18} />
          </button>
        </div>

        <nav className="flex-1 px-3 py-4 space-y-0.5">
          {navegacao.map((item) => (
            <NavLink
              key={item.href}
              to={item.href}
              onClick={() => setAberta(false)}
              className={({ isActive }) =>
                `nav-link ${isActive ? 'nav-link-active' : ''}`
              }
            >
              <item.icone size={17} strokeWidth={1.75} />
              {item.nome}
            </NavLink>
          ))}
        </nav>

        <div
          className="px-3 py-4 mt-auto"
          style={{ borderTop: '1px solid rgba(255,255,255,0.08)' }}
        >
          <div className="flex items-center gap-3 px-2 mb-3">
            <div className="h-8 w-8 rounded-md bg-white/10 flex items-center justify-center">
              <span className="text-sm font-medium text-white">
                {usuario?.nome?.charAt(0).toUpperCase()}
              </span>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium text-white truncate">{usuario?.nome}</p>
              <p className="text-xs capitalize" style={{ color: 'var(--sidebar-fg-dim)' }}>
                {usuario?.perfil}
              </p>
            </div>
          </div>
          <button onClick={logout} className="nav-link w-full">
            <LogOut size={16} strokeWidth={1.75} />
            Sair
          </button>
        </div>
      </aside>

      <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header
          className="lg:hidden flex items-center h-14 px-4 bg-white"
          style={{ borderBottom: '1px solid var(--border)' }}
        >
          <button
            onClick={() => setAberta(true)}
            className="text-gray-500 hover:text-gray-800"
          >
            <Menu size={22} />
          </button>
          <span className="ml-3 text-[15px] font-semibold text-gray-900">KoraCRM</span>
        </header>

        <main className="flex-1 overflow-auto px-6 py-7 lg:px-8">
          <div className="mx-auto max-w-6xl">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  )
}
