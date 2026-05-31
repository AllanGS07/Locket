# Sistema de Inteligência - Análise de Empréstimos (Python)

## 📊 Sobre

Módulo de inteligência em Python que analisa dados de empréstimos gerenciados pela API REST em PHP. Utiliza **Machine Learning**, **análise de dados** e **geração de alertas** para insights acionáveis.

## 🎯 Funcionalidades

### 1. **Análise de Empréstimos** (`loan_analyzer.py`)
- Listar todos os empréstimos
- Identificar empréstimos atrasados
- Calcular estatísticas por usuário
- Objetos mais emprestados
- Gerar relatórios resumidos

### 2. **Previsão de Atrasos (ML)** (`delay_predictor.py`)
- Modelo Random Forest para prever atrasos
- Features: histórico do usuário, duração, dia/mês de retirada
- Salva modelo treinado para reutilização
- Fornece probabilidade de atraso

### 3. **Geração de Relatórios** (`report_generator.py`)
- Relatórios JSON com análises completas
- Exportação em CSV para análise posterior
- Relatórios específicos de atrasos
- Relatórios com previsões

### 4. **Sistema de Alertas** (`alert_service.py`)
- Alertas de atrasos críticos (>30 dias)
- Alertas de atrasos altos (14-30 dias)
- Identificação de usuários reincidentes
- Priorização automática

## 🚀 Instalação

### 1. Configurar Banco de Dados
Certifique-se que o XAMPP está rodando e o banco `locket_db` foi criado:

```bash
# No MySQL (via XAMPP)
mysql -u root < ../Database/Locket.sql
```

### 2. Instalar Python (3.8+)
Verifique se tem Python instalado:
```bash
python --version
```

### 3. Instalar Dependências
```bash
pip install -r requirements.txt
```

### 4. Configurar Variáveis de Ambiente
```bash
cp .env.example .env
# Edite .env com suas configurações do banco
```

## 📝 Como Usar

### Gerar Relatório Simples
```bash
python main.py relatorio
```

### Gerar Alertas
```bash
python main.py alertas
```

### Treinar Modelo de Previsão
```bash
python main.py treinar
```

### Gerar Previsões
```bash
python main.py previsoes
```

### Análise Completa
```bash
python main.py completo
```

## 📂 Estrutura de Arquivos

```
python/
├── config_db.py           # Conexão com MySQL
├── loan_analyzer.py       # Análise de empréstimos
├── delay_predictor.py     # Modelo de previsão
├── report_generator.py    # Geração de relatórios
├── alert_service.py       # Sistema de alertas
├── main.py               # Ponto de entrada
├── requirements.txt      # Dependências Python
├── .env.example         # Variáveis de ambiente
├── .gitignore           # Arquivos ignorados
├── models/              # Modelos ML treinados
├── reports/             # Relatórios gerados
└── README.md            # Este arquivo
```

## 🔌 Integração com XAMPP

O sistema conecta ao MySQL via XAMPP na porta **3306** (padrão).

**Configurações esperadas:**
- Host: `localhost`
- User: `root`
- Senha: (em branco, por padrão)
- Database: `locket_db`

Edite `.env` para alterar essas configurações.

## 📊 Exemplos de Saída

### Relatório JSON
```json
{
  "data_geracao": "2026-05-31T16:35:00",
  "resumo_geral": {
    "total_emprestimos": 150,
    "emprestimos_ativos": 45,
    "emprestimos_devolvidos": 105,
    "emprestimos_atrasados": 8
  },
  "emprestimos_atrasados": [
    {
      "id_emprestimo": 42,
      "nome": "João Silva",
      "dias_atraso": 45,
      "nivel_risco": "Crítico"
    }
  ]
}
```

### Alertas
```
[CRÍTICO] João Silva deve devolver Notebook há 45 dias!
[ALTO] Maria Santos está com atraso de 20 dias
[REINCIDENTE] Pedro Costa tem 60% de atrasos históricos
```

## 🔐 Segurança

- ✓ Credenciais via variáveis de ambiente
- ✓ Conexão segura com banco
- ✓ Sem dados sensíveis em logs
- ✓ Modelos ML salvos localmente

## 🧠 Machine Learning

### Dataset
- Mínimo 100 empréstimos históricos
- Features: usuário, duração, data, histórico

### Modelo
- **Algoritmo**: Random Forest (100 árvores)
- **Acurácia**: Depende dos dados (tipicamente 75-90%)
- **Features Importância**: Histórico de atrasos (1º), Duração (2º), Usuário (3º)

### Retrainamento
Retreine o modelo periodicamente conforme novos dados:
```bash
python main.py treinar
```

## 📈 Próximas Funcionalidades

- [ ] Dashboard web (Flask + Vue.js)
- [ ] Exportação em PDF
- [ ] Notificações por email
- [ ] API REST para consultas
- [ ] Análise de sazonalidade
- [ ] Previsão de demanda
- [ ] Ranking de usuários
- [ ] Sugestões de políticas

## 🤝 Integração com API PHP

A API PHP em `/control/` fornece os dados. O sistema Python os consome diretamente do banco:

```
PHP API → MySQL ← Python Inteligência
```

Para maior integração, considere:
1. Endpoints na API para chamar análises
2. Webhook para alertas automáticos
3. Dashboard integrando ambos

## ⚙️ Troubleshooting

### Erro: "Connection refused"
- Verifique se XAMPP está rodando
- Verifique porta MySQL (3306)

### Erro: "Table doesn't exist"
- Execute scripts de criação do banco em `../Database/`

### Modelo não encontrado
- Execute `python main.py treinar` para criar modelo

## 📞 Suporte

Para dúvidas ou sugestões, consulte a documentação da API em `../README.md`
