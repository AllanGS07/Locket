# 🚀 Guia Rápido - Sistema de Inteligência

## ⚡ Setup em 5 Minutos

### Windows
```bash
# 1. Abrir prompt de comando na pasta python/
cd python

# 2. Executar instalador
install.bat
```

### Linux/Mac
```bash
# 1. Abrir terminal na pasta python/
cd python

# 2. Dar permissão e executar
chmod +x install.sh
./install.sh
```

## 🎯 Comandos Básicos

```bash
# Ver relatório de empréstimos
python main.py relatorio

# Ver todos os alertas
python main.py alertas

# Treinar modelo de IA
python main.py treinar

# Fazer previsões de atrasos
python main.py previsoes

# Análise completa (tudo junto)
python main.py completo
```

## 📊 O que Você Terá

### Análise de Dados
- ✓ Total de empréstimos ativos/devolvidos/atrasados
- ✓ Usuários com maior histórico de atrasos
- ✓ Objetos mais emprestados
- ✓ Taxa média de atraso por usuário

### Sistema de Alertas
- ✓ **CRÍTICO**: Atrasos > 30 dias
- ✓ **ALTO**: Atrasos 14-30 dias
- ✓ **REINCIDENTE**: Usuários com padrão de atrasos

### Previsões com IA
- ✓ Modelo treina com histórico de empréstimos
- ✓ Prevê probabilidade de novo atraso
- ✓ Identifica usuários de risco

### Relatórios
- ✓ JSON para análise programada
- ✓ CSV para Excel/BI tools
- ✓ Alertas em arquivo separado

## 🔧 Configuração Inicial

### 1. Editar `.env`
```bash
# Abrir arquivo .env (foi criado automaticamente)
# Verificar se está correto:

DB_HOST=localhost        # Ou seu servidor
DB_USER=root            # Usuário MySQL
DB_PASSWORD=            # Senha (vazio por padrão)
DB_PORT=3306            # Porta padrão MySQL
DB_NAME=locket_db       # Nome do banco
```

### 2. Testar Conexão
```bash
python test_system.py
```

Se tudo estiver ✓ verde, está pronto!

## 📈 Uso em Produção

### Executar Análises Regularmente

**Windows (Agendador de Tarefas)**
```bash
# Abrir Agendador de Tarefas
# Nova tarefa → Executar python main.py completo
# Agendar: Diário às 09:00
```

**Linux/Mac (cron)**
```bash
# Abrir crontab
crontab -e

# Adicionar linha (executar todo dia às 9h)
0 9 * * * cd /caminho/para/python && python3 main.py completo
```

### Salvar Histórico
Todos os relatórios são salvos em `reports/` com timestamp:
- `relatorio_20260531_093000.json`
- `atrasos_20260531_093000.json`
- `relatorio_detalhado_20260531_093000.csv`

## 🎨 Exemplo de Saída

```
============================================================
SISTEMA DE INTELIGÊNCIA - EMPRÉSTIMOS
============================================================

RESUMO GERAL
============================================================
total_emprestimos: 150
emprestimos_ativos: 42
emprestimos_devolvidos: 103
emprestimos_atrasados: 5

EMPRÉSTIMOS ATRASADOS (Top 5):
  - João Silva: 45 dias (Risco: Crítico)
  - Maria Santos: 22 dias (Risco: Alto)

USUÁRIOS COM MAIOR ATRASO:
  - João Silva: 1 atrasados
  - Pedro Costa: 1 atrasados

[CRÍTICO] João Silva deve devolver Notebook há 45 dias!
[ALTO] Maria Santos está com atraso de 22 dias
[REINCIDENTE] Pedro Costa tem 50% de atrasos históricos

============================================================
```

## 🤖 Machine Learning

O sistema usa **Random Forest** para prever atrasos baseado em:
- Histórico do usuário
- Duração típica de empréstimo
- Dia/mês da retirada
- Padrões de atraso

**Acurácia**: Típica 80-90% (depende dos dados)

Para melhorar precisão:
1. Tenha pelo menos 100 empréstimos históricos
2. Execute `python main.py treinar` periodicamente
3. Valide previsões conforme novo dados chegam

## 📚 Próximos Passos

1. **Dashboard Web** (futura versão)
   - Integrar com Flask para visualizações
   - Gráficos em tempo real

2. **API REST Complementar**
   - Chamar análises via endpoint
   - Webhooks para alertas automáticos

3. **Notificações por Email**
   - Alertar usuários sobre atrasos
   - Enviar relatórios ao admin

4. **Análises Avançadas**
   - Sazonalidade de atrasos
   - Previsão de demanda
   - Ranking de usuários

## ❓ FAQ

**P: Por que preciso de XAMPP?**
R: Para ter MySQL rodando localmente. O sistema lê os dados do banco.

**P: Quanto tempo leva para rodar?**
R: Geralmente 5-30 segundos, depende da quantidade de dados.

**P: Posso usar em produção?**
R: Sim, basta configurar as variáveis `.env` com seu servidor MySQL.

**P: Como faço backup dos relatórios?**
R: Copie a pasta `reports/` ou versione no Git.

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique `README.md` na pasta `python/`
2. Execute `test_system.py` para diagnosticar
3. Verifique se XAMPP está rodando
4. Verifique credenciais em `.env`

---

**Sistema pronto! 🎉**

Execute: `python main.py completo`
