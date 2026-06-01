import json
import csv
try:
    import pandas as pd
except Exception:
    pd = None
from datetime import datetime
from analisador_emprestimos import AnalisadorEmprestimos
from preditor_atraso import PreditorAtraso
from logger import registrador
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
        
        registrador.info(f'Relatorio JSON gerado: {caminho_arquivo}')
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
            
            registrador.info(f'Relatorio CSV gerado: {caminho_arquivo}')
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
        
        registrador.info(f'Relatorio atrasos gerado: {caminho_arquivo}')
        return caminho_arquivo
    
    def converter_data(self, valor):
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
    
    def gerar_relatorio_previsoes(self, nome_arquivo=None):
        if nome_arquivo is None:
            nome_arquivo = f"previsoes_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        caminho_arquivo = os.path.join(self.diretorio_saida, nome_arquivo)
        emprestimos = self.analisador.obter_todos_emprestimos()
        previsoes = []

        if emprestimos:
            for emprestimo in emprestimos[:20]:
                try:
                    data_retirada = self.converter_data(emprestimo.get('data_retirada'))
                    data_devolucao_prevista = self.converter_data(emprestimo.get('data_devolucao_prevista'))
                    dias_duracao = (data_devolucao_prevista - data_retirada).days if data_devolucao_prevista and data_retirada else 0
                    mes_retirada = data_retirada.month if data_retirada else 0
                    dia_semana = getattr(data_retirada, 'dayofweek', None) or (data_retirada.weekday() if data_retirada else 0)

                    id_usuario = emprestimo.get('id_usuario')
                    historico = self.analisador.obter_historico_usuario(id_usuario)

                    predicao = self.preditor.prever_emprestimo(
                        id_usuario=id_usuario,
                        dias_duracao=dias_duracao,
                        mes_retirada=mes_retirada,
                        dia_semana=dia_semana,
                        historico_emprestimos=historico['total_emprestimos'],
                        historico_atrasos=historico['total_atrasos'],
                        taxa_atraso=historico['taxa_atraso'],
                        funcao=emprestimo.get('funcao', 'ALUNO')
                    )

                    if predicao:
                        previsoes.append({
                            'id_emprestimo': emprestimo.get('id_emprestimo'),
                            'usuario': emprestimo.get('usuario_nome'),
                            'objeto': emprestimo.get('objeto_nome'),
                            **predicao
                        })
                except Exception as e:
                    registrador.erro(f'Erro ao prever emprestimo: {str(e)}')

        relatorio = {
            'data_geracao': datetime.now().isoformat(),
            'total_analisados': len(previsoes),
            'com_risco_atraso': len([p for p in previsoes if p.get('vai_atrasar')]),
            'previsoes': previsoes
        }

        with open(caminho_arquivo, 'w', encoding='utf-8') as f:
            json.dump(relatorio, f, indent=2, ensure_ascii=False, default=str)

        registrador.info(f'Relatorio previsoes gerado: {caminho_arquivo}')
        return caminho_arquivo
