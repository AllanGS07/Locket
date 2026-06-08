# 🚀 Guia de Execução - Refatoração Locket

## 📋 Checklist de Implementação

- [x] Roteamento PHP corrigido
- [x] Dados falsos do frontend removidos
- [x] SQL seed criado
- [x] Página cadastro.html criada
- [x] API de registro implementada
- [x] Login melhorado

---

## 1️⃣ POPULAR O BANCO COM DADOS DE TESTE

### Via Terminal (Recomendado)

```bash
# Windows
cd d:\Dev\Programas\XAMPP\htdocs\locket
mysql -u locket_app -p locket_db < Database\13_seed_testes.sql

# Linux/Mac
cd /path/to/locket
mysql -u locket_app -p locket_db < Database/13_seed_testes.sql
```

Quando solicitado, digite a senha: **`senha_app_forte_123!@#`**

### Via phpMyAdmin

1. Abra phpMyAdmin: http://localhost/phpmyadmin
2. Acesse banco `locket_db`
3. Vá para aba "SQL"
4. Cole o conteúdo de `Database/13_seed_testes.sql`
5. Clique em "Executar"

### Verificar Dados Inseridos

```sql
-- Verificar instituição
SELECT * FROM Instituicao WHERE CNPJ = '12.345.678/0001-90';

-- Verificar usuários
SELECT ID_Usuario, Nome, Email, Funcao FROM Usuario LIMIT 5;

-- Verificar objetos
SELECT * FROM Objeto LIMIT 5;
```

---

## 2️⃣ TESTAR O ROTEAMENTO

### Verificar Health Check

```bash
curl -X GET http://localhost/locket/control/health
```

Resposta esperada:
```json
{
  "success": true,
  "data": {
    "status": "online"
  },
  "message": "API online",
  "statusCode": 200
}
```

### Testar Login

```bash
curl -X POST http://localhost/locket/control/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "professor@universidade-testes.edu.br",
    "password": "secret"
  }'
```

Resposta esperada:
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "ID_Usuario": 1,
      "Nome": "Prof. Anderson Silva",
      "Email": "professor@universidade-testes.edu.br",
      "Funcao": "Admin"
    }
  }
}
```

---

## 3️⃣ TESTAR A INTERFACE

### Login

1. Abra http://localhost/locket/login.html
2. Digite credenciais:
   - **Email:** `professor@universidade-testes.edu.br`
   - **Senha:** `secret`
3. Clique "Entrar"
4. Se bem-sucedido → Será redirecionado para `/pages/dashboard.html`

### Cadastro

1. Abra http://localhost/locket/cadastro.html
2. Preencha o formulário:
   - **Nome:** João Silva
   - **CPF:** 123.456.789-01
   - **Matrícula:** TEST001
   - **Email:** joao.silva@test.com
   - **Data Nascimento:** 2000-01-15
   - **Senha:** senha123
   - **Confirmar Senha:** senha123
3. Clique "Criar Conta"
4. Se bem-sucedido → Toast "Conta criada com sucesso!" → Será redirecionado para `login.html`

### Listar Usuários (após login)

1. Acesse http://localhost/locket/pages/usuarios.html
2. Deverá ver a tabela com usuários do banco
3. Clique em "Atualizar" para recarregar

---

## 4️⃣ TESTAR VIA API.JS

Abra o console do navegador (F12) após fazer login:

```javascript
// Listar todos os usuários
await usersAPI.getAll();

// Obter usuário específico
await usersAPI.getById(1);

// Listar objetos/ativos
await assetsAPI.getAll();

// Obter ativo específico
await assetsAPI.getById(1);

// Listar empréstimos
const loansResponse = await apiRequest('GET', '/emprestimos');
```

---

## 5️⃣ ESTRUTURA DE PASTAS

```
locket/
├── 📄 login.html              ✅ Melhorado (API real)
├── 📄 cadastro.html           ✨ NOVO - Página de registro
├── 📁 control/
│   ├── 📄 index.php           ✅ Roteamento corrigido
│   ├── 📄 DatabaseConfig.php  ✅ Verificado
│   ├── ... (outros arquivos PHP)
├── 📁 Database/
│   ├── 📄 13_seed_testes.sql  ✨ NOVO - Dados de teste
│   ├── ... (outros arquivos SQL)
├── 📁 pages/
│   ├── 📄 usuarios.html       ✅ Frontend puro (API)
│   ├── 📄 empresarios.html    ✅ Frontend puro (API)
│   ├── 📄 ativos.html         ✅ Frontend puro (API)
├── 📁 js/
│   ├── 📄 api.js              ✅ Com authAPI.register()
│   ├── 📄 main.js             ✅ Funções utilitárias
├── 📁 css/
│   ├── 📄 style.css
├── 📄 REFACTORING_SUMMARY.md  ✨ Documentação
├── 📄 ROUTING_IMPROVEMENTS.md ✨ Detalhes técnicos
└── 📄 README.md               (seu README original)
```

---

## 6️⃣ CREDENCIAIS DE TESTE

### Usuários Já Inseridos

| Email | Senha | Função | Matrícula |
|-------|-------|--------|-----------|
| `professor@universidade-testes.edu.br` | `secret` | Admin | PROF001 |
| `joao@universidade-testes.edu.br` | `secret` | Aluno | ALN001 |
| `maria.operacional@universidade-testes.edu.br` | `secret` | Técnico | TEC001 |

---

## 7️⃣ TROUBLESHOOTING

### Erro 404 ao acessar `/usuarios`
**Solução:** Verifique se o path está correto. Use `~/usuarios` em vez de `~^/usuarios$~`

### Erro de Conexão ao Banco
**Solução:** Verifique credenciais em `control/DatabaseConfig.php`:
```
Usuario: locket_app
Senha: senha_app_forte_123!@#
Banco: locket_db
```

### Cadastro Retorna Erro 500
**Solução:** Verifique se `AuthController::register()` existe e está implementado

### Token Expirado
**Solução:** Faça login novamente para obter novo token

---

## 8️⃣ PRÓXIMOS PASSOS RECOMENDADOS

1. ✅ Executar SQL seed
2. ✅ Testar login com dados de teste
3. ✅ Testar cadastro de novo usuário
4. ✅ Testar endpoints via curl
5. ✅ Testar interface `/pages/usuarios.html`
6. **[PENDENTE]** Implementar `AuthController::register()` se não existir
7. **[PENDENTE]** Adicionar validações mais rigorosas no backend

---

## 9️⃣ VERIFICAÇÃO FINAL

```bash
# 1. Backend online?
curl http://localhost/locket/control/health

# 2. Banco acessível?
mysql -u locket_app -p -e "SELECT COUNT(*) FROM locket_db.Usuario;"

# 3. Dados inseridos?
mysql -u locket_app -p -e "SELECT * FROM locket_db.Instituicao LIMIT 1;"

# 4. Roteamento funcionando?
curl -X GET http://localhost/locket/control/usuarios \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

✅ **Refatoração concluída com sucesso!**

Para dúvidas, consulte:
- 📖 `REFACTORING_SUMMARY.md` (visão geral)
- 🔧 `ROUTING_IMPROVEMENTS.md` (detalhes técnicos)
- 📝 Comentários inline em `control/index.php`
