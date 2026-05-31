"""
Exemplos de uso do sistema de inteligência
Demonstra como usar cada módulo
"""

# ============================================
# EXEMPLO 1: Análise básica de empréstimos
# ============================================

from loan_analyzer import LoanAnalyzer

analyzer = LoanAnalyzer()

# Obter todos os empréstimos
all_loans = analyzer.get_all_loans()
print(f"Total de empréstimos: {len(all_loans)}")

# Analisar empréstimos atrasados
overdue_loans = analyzer.analyze_overdue_loans()
print(f"Empréstimos atrasados: {len(overdue_loans)}")

# Ver estatísticas por usuário
user_stats = analyzer.get_user_statistics()
for user in user_stats[:3]:
    print(f"{user['nome']}: {user['emprestimos_atrasados']} atrasados")

# Objetos mais emprestados
top_items = analyzer.get_most_borrowed_items(5)
for item in top_items:
    print(f"{item['nome']}: {item['total_emprestimos']} empréstimos")

# Gerar relatório completo
report = analyzer.generate_summary_report()
print(report['resumo_geral'])


# ============================================
# EXEMPLO 2: Sistema de Alertas
# ============================================

from alert_service import AlertService

alert_service = AlertService()

# Gerar todos os alertas
all_alerts = alert_service.generate_alerts()

# Exibir no console
alert_service.print_alerts()

# Salvar em arquivo JSON
alert_service.save_alerts('alerts_backup.json')


# ============================================
# EXEMPLO 3: Geração de Relatórios
# ============================================

from report_generator import ReportGenerator

generator = ReportGenerator()

# Gerar diferentes tipos de relatórios
json_path = generator.generate_json_report()
csv_path = generator.generate_csv_report()
overdue_path = generator.generate_overdue_report()

print(f"Relatório JSON: {json_path}")
print(f"Relatório CSV: {csv_path}")
print(f"Relatório Atrasos: {overdue_path}")


# ============================================
# EXEMPLO 4: Modelo de Previsão (ML)
# ============================================

from delay_predictor import DelayPredictor

predictor = DelayPredictor()

# Treinar o modelo (primeira vez)
predictor.train_model()

# Depois, carregar modelo
predictor.load_model()

# Fazer previsões
prediction = predictor.predict_loan(
    id_usuario=1,
    dias_duracao=7,
    mes_retirada=5,
    dia_semana=3,
    historico_emprestimos=5,
    historico_atrasos=1,
    taxa_atraso=0.2,
    funcao='ALUNO'
)

print(f"Vai atrasar: {prediction['vai_atrasar']}")
print(f"Probabilidade: {prediction['probabilidade_atraso']:.2%}")
print(f"Confiança: {prediction['confianca']:.2%}")


# ============================================
# EXEMPLO 5: Utilitários
# ============================================

from utils import (
    format_date,
    calculate_days_overdue,
    get_risk_level,
    print_table,
    save_json,
    load_json
)

from datetime import datetime, timedelta

# Formatar data
date = datetime.now()
formatted = format_date(date)
print(f"Data: {formatted}")

# Calcular atraso
due_date = datetime.now().date() - timedelta(days=15)
days_overdue = calculate_days_overdue(due_date)
risk = get_risk_level(days_overdue)
print(f"Dias de atraso: {days_overdue} - Risco: {risk}")

# Imprimir tabela
data = [
    {'nome': 'João', 'atrasos': 2},
    {'nome': 'Maria', 'atrasos': 0},
    {'nome': 'Pedro', 'atrasos': 5}
]
print_table(data)

# Salvar e carregar JSON
save_json(data, 'backup.json')
loaded = load_json('backup.json')


# ============================================
# EXEMPLO 6: Integração Completa
# ============================================

from config_db import DatabaseConfig

db = DatabaseConfig()

# Executar query customizada
query = """
    SELECT 
        u.nome,
        COUNT(e.id_emprestimo) as total,
        SUM(CASE WHEN e.status = 'ATRASADO' THEN 1 ELSE 0 END) as atrasados
    FROM usuario u
    LEFT JOIN emprestimo e ON u.id_usuario = e.id_usuario
    GROUP BY u.id_usuario
    ORDER BY atrasados DESC
    LIMIT 10
"""

results = db.execute_query(query)
print_table(results, ['nome', 'total', 'atrasados'])
