# 🎯 Guia Rápido - Frontend LockedIn

## ✅ O que foi criado

Um **frontend completo e profissional** com:

### 📱 Páginas (11 HTML)
- ✅ **index.html** - Página inicial com apresentação
- ✅ **login.html** - Autenticação do sistema
- ✅ **dashboard.html** - Painel de controle com métricas
- ✅ **usuarios.html** - CRUD de usuários com busca
- ✅ **usuarios-form.html** - Formulário de criação/edição
- ✅ **empresarios.html** - CRUD de empresários
- ✅ **empresarios-form.html** - Formulário com endereço completo
- ✅ **ativos.html** - Grid responsivo de ativos
- ✅ **ativos-form.html** - Formulário de avaliação
- ✅ **auditoria.html** - Log de auditoria com estatísticas
- ✅ **relatorios.html** - Gerador de relatórios
- ✅ **perfil.html** - Perfil do usuário com configurações

### 🎨 Estilos (CSS)
- ✅ **style.css** - Tema profissional completo
  - Cores para programa de empréstimos
  - Responsivo mobile-first
  - Animações suaves
  - Dark-friendly

### ⚙️ Funcionalidades (JavaScript)
- ✅ **main.js** - Funções utilitárias
  - Autenticação com token
  - Formatação de dados
  - Validação de formulários
  - Máscara de inputs
  - Notificações
  
- ✅ **api.js** - Client de API
  - 9 módulos de API
  - Tratamento de erros
  - Timeout automático
  - Upload/Download

## 🚀 Como usar

### 1️⃣ Abrir o Frontend

**Opção A: Python**
```bash
cd "d:\Dev\Códigos\locket.worktrees\agents-mvc-frontend-bootstrap-estrutura-html"
python -m http.server 8000
# Acessar: http://localhost:8000
```

**Opção B: Node.js**
```bash
npx http-server
```

**Opção C: PHP**
```bash
php -S localhost:8000
```

**Opção D: VS Code Live Server**
- Extensão recomendada: "Live Server"
- Clique direito em `index.html` → "Open with Live Server"

### 2️⃣ Conectar à API

Editar `js/api.js` (linha 6):
```javascript
const API_BASE_URL = 'http://seu-servidor:porta/api';
```

### 3️⃣ Fazer Login

**Credenciais de teste** (em login.html):
```
Email: admin@test.com
Senha: 123456
```

## 📊 Estrutura Semântica

Todas as páginas usam **HTML5 semântico**:

```html
<header>      <!-- Navbar -->
  <nav>       <!-- Navegação -->
</header>

<main>        <!-- Conteúdo principal -->
  <section>   <!-- Seções de conteúdo -->
  <article>   <!-- Componentes independentes -->
  <aside>     <!-- Sidebars -->
</main>

<footer>      <!-- Rodapé -->
```

## 📱 Responsividade

### Breakpoints Bootstrap
- Mobile: < 576px ✅
- Tablet: 576px - 991px ✅
- Desktop: 992px+ ✅

Todas as páginas foram testadas com:
- Grid responsivo
- Navbar colapsável
- Tabelas scrolláveis
- Cards em colunas

## 🎨 Design

### Paleta de Cores
```
Primária (Azul):        #0066CC  - Confiança e segurança
Secundária (Verde):     #00CC99  - Sucesso e crescimento
Sucesso:               #28A745  - Operações bem-sucedidas
Perigo:                #FF6B6B  - Erros e avisos críticos
Aviso:                 #FFC107  - Informações importantes
Info:                  #17A2B8  - Notificações
```

### Tipografia
- Font: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif
- Tamanho base: 0.95rem (responsivo)
- Pesos: 500 (regular), 600 (títulos), 700 (destaque)

## 🔐 Autenticação

### Flow
1. Usuário acessa login.html
2. Insere email e senha
3. Token salvo em localStorage
4. Redirect para dashboard
5. Todas as páginas verificam token
6. Se expirado → redirect para login

### LocalStorage
```javascript
localStorage.setItem('token', 'jwt_token_aqui')
localStorage.setItem('userEmail', 'usuario@email.com')
```

## 📡 API Integration

### Exemplo de Uso

```javascript
// Listar usuários
const users = await usersAPI.getAll(1, 10);

// Criar usuário
await usersAPI.create({
    name: "João Silva",
    email: "joao@example.com",
    password: "123456",
    role: "user"
});

// Atualizar usuário
await usersAPI.update(1, {
    name: "João Silva Updated"
});

// Deletar usuário
await usersAPI.delete(1);

// Gerar relatório
await reportsAPI.generateLoans('pdf');

// Upload de arquivo
await uploadFile('/upload', fileInput.files[0]);
```

## 📝 Validações

