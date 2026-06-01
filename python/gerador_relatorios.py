import json
import csv
try:
    import pandas as pd
except Exception:
    pd = None
from datetime import datetime
from analisador_emprestimos import AnalisadorEmprestimos
from preditor_atraso import PreditorAtraso
import os

class GeradorRelatorios:
    def __init__(self, diretorio_saida='relatorios'):
        self.analisador = AnalisadorEmprestimos()
        self.preditor = PreditorAtraso()
        self.diretorio_saida = diretorio_saida
        os.makedirs(diretorio_saida, exist_ok=True)
    
    def gerar_relatorio_json(self, nome_arquivo=None):
        if nome_arquivo is None:
            nome_arquivo = f"relatorio_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        caminho_arquivo = os.path.join(self.diretorio_saida, nome_arquivo)
        relatorio = self.analisador.gerar_relatorio_resumido()
        
        with open(caminho_arquivo, 'w', encoding='utf-8') as f:
            json.dump(relatorio, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"Relatorio JSON salvo: {caminho_arquivo}")
        return caminho_arquivo
    
    def gerar_relatorio_csv(self, nome_arquivo=None):
        if nome_arquivo is None:
            nome_arquivo = f"relatorio_detalhado_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
        
        caminho_arquivo = os.path.join(self.diretorio_saida, nome_arquivo)
        emprestimos = self.analisador.obter_todos_emprestimos()
        
        if emprestimos:
            with open(caminho_arquivo, 'w', newline='', encoding='utf-8') as f:
                escritor = csv.DictWriter(f, fieldnames=emprestimos[0].keys())
                escritor.writeheader()
                escritor.writerows(emprestimos)
            
            print(f"Relatorio CSV salvo: {caminho_arquivo}")
            return caminho_arquivo
        
        return None
    
    def gerar_relatorio_atrasos(self, nome_arquivo=None):
        if nome_arquivo is None:
            nome_arquivo = f"atrasos_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        caminho_arquivo = os.path.join(self.diretorio_saida, nome_arquivo)
        atrasados = self.analisador.analisar_emprestimos_atrasados()
        
        relatorio = {
            'data_geracao': datetime.now().isoformat(),
            'total_atrasos': len(atrasados) if atrasados else 0,
            'atrasos_criticos': len([e for e in atrasados if e.get('nivel_risco') == 'Critico']) if atrasados else 0,
            'atrasos_altos': len([e for e in atrasados if e.get('nivel_risco') == 'Alto']) if atrasados else 0,
            'detalhes': atrasados if atrasados else []
        }
        
        with open(caminho_arquivo, 'w', encoding='utf-8') as f:
            json.dump(relatorio, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"Relatorio de atrasos salvo: {caminho_arquivo}")
        return caminho_arquivo
    
    def gerar_relatorio_previsoes(self, nome_arquivo=None):
        if nome_arquivo is None:
            nome_arquivo = f"previsoes_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        caminho_arquivo = os.path.join(self.diretorio_saida, nome_arquivo)
        emprestimos = self.analisador.obter_todos_emprestimos()
        previsoes = []

        def converter_data(valor):
            if valor is None:
                return None
            if pd is not None:
                try:
                    return pd.to_datetime(valor)
                except Exception:
                    pass
            try:
                from datetime import datetime as dt
                if isinstance(valor, dt):
                    return valor
                try:
                    return dt.fromisoformat(valor)
                except Exception:
                    try:
                        return dt.strptime(valor, '%Y-%m-%d %H:%M:%S')
                    except Exception:
                        return dt.strptime(valor, '%Y-%m-%d')
            except Exception:
                return None

        if emprestimos:
            for emprestimo in emprestimos[:20]:
                try:
                    data_retirada = converter_data(emprestimo.get('data_retirada'))
                    data_devolucao_prevista = converter_data(emprestimo.get('data_devolucao_prevista'))
                    dias_duracao = (data_devolucao_prevista - data_retirada).days if data_devolucao_prevista and data_retirada else 0
                    mes_retirada = data_retirada.month if data_retirada else 0
                    dia_semana = getattr(data_retirada, 'dayofweek', None) or (data_retirada.weekday() if data_retirada else 0)

                    predicao = self.preditor.prever_emprestimo(
                        id_usuario=emprestimo.get('id_usuario'),
                        dias_duracao=dias_duracao,
                        mes_retirada=mes_retirada,
                        dia_semana=dia_semana,
                        historico_emprestimos=1,
                        historico_atrasos=0,
                        taxa_atraso=0.0,
                        funcao='ALUNO'
                    )

                    if predicao:
                        previsoes.append({
                            'id_emprestimo': emprestimo.get('id_emprestimo'),
                            'usuario': emprestimo.get('usuario_nome'),
                            'objeto': emprestimo.get('objeto_nome'),
                            **predicao
                        })
                except Exception as e:
                    print(f"Erro ao prever: {e}")

        relatorio = {
            'data_geracao': datetime.now().isoformat(),
            'total_analisados': len(previsoes),
            'com_risco_atraso': len([p for p in previsoes if p.get('vai_atrasar')]),
            'previsoes': previsoes
        }

        with open(caminho_arquivo, 'w', encoding='utf-8') as f:
            json.dump(relatorio, f, indent=2, ensure_ascii=False, default=str)

        print(f"Relatorio de previsoes salvo: {caminho_arquivo}")
        return caminho_arquivo
    
    def generate_json_report(self, filename=None):
        if filename is None:
            filename = f"relatorio_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        filepath = os.path.join(self.output_dir, filename)
        report = self.analyzer.generate_summary_report()
        
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False, default=str)
        
        print(f"Relatório JSON salvo: {filepath}")
        return filepath
    
    def generate_csv_report(self, filename=None):
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
        if filename is None:
            filename = f"previsoes_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        filepath = os.path.join(self.output_dir, filename)
        loans = self.analyzer.get_all_loans()
        predictions = []

        def _to_dt(v):
            if v is None:
                return None
            if pd is not None:
                try:
                    return pd.to_datetime(v)
                except Exception:
                    pass
            try:
                from datetime import datetime as _dt
                if isinstance(v, _dt):
                    return v
                try:
                    return _dt.fromisoformat(v)
                except Exception:
                    try:
                        return _dt.strptime(v, '%Y-%m-%d %H:%M:%S')
                    except Exception:
                        return _dt.strptime(v, '%Y-%m-%d')
            except Exception:
                return None

        if loans:
            for loan in loans[:20]:
                try:
                    data_retirada = _to_dt(loan.get('data_retirada'))
                    data_devolucao_prevista = _to_dt(loan.get('data_devolucao_prevista'))
                    dias_duracao = (data_devolucao_prevista - data_retirada).days if data_devolucao_prevista and data_retirada else 0
                    mes_retirada = data_retirada.month if data_retirada else 0
                    dia_semana = getattr(data_retirada, 'dayofweek', None) or (data_retirada.weekday() if data_retirada else 0)

                    pred = self.predictor.predict_loan(
                        id_usuario=loan.get('id_usuario'),
                        dias_duracao=dias_duracao,
                        mes_retirada=mes_retirada,
                        dia_semana=dia_semana,
                        historico_emprestimos=1,
                        historico_atrasos=0,
                        taxa_atraso=0.0,
                        funcao='ALUNO'
                    )

                    if pred:
                        predictions.append({
                            'id_emprestimo': loan.get('id_emprestimo'),
                            'usuario': loan.get('usuario_nome'),
                            'objeto': loan.get('objeto_nome'),
                            **pred
                        })
                except Exception as e:
                    print(f"Erro ao prever: {e}")

        report = {
            'data_geracao': datetime.now().isoformat(),
            'total_analisados': len(predictions),
            'com_risco_atraso': len([p for p in predictions if p.get('vai_atrasar')]),
            'previsoes': predictions
        }

        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(report, f, indent=2, ensure_ascii=False, default=str)

        print(f"Relatório de previsões salvo: {filepath}")
        return filepath
