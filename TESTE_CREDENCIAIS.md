# 🔒 Locket - Credenciais de Teste

## 📋 Instruções de Acesso

### Endereço da Aplicação
- **URL Principal:** `http://localhost/locket/`
- **Página de Login:** `http://localhost/locket/login.html`
- **API Backend:** `http://localhost/locket/control/index.php`

---

## 👤 Contas de Teste Disponíveis

### 1️⃣ **Administrador / Professor**
- **Email:** `professor@universidade-testes.edu.br`
- **Senha:** `secret`
- **Perfil:** Professor (com acesso ao dashboard administrativo)
- **Instituição:** Universidade Federal de Testes
- **Acesso a:**
  - Dashboard com métricas gerais
  - Gerenciamento de usuários
  - Gerenciamento de ativos
  - Auditoria do sistema
  - Relatórios avançados

### 2️⃣ **Aluno**
- **Email:** `joao@universidade-testes.edu.br`
- **Senha:** `secret`
- **Perfil:** Aluno
- **Acesso a:**
  - Visualizar ativos disponíveis para empréstimo
  - Realizar empréstimos
  - Acompanhar devoluções

### 3️⃣ **Técnico Administrativo**
- **Email:** `maria.operacional@universidade-testes.edu.br`
- **Senha:** `secret`
- **Perfil:** Técnico Administrativo
- **Acesso a:**
  - Gerenciamento operacional de ativos
  - Controle de inventário
  - Processamento de devoluções

---

## 🚀 Como Usar

### Login Rápido (Administrador)
1. Abra `http://localhost/locket/login.html`
2. Na página de login, você verá um botão **"Usar Credenciais Admin"**
3. Clique no botão para pré-preencher automaticamente as credenciais
4. Clique em **"Entrar"**

### Login Manual
1. Preencha o email da conta desejada
2. Digite a senha: `secret`
3. Clique em **"Entrar"**

---

## 📊 Dashboard Administrativo (Professor)

Após fazer login com a conta de professor, você terá acesso a:

### Seções Principais

#### 🏠 Dashboard
- Visualizar estatísticas gerais do sistema
- Total de usuários, empresários, ativos
- Ações recentes
- Empréstimos ativos e atrasados

#### 👥 Usuários
- Listar todos os usuários do sistema
- Criar novos usuários
- Editar dados de usuários
- Ativar/desativar usuários

#### 📦 Ativos
- Listar todos os ativos disponíveis
- Criar novos ativos
- Editar informações de ativos
- Visualizar status (Disponível, Emprestado, Manutenção, Descartado)

#### 🏢 Empresários
- Gerenciar empresas/instituições parceiras
- Dados cadastrais completos

#### 📋 Auditoria
- Visualizar log de todas as ações do sistema
- Rastrear mudanças e acessos
- Data e hora de cada ação
- Usuário responsável

#### 📈 Relatórios
- Gerar relatórios avançados
- Análises de empréstimos
- Relatórios de atrasos
- Exportar dados

---

## 🔧 Dados Inseridos

### Instituição
- **Nome:** Universidade Federal de Testes
- **CNPJ:** 12.345.678/0001-90
- **e-MEC/INEP:** MEC2026TEST001

### Ativos de Exemplo
1. **Notebook Dell Inspiron**
   - Tombamento: 1001
   - Série: DELL-NOTB-001-2026
   - Status: DISPONIVEL

2. **Projetor BenQ**
   - Tombamento: 1002
   - Série: BENQ-PROJ-002-2026
   - Status: DISPONIVEL

3. **Câmera Canon EOS**
   - Tombamento: 1003
   - Série: CANON-CAM-003-2026
   - Status: DISPONIVEL

---

## 🔄 Funcionalidades Principais

### Para Alunos
- ✅ Visualizar ativos disponíveis
- ✅ Requisitar empréstimo de ativos
- ✅ Acompanhar devoluções
- ✅ Ver histórico de empréstimos
- ✅ Gerenciar dependentes

### Para Técnicos
- ✅ Processar empréstimos
- ✅ Registrar devoluções
- ✅ Controlar inventário
- ✅ Gerar relatórios operacionais

### Para Administradores (Professor)
- ✅ Todas as funcionalidades acima
- ✅ Gerenciamento completo de usuários
- ✅ Auditoria do sistema
- ✅ Relatórios avançados
- ✅ Configurações globais

---

## 🔐 Segurança

- Senhas são armazenadas com hash bcrypt
- Autenticação via JWT (JSON Web Tokens)
- Middlewares de autenticação em todas as rotas protegidas
- Validação de entrada em todos os formulários

---

## 🐛 Suporte

Caso tenha dúvidas ou encontre problemas, entre em contato com o administrador do sistema.

**Última atualização:** 2026-06-08

---

*Locket - Sistema de Gerenciamento de Empréstimos de Ativos*
