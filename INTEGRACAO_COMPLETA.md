# 🎯 INTEGRAÇÃO COMPLETA: Locket - API PHP + Inteligência Python

## 📚 Estrutura Geral

```
locket/
├── DATABASE/                    # Scripts de criação do banco
│   ├── Locket.sql              # Tabelas principais
│   ├── views.sql               # Views seguras
│   ├── functions.sql           # Funções MySQL
│   └── users.sql               # Usuários e permissões
│
├── control/                    # API REST em PHP
│   ├── index.php              # Roteamento principal
│   ├── AuthController.php     # Login/Registro
│   ├── EmprestimoController.php # Gestão de empréstimos
│   ├── UsuarioController.php   # Gestão de usuários
│   ├── ObjetoController.php    # Gestão de objetos
│   └── ...
│
├── python/                    # ⭐ NOVO: Inteligência em Python
│   ├── main.py               # Ponto de entrada
│   ├── config_db.py          # Conexão MySQL
│   ├── loan_analyzer.py      # Análise de dados
│   ├── delay_predictor.py    # Modelo ML
│   ├── report_generator.py   # Geração relatórios
│   ├── alert_service.py      # Sistema de alertas
│   ├── requirements.txt      # Dependências
│   ├── README.md             # Documentação Python
│   ├── install.bat           # Instalador Windows
│   └── install.sh            # Instalador Linux/Mac
│
├── GUIA_RAPIDO.md           # ⭐ Guia de início rápido
├── README.md                # Documentação geral
└── .env.example             # Variáveis de ambiente
```

## 🔄 Fluxo de Dados

```
┌─────────────────────────────────────────────────────────────────┐
│                        USUÁRIOS / FRONTEND                      │
└──────────────────────┬──────────────────────────────────────────┘
                       │ HTTP REST API
                       ↓
┌──────────────────────────────────────────────────────────────────┐
│              API REST (control/index.php - PHP)                 │
│  • Autenticação com JWT                                         │
│  • CRUD de Empréstimos, Usuários, Objetos                      │
│  • Validação e Segurança (prepared statements)                 │
└──────────────────────┬──────────────────────────────────────────┘
                       │ MySQL Query
                       ↓
┌──────────────────────────────────────────────────────────────────┐
│            BANCO DE DADOS (MySQL via XAMPP)                    │
│  • usuario, emprestimo, objeto, instituicao                     │
│  • Views de segurança                                           │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ├─── Leitura direta (Python)
                       ↓
┌──────────────────────────────────────────────────────────────────┐
│      SISTEMA DE INTELIGÊNCIA (python/ - Python)                │
│  • Análise de dados com Pandas                                  │
│  • Machine Learning (Scikit-learn)                              │
│  • Geração de relatórios (JSON/CSV)                            │
│  • Sistema de alertas automáticos                               │
└──────────────────────┬──────────────────────────────────────────┘
                       │
         ┌─────────────┼─────────────┐
         ↓             ↓             ↓
    Relatórios    Alertas      Previsões
   (reports/)   (alerts.json) (JSON)
```

## 🚀 Como Começar

### 1️⃣ Setup Inicial (Uma única vez)

#### A. Banco de Dados
```bash
# No MySQL (via XAMPP)
mysql -u root < Database/Locket.sql
mysql -u root locket_db < Database/views.sql
mysql -u root locket_db < Database/functions.sql
mysql -u root < Database/users.sql
```

#### B. API PHP
```bash
# Copiar .env e configurar (já funciona com XAMPP)
cp .env.example .env
```

#### C. Sistema Python (⭐ NOVO)
```bash
# Abrir terminal/prompt na pasta python/
cd python

# Windows
install.bat

# Linux/Mac
./install.sh
```

### 2️⃣ Testar Tudo

```bash
# Testar API
curl http://localhost/locket/control/index.php/health

# Testar Python
python test_system.py
```

## 📊 Usando a Inteligência

### Análise Simples
```bash
cd python
python main.py relatorio
```

Resultado: Mostra resumo de empréstimos, atrasos e usuários

### Ver Alertas
```bash
python main.py alertas
```

Resultado: Lista alertas organizados por prioridade (CRÍTICO → REINCIDENTE)

### Análise Completa
```bash
python main.py completo
```

Resultado:
- ✓ 3 relatórios gerados (JSON, CSV, Atrasos)
- ✓ Alertas processados
- ✓ Resumo final exibido

### Treinar IA
```bash
python main.py treinar
```

Resultado: Modelo Random Forest salvo em `models/delay_model.pkl`

## 🎯 Funcionalidades por Camada

### CAMADA 1: API PHP (Gerenciamento)
- ✅ Autenticação com JWT
- ✅ CRUD de empréstimos
- ✅ Controle de acesso por função
- ✅ Validação de dados
- ✅ Proteção contra SQL Injection

### CAMADA 2: Banco MySQL (Dados)
- ✅ Schema normalizado
- ✅ Views para segurança
- ✅ Índices para performance
- ✅ Funções auxiliares

### CAMADA 3: Python (Inteligência) ⭐
- ✅ Análise de dados com Pandas
- ✅ Machine Learning (previsões)
- ✅ Sistema de alertas automáticos
- ✅ Geração de relatórios (JSON/CSV)
- ✅ Integração com XAMPP

