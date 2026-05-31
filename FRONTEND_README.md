# LockedIn - Frontend (HTML/CSS/JavaScript)

Frontend responsivo desenvolvido com **HTML5 semântico**, **CSS3** e **JavaScript vanilla**, utilizando **Bootstrap 5** para componentes e layout.

## 📁 Estrutura de Pastas

```
frontend/
├── index.html                 # Página inicial
├── login.html                # Página de login
├── pages/                    # Páginas principais
│   ├── dashboard.html        # Painel de controle
│   ├── usuarios.html         # Listar usuários
│   ├── usuarios-form.html    # Formulário de usuário
│   ├── empresarios.html      # Listar empresários
│   ├── empresarios-form.html # Formulário de empresário
│   ├── ativos.html           # Listar ativos
│   ├── ativos-form.html      # Formulário de ativo
│   ├── auditoria.html        # Log de auditoria
│   ├── relatorios.html       # Geração de relatórios
│   └── perfil.html           # Perfil do usuário
├── css/
│   └── style.css             # Estilos customizados
├── js/
│   ├── main.js               # Funções principais
│   └── api.js                # Client de API
└── README.md                 # Este arquivo
```

## 🎨 Design e Layout

### Cores
- **Primária**: #0066CC (Azul)
- **Secundária**: #00CC99 (Verde)
- **Sucesso**: #28A745
- **Perigo**: #FF6B6B
- **Aviso**: #FFC107
- **Info**: #17A2B8

### Tags HTML Semânticas
- `<header>` - Barra de navegação superior
- `<nav>` - Navegação
- `<main>` - Conteúdo principal
- `<section>` - Seções de conteúdo
- `<article>` - Componentes independentes
- `<aside>` - Barras laterais
- `<footer>` - Rodapé
- `<fieldset>` - Agrupamento de formulários
- `<legend>` - Título de fieldsets

## 📱 Responsividade

O layout foi desenvolvido com **mobile-first** e **Bootstrap 5 Grid**:

### Breakpoints
- **< 576px**: Mobile (XS)
- **576px - 767px**: Tablet pequeno (SM)
- **768px - 991px**: Tablet (MD)
- **992px - 1199px**: Desktop (LG)
- **≥ 1200px**: Desktop grande (XL)
- **≥ 1400px**: Desktop extra grande (XXL)

### Exemplos de Responsividade
```html
<!-- Grid responsivo -->
<div class="col-12 col-md-6 col-lg-3">
    <!-- Conteúdo -->
</div>

<!-- Ocultar em mobile -->
<div class="d-none d-md-block">
    <!-- Visível em tablet+ -->
</div>

<!-- Menu hamburger automático -->
<button class="navbar-toggler" type="button" data-bs-toggle="collapse">
    Menu
</button>
```

## 🔐 Autenticação

### Login
- Página: `login.html`
- Token armazenado em `localStorage`
- Verificação automática em páginas protegidas
- Redirect para login se token expirado

### Dados de Teste
```
Email: admin@test.com
Senha: 123456
```

## 📡 API Integration

### Client API (`js/api.js`)

Módulos disponíveis:

#### Autenticação
```javascript
await authAPI.login(email, password)
await authAPI.logout()
await authAPI.refresh()
```

#### Usuários
```javascript
await usersAPI.getAll(page, limit)
await usersAPI.getById(id)
await usersAPI.create(data)
await usersAPI.update(id, data)
await usersAPI.delete(id)
await usersAPI.search(query)
```

#### Empresários
```javascript
await businessmenAPI.getAll(page, limit)
await businessmenAPI.getById(id)
await businessmenAPI.create(data)
await businessmenAPI.update(id, data)
await businessmenAPI.delete(id)
```

#### Ativos
```javascript
await assetsAPI.getAll(page, limit)
await assetsAPI.getById(id)
await assetsAPI.create(data)
await assetsAPI.update(id, data)
await assetsAPI.delete(id)
await assetsAPI.getByBusinessman(businessmanId)
```

#### Auditoria
```javascript
await auditAPI.getAll(page, limit)
await auditAPI.getFiltered(filters)
await auditAPI.getStats()
```

#### Relatórios
```javascript
await reportsAPI.generateLoans(format)
await reportsAPI.generateAssets(format)
await reportsAPI.generateUsers(format)
await reportsAPI.generateFinancial(format)
await reportsAPI.generateCustom(data)
```

### Configuração da API

Editar `js/api.js`:
```javascript
const API_BASE_URL = 'http://seu-servidor:porta/api';
```

## 🛠️ Funções Utilitárias

### Formatação
```javascript
formatCurrency(1000)        // R$ 1.000,00
formatDate('2026-05-31')    // 31/05/2026
formatDateTime(new Date())  // 31/05/2026 16:20:45
```

### Validação
```javascript
isValidEmail('user@example.com')
isValidCPF('123.456.789-00')
isValidCNPJ('12.345.678/0001-90')
isValidPhone('(11) 98765-4321')
```

