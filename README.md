# Locket - Sistema de Gerenciamento de Empréstimos

API REST em PHP com backend Python para análise inteligente de empréstimos de objetos em instituições de ensino.

## 🏗️ Arquitetura

```
Frontend (HTML/CSS/JS)
        ↓
REST API (PHP) - Controle de dados
        ↓
Análise Python (Flask) - Inteligência
        ↓
MySQL - Banco de dados
```

## 🔒 Arquitetura de Segurança

### Autenticação
- JWT (JSON Web Tokens) com algoritmo HMAC HS256
- Token expira em 1 hora (configurável)
- Middleware verifica autenticação em todas as rotas protegidas

### Autorização
- Controle de acesso baseado em função (RBAC)
- Alunos acessam apenas seus dados
- Professores e Admin têm acesso expandido

### Proteção contra SQL Injection
- Todas as consultas usam prepared statements
- Inputs sanitizados e validados

### Proteção de Senhas
- Requisitos fortes (8+ chars, maiúscula, minúscula, número, especial)
- Hashing com bcrypt

## 📋 Pré-requisitos

- PHP 7.4+
- MySQL 5.7+
- Python 3.8+
- Flask 2.0+
- pip (gerenciador de pacotes Python)

## 🚀 Instalação

### 1. Preparar o Banco de Dados

```bash
mysql -u root -p < database/Locket.sql
mysql -u root -p locket_db < database/views.sql
mysql -u root -p locket_db < database/functions.sql
mysql -u root -p < database/users.sql
```

### 2. Configurar Variáveis de Ambiente

```bash
cp .env.example .env
# Edite .env com suas configurações
```

### 3. Instalar dependências Python

```bash
cd python
pip install -r requirements.txt
```

### 4. Iniciar API Python

```bash
cd python
python main.py
```

A API Python estará disponível em `http://localhost:5000`

## 🔑 Endpoints da API Python

### Status
```http
GET /api/health
GET /api/status
```

### Empréstimos
```http
GET /api/loans
GET /api/loans/overdue
GET /api/loans/summary
```

### Usuários
```http
GET /api/users/statistics
```

### Itens
```http
GET /api/items/most-borrowed?limit=10
```

### Alertas
```http
GET /api/alerts
GET /api/alerts/critical
```

### Previsões
```http
POST /api/predictions/loan
POST /api/model/train
```

### Relatórios
```http
GET /api/reports/json
GET /api/reports/csv
GET /api/reports/predictions
GET /api/analysis/complete
```

## 🛠️ Estrutura de Arquivos

```
python/
├── api.py                  # API Flask com endpoints
├── main.py                 # Inicializador da aplicação
├── config_db.py            # Conexão com MySQL
├── loan_analyzer.py        # Análise de empréstimos
├── delay_predictor.py      # ML para previsão de atrasos
├── alert_service.py        # Gerador de alertas
├── report_generator.py     # Gerador de relatórios
├── utils.py                # Utilitários gerais
├── requirements.txt        # Dependências Python
└── models/
    └── delay_model.pkl     # Modelo ML treinado
```

## 📦 Dependências Python

- mysql-connector-python
- pandas
- scikit-learn
- joblib
- python-dotenv
- flask
- flask-cors

## 🧠 Recursos de IA

### Análise de Dados
- Identificação de padrões de atraso
- Estatísticas de uso por usuário e item
- Ranking de objetos mais emprestados

### Alertas Inteligentes
- Crítico: Atraso > 30 dias
- Alto: Atraso 14-30 dias
- Reincidente: Usuários com histórico de atrasos
- Priorização automática

### Previsão de Atrasos
- Modelo Random Forest treinado em dados históricos
- Features: usuário, duração, mês, dia da semana, histórico
- Probabilidade de atraso com nível de confiança

## 🔐 Boas Práticas para Produção

1. **HTTPS**: Sempre usar em produção
2. **Rate Limiting**: Implementar limite de requisições
3. **CORS**: Configurar domínios específicos
4. **Logs**: Manter histórico de acessos
5. **Backup**: Automatizar backups do banco
6. **Monitoramento**: Alertar sobre erros
7. **Secrets**: Usar gerenciador de secrets para JWT_SECRET
8. **Modelos**: Retreinar modelo ML regularmente