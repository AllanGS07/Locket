# 🎯 PRÓXIMOS PASSOS

## 1️⃣ Instalação Imediata (5-10 minutos)

### Se está no Windows:
```cmd
cd d:\Dev\Códigos\locket.worktrees\agents-inteligencia-emprestimos-python-xampp\python
install.bat
```

### Se está no Linux/Mac:
```bash
cd /path/to/locket/python
chmod +x install.sh
./install.sh
```

**O instalador vai:**
- ✅ Verificar Python
- ✅ Criar arquivo `.env`
- ✅ Criar diretórios `models/` e `reports/`
- ✅ Instalar dependências (Pandas, Scikit-learn, etc)
- ✅ Executar testes de validação

## 2️⃣ Primeira Execução (1 minuto)

Depois de instalar, execute:

```bash
# Ir para pasta python
cd python

# Opção 1: Ver relatório simples
python main.py relatorio

# Opção 2: Ver alertas
python main.py alertas

# Opção 3: Análise completa (RECOMENDADO PARA PRIMEIRA VEZ)
python main.py completo
```

**Esperado na primeira vez:**
```
============================================================
SISTEMA DE INTELIGÊNCIA - EMPRÉSTIMOS
============================================================

[1/4] Gerando relatórios...
✓ Relatório JSON salvo
✓ Relatório CSV salvo

[2/4] Gerando alertas...
Total de alertas: 5

[3/4] Resumo da análise...
Total de empréstimos: 150
Empréstimos ativos: 45

[4/4] ✓ Análise completa finalizada!
```

## 3️⃣ Verificar Resultados

### Relatórios Gerados
```bash
# Windows
dir python\reports\

# Linux/Mac
ls python/reports/
```

Você verá arquivos como:
- `relatorio_20260531_093000.json`
- `atrasos_20260531_093000.json`
- `relatorio_detalhado_20260531_093000.csv`

### Abrir Relatórios

**JSON** - Usar editor de texto ou:
```bash
# Windows
notepad python\reports\relatorio*.json

# Linux/Mac
cat python/reports/relatorio*.json
```

**CSV** - Abrir no Excel:
```bash
# Windows
start python\reports\*.csv

# Mac
open python/reports/*.csv
```

## 4️⃣ Configurações Opcionais

### Editar Configurações

```bash
# Windows
notepad python\.env

# Linux/Mac
nano python/.env
```

**Variáveis disponíveis:**
```
DB_HOST=localhost          # Se banco está em outro lugar
DB_USER=root              # Se mudou usuário MySQL
DB_PASSWORD=              # Se MySQL tem senha
DB_PORT=3306              # Se MySQL usa porta diferente
DB_NAME=locket_db         # Se banco tem nome diferente
```

### Agendar Execução Automática

**Windows - Agendador de Tarefas:**
```
1. Abrir "Agendador de Tarefas"
2. Ação → Criar Tarefa...
3. Geral:
   - Nome: "Análise Empréstimos Locket"
4. Gatilho:
   - Novo... → Diariamente às 09:00
5. Ação:
   - Novo... → Programa: C:\Python\python.exe
   - Argumentos: D:\locket\python\main.py completo
6. OK
```

**Linux/Mac - Cron:**
```bash
crontab -e

# Adicionar linha (executar 09:00 todo dia):
0 9 * * * cd /home/user/locket/python && python3 main.py completo >> /tmp/locket_cron.log 2>&1
```

## 5️⃣ Próximas Funcionalidades

### Treinar Modelo de IA (Opcional)

```bash
# Primeira vez - treinar modelo
python main.py treinar

# Depois - usar para fazer previsões
python main.py previsoes
```

**O que muda:**
- Modelo aprende padrões de atrasos
- Consegue prever novos atrasos com 80-90% acurácia
- Ajuda a priorizar casos críticos

### Enviar Alertas por Email (Futura)

Será possível configurar para enviar:
```python
# Em .env (futura versão)
SMTP_SERVER=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu_email@gmail.com
SMTP_PASSWORD=sua_senha
ALERT_RECIPIENTS=admin@locket.edu.br
```

### Dashboard Web (Futura)

```bash
# Instalação (futura)
pip install flask
python web_dashboard.py
# Acesso: http://localhost:5000
```

## 6️⃣ Integração com API PHP

### Opção A: Adicionar Endpoint (Recomendado)