## 📈 Exemplo: Fluxo Completo

```
1. Usuário faz login via API
   POST /auth/login
   ↓
2. Usuário pega empréstimo
   POST /emprestimos
   ↓
3. Dados armazenados em MySQL
   ↓
4. Sistema Python executa análise (diário)
   python main.py completo
   ↓
5. Alertas gerados
   └─ Email: "João tem atraso de 25 dias"
   └─ JSON: reports/alertas_20260531_090000.json
   └─ CSV: reports/relatorio_detalhado_20260531_090000.csv
   ↓
6. IA prevê próximos atrasos
   └─ Maria: 78% probabilidade de atraso
   ↓
7. Admin recebe dashboard com insights
   └─ "5 empréstimos com risco crítico"
```

## ⚙️ Configuração Avançada

### Agendar Análises Automáticas

**Windows (Agendador de Tarefas)**
```
Novo Programa/Script
→ C:\Python\python.exe
→ Argumentos: D:\locket\python\main.py completo
→ Executar às 09:00 todo dia
```

**Linux (cron)**
```bash
crontab -e
# Adicionar:
0 9 * * * cd /home/user/locket/python && python3 main.py completo
```

### Enviar Relatórios por Email (Futura)

```python
# Em alert_service.py (próxima versão)
import smtplib
def send_email_alerts(recipients):
    # Ler alerts.json
    # Enviar para cada admin
```

### Publicar Resultados em Dashboard (Futura)

```python
# Em report_generator.py (próxima versão)
from flask import Flask, jsonify
@app.route('/api/relatorios')
def get_reports():
    return jsonify(reports)
```

## 🔒 Segurança

### API PHP
- ✅ JWT com expiração
- ✅ Prepared statements (anti SQL injection)
- ✅ Validação de entrada
- ✅ Senhas com bcrypt
- ✅ RBAC (Role-Based Access Control)

### Python
- ✅ Credenciais em .env (não no código)
- ✅ Conexão segura com MySQL
- ✅ Sem dados sensíveis em logs
- ✅ Modelos ML salvos localmente

## 📊 Dados Exportados

Os relatórios ficam em `python/reports/`:

```
reports/
├── relatorio_20260531_093000.json       # Análise completa (JSON)
├── relatorio_detalhado_20260531_093000.csv # Todos empréstimos (CSV)
├── atrasos_20260531_093000.json         # Apenas atrasados (JSON)
├── previsoes_20260531_093000.json       # Previsões ML (JSON)
└── alerts.json                           # Alertas (JSON)
```

Ideal para:
- 📊 Importar em Excel/Power BI
- 📧 Enviar por email
- 📱 Publicar em dashboard
- 📈 Análises gerenciais

## 🤖 Machine Learning: O que Você Ganha

### Antes (sem IA)
```
Admin: "Há 5 empréstimos atrasados"
Admin: "Preciso chamar os alunos"
```

### Depois (com IA)
```
Sistema: "5 empréstimos atrasados (2 CRÍTICOS)"
Sistema: "João: 85% chance de atrasar (previsto)"
Sistema: "Maria: padrão de reincidente (histórico)"
Admin: "Priorizo João e Maria"
```

## 🚀 Próximas Versões

### v2.0 (Médio Prazo)
- [ ] Dashboard web com gráficos
- [ ] Exportação para PDF
- [ ] Notificações por email
- [ ] API REST para chamar análises
- [ ] Webhooks para eventos

### v3.0 (Longo Prazo)
- [ ] Análise de sazonalidade
- [ ] Recomendações automáticas
- [ ] Integração com sistema de multas
- [ ] Análise de comportamento por turma
- [ ] Relatório gerencial automático

## 🎓 Aprendizado

Este projeto demonstra:
1. **Full Stack**: PHP + Python + MySQL
2. **API REST**: Segurança, autenticação, validação
3. **Data Science**: Pandas, análise, visualização
4. **Machine Learning**: Random Forest, previsões
5. **DevOps**: Scripts de instalação, configuração

## ❓ Troubleshooting

### "Erro ao conectar ao banco"
```bash
# Verificar:
1. XAMPP está rodando? (Apache + MySQL)
2. Banco locket_db foi criado?
3. Credenciais em .env estão corretas?
```

### "Módulo não encontrado"
```bash
cd python
pip install -r requirements.txt
```

### "Sem dados para análise"
```bash
# Precisa de empréstimos no banco:
# 1. Criar usuários
# 2. Criar objetos
# 3. Criar empréstimos via API
```

## 📞 Suporte

- 📖 Veja `GUIA_RAPIDO.md` para início rápido
- 📚 Veja `python/README.md` para documentação Python
- 📝 Veja `python/EXEMPLOS.md` para exemplos de código
- 🔍 Execute `python test_system.py` para diagnosticar

---

## ✨ Resumo

Você tem agora um **sistema inteligente de análise de empréstimos** que:

1. **Gerencia** empréstimos via API REST
2. **Armazena** dados em MySQL (XAMPP)
3. **Analisa** automaticamente com Python
4. **Prevê** atrasos com Machine Learning
5. **Alerta** sobre casos críticos
6. **Exporta** relatórios para análise

**Tudo integrado. Tudo pronto. 🎉**

Execute: `cd python && python main.py completo`