### Máscara de Input
```javascript
<input data-mask="cpf" type="text">
<input data-mask="cnpj" type="text">
<input data-mask="phone" type="text">
<input data-mask="cep" type="text">
```

### Notificações
```javascript
showToast('Mensagem de sucesso', 'success')
showToast('Erro ao salvar', 'danger')
showToast('Aviso importante', 'warning')
```

### Upload de Arquivos
```javascript
await uploadFile('/upload', fileInput.files[0])
```

### Download de Arquivos
```javascript
downloadFile('/api/reports/pdf', 'relatorio.pdf')
```

## 📊 Páginas Principais

### Dashboard
- Métricas principais (usuários, empresários, ativos)
- Atividades recentes
- Ações rápidas
- Botões para criar novos registros

### Usuários
- Listagem com filtros e busca
- Criação e edição de usuários
- Validação de forma
- Exclusão com confirmação

### Empresários
- Cadastro completo com endereço
- Detalhes de contato
- Status (Ativo/Inativo/Suspenso)
- Histórico de empréstimos

### Ativos
- Grid responsivo com cards
- Avaliação e localização
- Número de série
- Ligação com empresários

### Auditoria
- Filtros por data e tipo de ação
- Estatísticas de operações
- Log completo de todas as ações
- Detalhes de cada operação

### Relatórios
- Geração de múltiplos tipos
- Formatos: PDF, Excel, CSV
- Relatórios recentes
- Gerador personalizado

### Perfil
- Editar informações pessoais
- Alterar senha
- Histórico de acessos
- Foto de perfil

## 🎯 Features Implementadas

✅ Layout semântico com tags HTML5
✅ Responsivo (Desktop, Tablet, Mobile)
✅ Dark mode pronto
✅ Bootstrap 5
✅ Autenticação com JWT
✅ CRUD operations
✅ Busca e filtros
✅ Validação de formulários
✅ Máscara de inputs
✅ Notificações
✅ Export CSV
✅ Print
✅ Upload/Download
✅ Paginação
✅ Tooltips e Popovers

## 🚀 Como Usar

### 1. Configurar API
```javascript
// js/api.js
const API_BASE_URL = 'http://localhost:3000/api';
```

### 2. Iniciar Servidor (Desenvolvimento)
```bash
# Python 3
python -m http.server 8000

# Node.js
npx http-server

# PHP
php -S localhost:8000
```

### 3. Acessar
```
http://localhost:8000/index.html
```

## 📝 Convenções

### Nomenclatura
- Arquivos: `snake-case.html`
- IDs: `camelCase`
- Classes: `kebab-case`
- Funções: `camelCase()`

### Estrutura de Formulário
```html
<fieldset>
    <legend>Título da Seção</legend>
    <!-- Campos do formulário -->
</fieldset>
```

### Cards
```html
<article class="col-12 col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0 fw-bold">Título</h6>
        </div>
        <div class="card-body">
            <!-- Conteúdo -->
        </div>
    </div>
</article>
```

## 🔗 Endpoints Esperados

### Autenticação
- `POST /api/login`
- `POST /api/refresh-token`

### Usuários
- `GET /api/users`
- `GET /api/users/:id`
- `POST /api/users`
- `PUT /api/users/:id`
- `DELETE /api/users/:id`

### Empresários
- `GET /api/businessmen`
- `GET /api/businessmen/:id`
- `POST /api/businessmen`
- `PUT /api/businessmen/:id`
- `DELETE /api/businessmen/:id`

### Ativos
- `GET /api/assets`
- `GET /api/assets/:id`
- `POST /api/assets`
- `PUT /api/assets/:id`
- `DELETE /api/assets/:id`

### Auditoria
- `GET /api/audit`
- `GET /api/audit/stats`

### Relatórios
- `GET /api/reports/:type?format=pdf`
- `POST /api/reports/custom`

## 📚 Bibliotecas

- **Bootstrap 5.3.0** - Framework CSS
- **Bootstrap Icons 1.11.0** - Ícones
- Sem dependências Node.js
- JavaScript vanilla puro

## 🎓 Aprendizados

Este projeto demonstra:
- HTML5 semântico
- CSS3 responsivo
- JavaScript moderno (async/await, fetch)
- Bootstrap 5
- RESTful API consumption
- Local storage
- Form validation
- UX/UI best practices

## 📱 Suporte

### Navegadores
- Chrome (88+)
- Firefox (87+)
- Safari (14+)
- Edge (88+)

### Dispositivos
- Desktop (1920px+)
- Laptop (1366px+)
- Tablet (768px+)
- Mobile (360px+)

## 📄 Licença

Desenvolvido por AllanGS07
Todos os direitos reservados © 2026 LockedIn

## 🤝 Contribuindo

Para contribuições, faça um fork do projeto e envie um pull request.

---

**Desenvolvido com ❤️ para LockedIn - Sistema de Empréstimos**