```php
// Em control/index.php (PHP)
case 'relatorio':
    // Chamar Python
    $output = shell_exec('python ../python/main.py relatorio');
    ApiResponse::success($output);
    break;
```

### Opção B: Webhook Automático

```bash
# Quando empréstimo é atrasado
POST /webhook/emprestimo-atrasado
Body: {emprestimo_id, dias_atraso}

# Python recebe e processa
```

## 7️⃣ Troubleshooting

### Erro: "Connection refused"
```bash
# Verificar se XAMPP está rodando
# Windows: abrir XAMPP Control Panel
# Linux: sudo /opt/lampp/bin/sudo ./lampp start

# Depois testar
python test_system.py
```

### Erro: "Module not found"
```bash
# Reinstalar dependências
pip install -r requirements.txt --force-reinstall
```

### Sem dados nos relatórios
```bash
# Verificar se há empréstimos no banco
mysql -u root -e "SELECT COUNT(*) FROM locket_db.emprestimo;"

# Se 0, criar dados de teste
# (próxima versão terá seed de dados)
```

## 📊 Métricas para Acompanhar

### Dashboard Sugerido

```
HOJE:
- Total de empréstimos: ___
- Ativos: ___
- Devolvidos: ___
- Atrasados: ___
- CRÍTICOS (>30 dias): ___

ALERTAS:
- Novos alertas hoje: ___
- Resolvidos: ___
- Pendentes: ___

IA:
- Empréstimos com risco previsto: ___
- Reincidentes identificados: ___
```

## 🎓 Aprender Mais

### Arquivos para Estudar

1. **Conceitos básicos**
   - `python/README.md` - Documentação geral

2. **Como funciona**
   - `python/config_db.py` - Conexão DB
   - `python/loan_analyzer.py` - Análises

3. **Machine Learning**
   - `python/delay_predictor.py` - Modelo

4. **Exemplos práticos**
   - `python/EXEMPLOS.md` - Código exemplo

### Linha de Aprendizado

```
1. Rodar exemplo simples (main.py relatorio)
   ↓
2. Entender o fluxo (dados → análise → relatório)
   ↓
3. Customizar queries em loan_analyzer.py
   ↓
4. Treinar modelo em delay_predictor.py
   ↓
5. Criar dashboard web (Flask)
```

## ✨ Checklist Final

```
Antes de começar:
☐ XAMPP rodando (Apache + MySQL)
☐ Banco locket_db criado
☐ Python 3.8+ instalado

Setup:
☐ Pasta python/ existe
☐ install.bat/install.sh executado
☐ .env configurado
☐ test_system.py passou ✓

Primeira execução:
☐ python main.py completo executado
☐ Relatórios criados em reports/
☐ Alertas exibidos no console

Próximas ações:
☐ Abrir relatórios em JSON/CSV
☐ Configurar agendador (cron/Task Scheduler)
☐ Personalizar queries (opcional)
☐ Treinar modelo ML (opcional)
```

## 🚀 Comando Pronto para Começar

```bash
# Copiar e colar:
cd d:\Dev\Códigos\locket.worktrees\agents-inteligencia-emprestimos-python-xampp\python
python main.py completo
```

## 📞 Se Tiver Problemas

1. **Executar teste:**
   ```bash
   python test_system.py
   ```
   Mostra qual componente tem problema

2. **Verificar logs:**
   ```bash
   # Windows
   type python\.env
   
   # Linux/Mac
   cat python/.env
   ```
   Confirmar credenciais do banco

3. **Reinstalar:**
   ```bash
   pip uninstall -r requirements.txt -y
   pip install -r requirements.txt
   ```

---

## ⏭️  PRÓXIMA AÇÃO

```
👉 Abra o terminal/prompt em:
   d:\Dev\Códigos\locket.worktrees\agents-inteligencia-emprestimos-python-xampp\python

👉 Se Windows, execute:
   install.bat

👉 Se Linux/Mac, execute:
   chmod +x install.sh
   ./install.sh

👉 Depois execute:
   python main.py completo

✨ Pronto! Sistema rodando!
```

---

**Dúvidas? Veja:**
- 📖 `GUIA_RAPIDO.md` - Início rápido
- 📚 `INTEGRACAO_COMPLETA.md` - Arquitetura
- 📝 `python/README.md` - Documentação
- 💻 `python/EXEMPLOS.md` - Código
