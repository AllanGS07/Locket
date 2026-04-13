# Locket - API de Gerenciamento de Empréstimos

API REST em Vanilla PHP para gerenciamento de empréstimos de objetos em instituições de ensino.

## 🔒 Arquitetura de Segurança

### Proteção contra SQL Injection
- Todas as consultas usam **prepared statements** (bind_param)
- Inputs são sanitizados e validados

### Autenticação
- JWT (JSON Web Tokens) com algoritmo HMAC HS256
- Token expira em 1 hora (configurável)
- Middleware verifica autenticação em todas as rotas protegidas

### Autorização
- Controle de acesso baseado em função (RBAC)
- Alunos acessam apenas seus dados
- Professores e Admin têm acesso expandido
- Views de banco de dados expõem apenas dados públicos

### Sanitização de Dados
- Validação de email com `filter_var()`
- CPF validado e formatado
- Senhas com requisitos fortes (8+ chars, maiúscula, minúscula, número, especial)
- Hashing com `password_hash()` (bcrypt)

### Views do Banco
- `vw_usuario_publico`: sem email, CPF ou senha
- `vw_objetos_publicos`: apenas objetos disponíveis
- `vw_detalhes_emprestimos`: sem dados sensíveis
- `vw_emprestimos_atrasados`: restrito a admins

## 📋 Pré-requisitos

- PHP 7.4+
- MySQL 5.7+
- curl (para testar API)

## 🚀 Instalação

### 1. Preparar o Banco de Dados

```bash
# Criar banco de dados e tabelas
mysql -u root -p < database/Locket.sql

# Criar views
mysql -u root -p locket_db < database/views.sql

# Criar funções
mysql -u root -p locket_db < database/functions.sql

# Criar usuários com permissões (segurança)
mysql -u root -p < database/users.sql
```

### 2. Configurar Variáveis de Ambiente

```bash
cp .env.example .env
# Edite .env com suas configurações
```

### 3. Estrutura de Pastas

```
locket/
├── database/
│   ├── Locket.sql          # Criação de banco e tabelas
│   ├── views.sql           # Views para segurança
│   ├── functions.sql       # Funções MySQL
│   └── users.sql           # Usuários e permissões
├── control/
│   ├── index.php           # Roteamento principal
│   ├── DatabaseConfig.php  # Configuração do DB
│   ├── ApiResponse.php     # Respostas padrão
│   ├── InputValidator.php  # Validação de entrada
│   ├── JwtAuth.php         # Autenticação com JWT
│   ├── AuthMiddleware.php  # Middleware de autenticação
│   ├── AuthController.php  # Login e registro
│   ├── UsuarioController.php
│   ├── ObjetoController.php
│   └── EmprestimoController.php
├── model/
├── view/
├── .env.example
└── README.md
```

## 🔑 Endpoints da API

### Autenticação

#### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "usuario@email.com",
    "password": "SenhaForte@123"
}

Response:
{
    "success": true,
    "message": "Login realizado com sucesso",
    "data": {
        "token": "eyJhbGc...",
        "user_id": 1,
        "funcao": "ALUNO"
    }
}
```

#### Registrar
```http
POST /auth/register
Content-Type: application/json

{
    "email": "novo@email.com",
    "password": "SenhaForte@123",
    "nome": "João Silva",
    "cpf": "12345678901",
    "matricula": "2024001",
    "data_nascimento": "2005-01-15",
    "id_instituicao": 1,
    "funcao": "ALUNO"
}
```

### Usuários

#### Listar
```http
GET /usuarios
Authorization: Bearer {token}
```

#### Obter
```http
GET /usuarios/1
Authorization: Bearer {token}
```

#### Atualizar
```http
PUT /usuarios/1
Authorization: Bearer {token}
Content-Type: application/json

{
    "nome": "Novo Nome"
}
```

### Objetos

#### Listar
```http
GET /objetos
Authorization: Bearer {token}
```

#### Obter
```http
GET /objetos/1
Authorization: Bearer {token}
```

#### Criar (apenas TECNICO_ADMINISTRATIVO)
```http
POST /objetos
Authorization: Bearer {token}
Content-Type: application/json

{
    "numero_tombamento": 1001,
    "nome": "Notebook",
    "marca": "Dell",
    "modelo": "Latitude",
    "numero_serie": "ABC123XYZ",
    "id_instituicao": 1
}
```

### Empréstimos

#### Listar
```http
GET /emprestimos?usuario_id=1
Authorization: Bearer {token}
```

#### Criar
```http
POST /emprestimos
Authorization: Bearer {token}
Content-Type: application/json

{
    "id_objeto": 5,
    "data_retirada": "2024-01-15",
    "data_devolucao_prevista": "2024-01-22"
}
```

#### Devolver
```http
PUT /emprestimos/1/devolver
Authorization: Bearer {token}
```

## 🛡️ Medidas de Segurança Implementadas

1. **Prepared Statements**: Todas as queries usam bind_param
2. **Validação de Entrada**: Sanitização e validação em InputValidator
3. **Senhas Fortes**: Requisitos mínimos de complexidade + bcrypt
4. **JWT**: Autenticação stateless com expiração
5. **Controle de Acesso**: Middleware e verificação por função
6. **Views**: Banco de dados expõe apenas dados públicos
7. **CORS**: Headers configurados para segurança
8. **SQL Injection**: Prevenido com prepared statements
9. **Logs**: Erros logados, não exibidos ao cliente
10. **Variáveis de Ambiente**: Credenciais não no código

## 📝 Códigos de Status HTTP

- `200`: Sucesso (GET, PUT)
- `201`: Criado com sucesso (POST)
- `400`: Erro de validação
- `401`: Não autenticado
- `403`: Acesso proibido
- `404`: Recurso não encontrado
- `405`: Método não permitido
- `500`: Erro interno do servidor

## 🧪 Testar Localmente

```bash
# Verificar saúde da API
curl http://localhost/locket/control/index.php/health

# Registrar usuário
curl -X POST http://localhost/locket/control/index.php/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email":"user@test.com",
    "password":"Test@1234",
    "nome":"Test User",
    "cpf":"12345678901",
    "matricula":"2024001",
    "data_nascimento":"2005-01-15",
    "id_instituicao":1,
    "funcao":"ALUNO"
  }'

# Fazer login
curl -X POST http://localhost/locket/control/index.php/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email":"user@test.com",
    "password":"Test@1234"
  }'

# Usar token obtido
curl http://localhost/locket/control/index.php/usuarios \
  -H "Authorization: Bearer {token}"
```

## 📄 Estrutura de Resposta

### Sucesso
```json
{
    "success": true,
    "message": "Descrição da operação",
    "data": {},
    "timestamp": "2024-01-15T10:30:00+00:00"
}
```

### Erro
```json
{
    "success": false,
    "message": "Descrição do erro",
    "errors": ["campo1", "campo2"],
    "timestamp": "2024-01-15T10:30:00+00:00"
}
```

## 🔐 Boas Práticas para Produção

1. **HTTPS**: Sempre usar em produção
2. **Rate Limiting**: Implementar limite de requisições
3. **CORS**: Configurar domínios específicos
4. **Logs**: Manter histórico de acessos
5. **Backup**: Automatizar backups do banco
6. **Monitoramento**: Alertar sobre erros e acessos incomuns
7. **Secrets**: Usar gerenciador de secrets para JWT_SECRET