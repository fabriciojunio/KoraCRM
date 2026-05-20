import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from './hooks/useAuth'
import FormLogin from './features/auth/FormLogin'
import Layout from './components/layout/Layout'
import Dashboard from './app/dashboard/page'
import LeadsPage from './app/leads/page'
import PipelinePage from './app/pipeline/page'
import TarefasPage from './app/tarefas/page'

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
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="leads" element={<LeadsPage />} />
          <Route path="pipeline" element={<PipelinePage />} />
          <Route path="tarefas" element={<TarefasPage />} />
        </Route>
        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