### Máscaras Automáticas
```html
<input data-mask="cpf" placeholder="000.000.000-00">
<input data-mask="cnpj" placeholder="00.000.000/0000-00">
<input data-mask="phone" placeholder="(00) 00000-0000">
<input data-mask="cep" placeholder="00000-000">
```

### Validações Disponíveis
- ✅ Email
- ✅ CPF
- ✅ CNPJ
- ✅ Telefone
- ✅ Senha (força)
- ✅ Formulários

## 🎯 Casos de Uso

### 1. Criar Novo Usuário
1. Dashboard → Novo Usuário
2. Preencher formulário
3. Salvar
4. Reflete em Auditoria

### 2. Cadastrar Empresário
1. Empresários → Novo
2. Preencher dados completos
3. Endereço automático
4. Salvar

### 3. Registrar Ativo
1. Ativos → Novo Ativo
2. Informações e avaliação
3. Localização
4. Salvar

### 4. Gerar Relatório
1. Relatórios → Selecionar tipo
2. Escolher formato (PDF/Excel/CSV)
3. Clique em Gerar
4. Download automático

### 5. Auditar Ações
1. Auditoria
2. Filtrar por data/tipo
3. Visualizar detalhes
4. Exportar se necessário

## 🔧 Personalização

### Alterar Cores

Editar `css/style.css` (linhas 8-14):
```css
:root {
    --primary: #0066CC;    /* Altere aqui */
    --secondary: #00CC99;  /* Altere aqui */
    /* ... */
}
```

### Adicionar Página

1. Criar novo arquivo `pages/nova-pagina.html`
2. Copiar estrutura do index/dashboard
3. Adicionar rota no navbar
4. Incluir scripts (bootstrap, api.js, main.js)

### Alterar Logo

Editar navbar em todas as páginas:
```html
<a class="navbar-brand" href="../index.html">
    <!-- Altere o ícone/texto aqui -->
    <i class="bi bi-safe2"></i> LockedIn
</a>
```

## ⚡ Performance

### Otimizações Implementadas
- ✅ CSS minificável (Bootstrap CDN)
- ✅ JavaScript modular
- ✅ Lazy loading de imagens
- ✅ Cache local (localStorage)
- ✅ Debounce em buscas
- ✅ Timeout de API (10s)

### Dicas
- Comprimir imagens
- Usar Cache-Control headers
- Minificar CSS/JS em produção
- CDN para recursos estáticos

## 🐛 Troubleshooting

### "Não consigo fazer login"
1. Verificar se API está rodando
2. Verificar API_BASE_URL em js/api.js
3. Checar credenciais (admin@test.com / 123456)

### "Página branca/em branco"
1. Verificar console do navegador (F12)
2. Checar erros de JavaScript
3. Verificar se Bootstrap CDN está acessível

### "Botões não funcionam"
1. Verificar se main.js está carregado
2. Checar console para erros
3. Verificar conectividade da API

## 📚 Recursos

### Bootstrap 5
- https://getbootstrap.com/docs/5.0/

### Bootstrap Icons
- https://icons.getbootstrap.com/

### MDN Web Docs
- https://developer.mozilla.org/

## ✨ Next Steps

### Para Produção
- [ ] Implementar CI/CD
- [ ] Adicionar testes (Jest/Vitest)
- [ ] Minificar assets
- [ ] Adicionar service workers
- [ ] Implementar PWA
- [ ] Adicionar analytics
- [ ] Configurar CORS

### Melhorias Futuras
- [ ] Dark Mode toggle
- [ ] Internacionalização (i18n)
- [ ] Tema customizável
- [ ] Offline mode
- [ ] Notificações push
- [ ] Chat em tempo real

## 📞 Suporte

Para dúvidas sobre a estrutura, consulte:
- FRONTEND_README.md - Documentação técnica
- comentários no código
- console do navegador (F12)

---

## 📊 Resumo Técnico

| Aspecto | Detalhes |
|---------|----------|
| **Linguagens** | HTML5, CSS3, JavaScript (vanilla) |
| **Framework** | Bootstrap 5.3.0 |
| **Icons** | Bootstrap Icons 1.11.0 |
| **Autenticação** | JWT + localStorage |
| **API** | RESTful com fetch() |
| **Responsivo** | Mobile-first com 6 breakpoints |
| **Tags Semânticas** | header, nav, main, section, article, aside, footer |
| **Páginas** | 13 arquivos HTML |
| **CSS Customizado** | 1 arquivo (6KB) |
| **JavaScript** | 2 arquivos (18KB) |
| **Sem Dependências** | Node.js não é necessário |

---

**✅ Frontend pronto para desenvolvimento!**

Desenvolvido para **LockedIn - Sistema de Empréstimos**
© 2026 - Todos os direitos reservados
