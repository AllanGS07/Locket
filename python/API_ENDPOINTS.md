# Python API - Análise de Empréstimos

API Flask para análise inteligente de empréstimos.

## Instalação

```bash
pip install -r requirements.txt
```

## Executar

```bash
python main.py
```

A API estará disponível em `http://localhost:5000`

## Endpoints Principais

- `GET /api/health` - Verificar saúde
- `GET /api/loans` - Todos os empréstimos
- `GET /api/loans/overdue` - Empréstimos atrasados
- `GET /api/loans/summary` - Resumo geral
- `GET /api/alerts` - Todos os alertas
- `GET /api/alerts/critical` - Alertas críticos
- `POST /api/predictions/loan` - Prever atraso
- `POST /api/model/train` - Treinar modelo
- `GET /api/analysis/complete` - Análise completa
