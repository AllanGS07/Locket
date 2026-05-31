"""
Ponto de entrada do sistema de inteligência
Orquestra análises, previsões e relatórios
"""
import sys
import argparse
from datetime import datetime
from loan_analyzer import LoanAnalyzer
from delay_predictor import DelayPredictor
from report_generator import ReportGenerator
from alert_service import AlertService

def main():
    parser = argparse.ArgumentParser(
        description='Sistema de Inteligência para Análise de Empréstimos'
    )
    
    parser.add_argument(
        'comando',
        nargs='?',
        default='relatorio',
        choices=['relatorio', 'alertas', 'treinar', 'previsoes', 'completo'],
        help='Comando a executar'
    )
    
    args = parser.parse_args()
    
    print(f"\n{'='*60}")
    print("SISTEMA DE INTELIGÊNCIA - EMPRÉSTIMOS")
    print(f"{'='*60}")
    print(f"Executando: {args.comando}")
    print(f"Hora: {datetime.now().strftime('%d/%m/%Y %H:%M:%S')}\n")
    
    if args.comando == 'relatorio':
        print("Gerando relatório...")
        analyzer = LoanAnalyzer()
        report = analyzer.generate_summary_report()
        
        if report:
            print("\n" + "="*60)
            print("RESUMO GERAL")
            print("="*60)
            for key, value in report['resumo_geral'].items():
                print(f"{key}: {value}")
            
            if report['emprestimos_atrasados']:
                print("\nEMPRÉSTIMOS ATRASADOS (Top 5):")
                for emp in report['emprestimos_atrasados'][:5]:
                    print(f"  - {emp['nome']}: {emp['dias_atraso']} dias (Risco: {emp['nivel_risco']})")
            
            if report['usuarios_com_maior_atraso']:
                print("\nUSUÁRIOS COM MAIOR ATRASO:")
                for usr in report['usuarios_com_maior_atraso']:
                    print(f"  - {usr['nome']}: {usr['emprestimos_atrasados']} atrasados")
    
    elif args.comando == 'alertas':
        print("Gerando alertas...")
        alert_service = AlertService()
        alert_service.generate_alerts()
        alert_service.print_alerts()
        alert_service.save_alerts('reports/alerts.json')
    
    elif args.comando == 'treinar':
        print("Treinando modelo de previsão...")
        predictor = DelayPredictor()
        if predictor.train_model():
            print("✓ Modelo treinado com sucesso!")
        else:
            print("✗ Erro ao treinar modelo")
    
    elif args.comando == 'previsoes':
        print("Gerando relatório de previsões...")
        generator = ReportGenerator()
        try:
            generator.generate_predictions_report()
            print("✓ Relatório de previsões gerado com sucesso!")
        except Exception as e:
            print(f"✗ Erro ao gerar previsões: {e}")
    
    elif args.comando == 'completo':
        print("Executando análise completa...\n")
        
        # Gerar relatórios
        print("[1/4] Gerando relatórios...")
        generator = ReportGenerator()
        generator.generate_json_report()
        generator.generate_csv_report()
        generator.generate_overdue_report()
        
        # Gerar alertas
        print("\n[2/4] Gerando alertas...")
        alert_service = AlertService()
        alert_service.generate_alerts()
        alert_service.print_alerts()
        alert_service.save_alerts('reports/alerts.json')
        
        # Mostrar resumo
        print("\n[3/4] Resumo da análise...")
        analyzer = LoanAnalyzer()
        report = analyzer.generate_summary_report()
        
        if report:
            print(f"Total de empréstimos: {report['resumo_geral']['total_emprestimos']}")
            print(f"Empréstimos ativos: {report['resumo_geral']['emprestimos_ativos']}")
            print(f"Empréstimos atrasados: {report['resumo_geral']['emprestimos_atrasados']}")
        
        print("\n[4/4] ✓ Análise completa finalizada!")
    
    print(f"\n{'='*60}\n")

if __name__ == '__main__':
    main()
