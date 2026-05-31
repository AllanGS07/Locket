"""
Gerador de relatórios em múltiplos formatos
"""
import json
import csv
from datetime import datetime
from loan_analyzer import LoanAnalyzer
from delay_predictor import DelayPredictor
import os

class ReportGenerator:
    """Gera relatórios de análise"""
    
    def __init__(self, output_dir='reports'):
        self.analyzer = LoanAnalyzer()
        self.predictor = DelayPredictor()
        self.output_dir = output_dir
        
        # Criar diretório se não existir
        os.makedirs(output_dir, exist_ok=True)
    
    def generate_json_report(self, filename=None):
        """Gera relatório em JSON"""
        if filename is None:
            filename = f"relatorio_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        filepath = os.path.join(self.output_dir, filename)
        
        report = self.analyzer.generate_summary_report()
        
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"Relatório JSON salvo: {filepath}")
        return filepath
    
    def generate_csv_report(self, filename=None):
        """Gera relatório detalhado em CSV"""
        if filename is None:
            filename = f"relatorio_detalhado_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
        
        filepath = os.path.join(self.output_dir, filename)
        
        loans = self.analyzer.get_all_loans()
        
        if loans:
            with open(filepath, 'w', newline='', encoding='utf-8') as f:
                writer = csv.DictWriter(f, fieldnames=loans[0].keys())
                writer.writeheader()
                writer.writerows(loans)
            
            print(f"Relatório CSV salvo: {filepath}")
            return filepath
        
        return None
    
    def generate_overdue_report(self, filename=None):
        """Relatório específico de atrasos"""
        if filename is None:
            filename = f"atrasos_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        filepath = os.path.join(self.output_dir, filename)
        
        overdue = self.analyzer.analyze_overdue_loans()
        
        report = {
            'data_geracao': datetime.now().isoformat(),
            'total_atrasos': len(overdue) if overdue else 0,
            'atrasos_criticos': len([e for e in overdue if e['nivel_risco'] == 'Crítico']) if overdue else 0,
            'atrasos_altos': len([e for e in overdue if e['nivel_risco'] == 'Alto']) if overdue else 0,
            'detalhes': overdue if overdue else []
        }
        
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"Relatório de atrasos salvo: {filepath}")
        return filepath
    
    def generate_predictions_report(self, filename=None):
        """Gera relatório com previsões de atrasos"""
        if filename is None:
            filename = f"previsoes_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        filepath = os.path.join(self.output_dir, filename)
        
        loans = self.analyzer.get_all_loans()
        predictions = []
        
        if loans:
            for loan in loans[:20]:  # Primeiros 20 empréstimos
                try:
                    dias_duracao = (pd.to_datetime(loan['data_devolucao_prevista']) - 
                                   pd.to_datetime(loan['data_retirada'])).days
                    
                    pred = self.predictor.predict_loan(
                        id_usuario=loan['id_usuario'],
                        dias_duracao=dias_duracao,
                        mes_retirada=pd.to_datetime(loan['data_retirada']).month,
                        dia_semana=pd.to_datetime(loan['data_retirada']).dayofweek,
                        historico_emprestimos=1,
                        historico_atrasos=0,
                        taxa_atraso=0.0,
                        funcao='ALUNO'
                    )
                    
                    if pred:
                        predictions.append({
                            'id_emprestimo': loan['id_emprestimo'],
                            'usuario': loan['usuario_nome'],
                            'objeto': loan['objeto_nome'],
                            **pred
                        })
                except Exception as e:
                    print(f"Erro ao prever: {e}")
        
        report = {
            'data_geracao': datetime.now().isoformat(),
            'total_analisados': len(predictions),
            'com_risco_atraso': len([p for p in predictions if p['vai_atrasar']]),
            'previsoes': predictions
        }
        
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"Relatório de previsões salvo: {filepath}")
        return filepath


if __name__ == '__main__':
    import pandas as pd
    
    generator = ReportGenerator()
    
    print("Gerando relatórios...\n")
    generator.generate_json_report()
    generator.generate_csv_report()
    generator.generate_overdue_report()
    # generator.generate_predictions_report()
