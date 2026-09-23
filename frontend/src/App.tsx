import { lazy, Suspense } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { useAuth } from './hooks/useAuth'
import FormLogin from './features/auth/FormLogin'
import Layout from './components/layout/Layout'
import { Carregando } from './components/ui/Estados'

const Painel = lazy(() => import('./app/painel/page'))
const Leads = lazy(() => import('./app/leads/page'))
const Funil = lazy(() => import('./app/funil/page'))
const Tarefas = lazy(() => import('./app/tarefas/page'))
const Equipe = lazy(() => import('./app/equipe/page'))

function RotaProtegida({ children }: { children: React.ReactNode }) {
  const { estaAutenticado } = useAuth()
  if (!estaAutenticado) return <Navigate to="/entrar" replace />
  return <>{children}</>
}

function Pagina({ children }: { children: React.ReactNode }) {
  return <Suspense fallback={<Carregando />}>{children}</Suspense>
}

export default function App() {
  return (
    <BrowserRouter future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
      <Routes>
        <Route path="/entrar" element={<FormLogin />} />
        <Route
          path="/"
          element={
            <RotaProtegida>
              <Layout />
            </RotaProtegida>
          }
        >
          <Route index element={<Navigate to="/painel" replace />} />
          <Route path="painel" element={<Pagina><Painel /></Pagina>} />
          <Route path="leads" element={<Pagina><Leads /></Pagina>} />
          <Route path="funil" element={<Pagina><Funil /></Pagina>} />
          <Route path="tarefas" element={<Pagina><Tarefas /></Pagina>} />
          <Route path="equipe" element={<Pagina><Equipe /></Pagina>} />
        </Route>
        <Route path="*" element={<Navigate to="/painel" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
