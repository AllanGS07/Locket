import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import joblib
import os
from datetime import datetime
from configuracao_bd import ConfiguracaoBD
from logger import registrador

class PreditorAtraso:
    def __init__(self, caminho_modelo='modelos/modelo_atraso.pkl'):
        self.bd = ConfiguracaoBD()
        self.modelo = None
        self.caminho_modelo = caminho_modelo
        self.codificador_status = LabelEncoder()
        self.codificador_funcao = LabelEncoder()
        os.makedirs(os.path.dirname(caminho_modelo) if os.path.dirname(caminho_modelo) else '.', exist_ok=True)
    
    def preparar_dados(self):
        try:
            import pandas as pd
        except Exception:
            registrador.erro('pandas nao disponivel para preparar dados')
            return None

        consulta = """
            SELECT 
                e.id_emprestimo,
                e.id_usuario,
                u.funcao,
                e.id_objeto,
                e.data_retirada,
                e.data_devolucao_prevista,
                e.data_devolucao_real,
                CASE WHEN e.data_devolucao_real IS NULL AND CURDATE() > e.data_devolucao_prevista THEN 1
                     WHEN e.data_devolucao_real IS NOT NULL AND e.data_devolucao_real > e.data_devolucao_prevista THEN 1
                     ELSE 0 END as atrasado,
                COUNT(DISTINCT e2.id_emprestimo) as historico_emprestimos,
                SUM(CASE WHEN e2.data_devolucao_real IS NULL AND CURDATE() > e2.data_devolucao_prevista THEN 1 ELSE 0 END) as historico_atrasos
            FROM emprestimo e
            JOIN usuario u ON e.id_usuario = u.id_usuario
            LEFT JOIN emprestimo e2 ON e.id_usuario = e2.id_usuario AND e2.id_emprestimo < e.id_emprestimo
            WHERE e.data_retirada IS NOT NULL
            GROUP BY e.id_emprestimo, e.id_usuario, u.funcao, e.id_objeto, 
                     e.data_retirada, e.data_devolucao_prevista, e.data_devolucao_real
            LIMIT 1000
        """
        
        dados = self.bd.executar_consulta(consulta)
        if not dados:
            registrador.aviso('Sem dados disponivel para treinar modelo')
            return None
        
        df = pd.DataFrame(dados)
        df['data_retirada'] = pd.to_datetime(df['data_retirada'])
        df['data_devolucao_prevista'] = pd.to_datetime(df['data_devolucao_prevista'])
        
        df['dias_duracao_prevista'] = (df['data_devolucao_prevista'] - df['data_retirada']).dt.days
        df['mes_retirada'] = df['data_retirada'].dt.month
        df['dia_semana_retirada'] = df['data_retirada'].dt.dayofweek
        
        df['historico_emprestimos'] = df['historico_emprestimos'].fillna(0)
        df['historico_atrasos'] = df['historico_atrasos'].fillna(0)
        
        df['taxa_atraso'] = df.apply(
            lambda x: x['historico_atrasos'] / x['historico_emprestimos'] if x['historico_emprestimos'] > 0 else 0,
            axis=1
        )
        
        return df
    
    def treinar_modelo(self):
        df = self.preparar_dados()
        if df is None:
            return False
        
        features = ['id_usuario', 'dias_duracao_prevista', 'mes_retirada', 
                   'dia_semana_retirada', 'historico_emprestimos', 'historico_atrasos', 'taxa_atraso']
        
        df['funcao_codificada'] = self.codificador_funcao.fit_transform(df['funcao'])
        features.append('funcao_codificada')
        
        X = df[features]
        y = df['atrasado']
        
        X_treino, X_teste, y_treino, y_teste = train_test_split(X, y, test_size=0.2, random_state=42)
        
        self.modelo = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=10)
        self.modelo.fit(X_treino, y_treino)
        
        y_pred = self.modelo.predict(X_teste)
        acuracia = accuracy_score(y_teste, y_pred)
        
        registrador.info(f'Modelo treinado com acuracia {acuracia:.2%}')
        
        joblib.dump(self.modelo, self.caminho_modelo)
        registrador.info(f'Modelo salvo em {self.caminho_modelo}')
        
        return True
    
    def carregar_modelo(self):
        if os.path.exists(self.caminho_modelo):
            self.modelo = joblib.load(self.caminho_modelo)
            registrador.info(f'Modelo carregado de {self.caminho_modelo}')
            return True
        registrador.aviso(f'Modelo nao encontrado em {self.caminho_modelo}')
        return False
    
    def prever_emprestimo(self, id_usuario, dias_duracao, mes_retirada, dia_semana, 
                     historico_emprestimos, historico_atrasos, taxa_atraso, funcao):
        if self.modelo is None:
            if not self.carregar_modelo():
                return None
        
        try:
            funcao_codificada = self.codificador_funcao.transform([funcao])[0]
        except ValueError:
            registrador.aviso(f'Funcao desconhecida para predicao: {funcao}')
            return None
        
        X = [[id_usuario, dias_duracao, mes_retirada, dia_semana, 
              historico_emprestimos, historico_atrasos, taxa_atraso, funcao_codificada]]
        
        predicao = self.modelo.predict(X)[0]
        probabilidade = self.modelo.predict_proba(X)[0]
        
        return {
            'vai_atrasar': bool(predicao),
            'probabilidade_atraso': float(probabilidade[1]),
            'confianca': float(max(probabilidade))
        }
