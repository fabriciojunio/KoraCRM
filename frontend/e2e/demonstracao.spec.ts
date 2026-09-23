import { test, expect, type Page } from '@playwright/test'

async function entrarNaDemonstracao(page: Page) {
  await page.goto('/entrar')
  await page.getByRole('button', { name: 'Entrar como demonstração' }).click()
  await expect(page).toHaveURL(/\/painel$/)
}

test.beforeEach(async ({ page }) => {
  await entrarNaDemonstracao(page)
})

test('a entrada pela demonstração abre o painel com a faixa de aviso', async ({ page }) => {
  await expect(page.getByText('Os dados desta sessão são fictícios')).toBeVisible()
  await expect(page.getByRole('heading', { name: 'Painel', level: 1 })).toBeVisible()
  await expect(page.getByText('Leads', { exact: true }).first()).toBeVisible()
})

test('sem sessão, qualquer rota cai na entrada', async ({ page, context }) => {
  await context.clearCookies()
  await page.evaluate(() => localStorage.clear())
  await page.goto('/leads')
  await expect(page).toHaveURL(/\/entrar$/)
  await expect(page.getByRole('button', { name: 'Entrar na conta' })).toBeVisible()
})

test('as guias levam a cada seção do sistema', async ({ page }) => {
  for (const guia of ['Leads', 'Funil', 'Tarefas', 'Equipe']) {
    await page.getByRole('link', { name: guia, exact: true }).click()
    await expect(page.getByRole('heading', { name: guia, level: 1 })).toBeVisible()
  }
})

test('a busca de leads filtra a tabela e o filtro de estágio também', async ({ page }) => {
  await page.getByRole('link', { name: 'Leads', exact: true }).click()

  const linhas = page.locator('tbody tr')
  await expect(linhas).toHaveCount(9)

  await page.getByLabel('Buscar leads').fill('nexus')
  await expect(linhas).toHaveCount(1)
  await expect(linhas.first()).toContainText('Grupo Nexus')

  await page.getByLabel('Buscar leads').fill('')
  await page.getByLabel('Filtrar por estágio').selectOption('ganho')
  await expect(linhas).toHaveCount(1)
  await expect(linhas.first()).toContainText('Alfa Sistemas')
})

test('a busca sem resultado mostra o estado vazio, não uma tabela em branco', async ({ page }) => {
  await page.getByRole('link', { name: 'Leads', exact: true }).click()
  await page.getByLabel('Buscar leads').fill('empresa que não existe')

  await expect(page.getByText('Nenhuma ficha por aqui')).toBeVisible()
  await expect(page.locator('tbody tr')).toHaveCount(0)
})

test('gravar uma ficha nova coloca o lead na lista e na primeira guia do funil', async ({ page }) => {
  await page.getByRole('link', { name: 'Leads', exact: true }).click()
  await page.getByRole('button', { name: 'Nova ficha' }).click()

  const ficha = page.getByRole('dialog', { name: 'Nova ficha de lead' })
  await ficha.getByLabel('Nome ou razão social').fill('Padaria do Bairro')
  await ficha.getByLabel('Empresa').fill('Padaria do Bairro Ltda')
  await ficha.getByLabel('Valor estimado').fill('3500')
  await ficha.getByRole('button', { name: 'Gravar ficha' }).click()

  await expect(ficha).toBeHidden()
  await expect(page.locator('tbody tr').first()).toContainText('Padaria do Bairro')

  await page.getByRole('link', { name: 'Funil', exact: true }).click()
  const colunaNovo = page.locator('section').filter({ has: page.getByRole('heading', { name: 'Novo' }) })
  await expect(colunaNovo).toContainText('Padaria do Bairro')
})

test('a ficha nova recusa nome em branco', async ({ page }) => {
  await page.getByRole('link', { name: 'Leads', exact: true }).click()
  await page.getByRole('button', { name: 'Nova ficha' }).click()

  const ficha = page.getByRole('dialog', { name: 'Nova ficha de lead' })
  await ficha.getByRole('button', { name: 'Gravar ficha' }).click()

  await expect(ficha.getByText('Nome é obrigatório')).toBeVisible()
  await expect(ficha).toBeVisible()
})

test('mover um lead pelo seletor muda a guia e some o seletor quando fecha', async ({ page }) => {
  await page.getByRole('link', { name: 'Funil', exact: true }).click()

  const cartao = page.locator('article').filter({ hasText: 'LogisPrime' })
  await cartao.getByRole('combobox').selectOption('proposta')

  const colunaProposta = page
    .locator('section')
    .filter({ has: page.getByRole('heading', { name: 'Proposta' }) })
  await expect(colunaProposta).toContainText('LogisPrime')

  await page
    .locator('article')
    .filter({ hasText: 'LogisPrime' })
    .getByRole('combobox')
    .selectOption('ganho')

  const colunaGanho = page
    .locator('section')
    .filter({ has: page.getByRole('heading', { name: 'Ganho' }) })
  await expect(colunaGanho).toContainText('LogisPrime')
  await expect(colunaGanho.locator('article').filter({ hasText: 'LogisPrime' }).getByRole('combobox')).toHaveCount(0)
})

test('concluir uma tarefa tira ela da lista em aberto', async ({ page }) => {
  await page.getByRole('link', { name: 'Tarefas', exact: true }).click()

  const tarefa = page.getByRole('listitem').filter({ hasText: 'Ligar para confirmar interesse' })
  await expect(tarefa).toBeVisible()
  await tarefa.getByRole('checkbox').click()

  await expect(
    page.getByRole('listitem').filter({ hasText: 'Ligar para confirmar interesse' })
  ).toHaveCount(0)

  await page.getByRole('button', { name: 'Concluídas' }).click()
  await expect(
    page.getByRole('listitem').filter({ hasText: 'Ligar para confirmar interesse' })
  ).toBeVisible()
})

test('a equipe mostra o alcance de cada perfil', async ({ page }) => {
  await page.getByRole('link', { name: 'Equipe', exact: true }).click()

  await expect(page.getByRole('cell', { name: 'Helena Prado' })).toBeVisible()
  await expect(page.getByText('Desativado')).toBeVisible()
})

test('sair limpa a sessão e volta para a entrada', async ({ page }) => {
  await page.getByRole('button', { name: /Visitante da demonstração|VD/ }).click()
  await page.getByRole('button', { name: 'Sair da conta' }).click()

  await expect(page).toHaveURL(/\/entrar$/)
  const token = await page.evaluate(() => localStorage.getItem('koracrm_token'))
  expect(token).toBeNull()
})
