import { lazy, Suspense } from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from './hooks/useAuth'
import FormLogin from './features/auth/FormLogin'
import Layout from './components/layout/Layout'

const Dashboard    = lazy(() => import('./app/dashboard/page'))
const LeadsPage    = lazy(() => import('./app/leads/page'))
const PipelinePage = lazy(() => import('./app/pipeline/page'))
const TarefasPage  = lazy(() => import('./app/tarefas/page'))

// Rota protegida — redireciona para login se não autenticado
function RotaProtegida({ children }: { children: React.ReactNode }) {
  const { estaAutenticado } = useAuth()
  if (!estaAutenticado) return <Navigate to="/login" replace />
  return <>{children}</>
}

export default function App() {
  return (
    <BrowserRouter
      future={{ v7_startTransition: true, v7_relativeSplatPath: true }}
    >
      <Routes>
        <Route path="/login" element={<FormLogin />} />
        <Route
          path="/"
          element={
            <RotaProtegida>
              <Layout />
            </RotaProtegida>
          }
        >
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<Suspense fallback={null}><Dashboard /></Suspense>} />
          <Route path="leads"     element={<Suspense fallback={null}><LeadsPage /></Suspense>} />
          <Route path="pipeline"  element={<Suspense fallback={null}><PipelinePage /></Suspense>} />
          <Route path="tarefas"   element={<Suspense fallback={null}><TarefasPage /></Suspense>} />
        </Route>
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
