"""
Utilitários gerais do sistema
"""
from datetime import datetime, timedelta
import json

def format_date(date_obj):
    """Formata data para string"""
    if date_obj:
        return date_obj.strftime('%d/%m/%Y')
    return 'N/A'

def format_datetime(date_obj):
    """Formata data e hora"""
    if date_obj:
        return date_obj.strftime('%d/%m/%Y %H:%M:%S')
    return 'N/A'

def calculate_days_overdue(due_date, current_date=None):
    """Calcula dias de atraso"""
    if current_date is None:
        current_date = datetime.now().date()
    
    if due_date < current_date:
        return (current_date - due_date).days
    return 0

def priority_color(priority_level):
    """Retorna cor para cada nível de prioridade (terminal)"""
    colors = {
        'CRÍTICO': '\033[91m',   # Vermelho
        'ALTO': '\033[93m',      # Amarelo
        'MÉDIO': '\033[92m',     # Verde
        'BAIXO': '\033[94m'      # Azul
    }
    reset = '\033[0m'
    return colors.get(priority_level, '') + priority_level + reset

def print_table(data, headers=None):
    """Imprime tabela formatada no console"""
    if not data:
        print("Nenhum dado para exibir")
        return
    
    if isinstance(data, list) and isinstance(data[0], dict):
        if headers is None:
            headers = list(data[0].keys())
        
        # Calcular larguras de coluna
        col_widths = {}
        for header in headers:
            max_width = len(str(header))
            for row in data:
                max_width = max(max_width, len(str(row.get(header, ''))))
            col_widths[header] = max_width
        
        # Cabeçalho
        header_line = ' | '.join(f"{h:<{col_widths[h]}}" for h in headers)
        print(header_line)
        print('-' * len(header_line))
        
        # Dados
        for row in data:
            row_line = ' | '.join(f"{str(row.get(h, '')):<{col_widths[h]}}" for h in headers)
            print(row_line)

def save_json(data, filepath):
    """Salva dados em JSON"""
    with open(filepath, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False, default=str)

def load_json(filepath):
    """Carrega dados de JSON"""
    with open(filepath, 'r', encoding='utf-8') as f:
        return json.load(f)

def get_risk_level(days_overdue):
    """Determina nível de risco baseado em dias de atraso"""
    if days_overdue > 30:
        return 'Crítico'
    elif days_overdue > 14:
        return 'Alto'
    elif days_overdue > 7:
        return 'Médio'
    else:
        return 'Baixo'
