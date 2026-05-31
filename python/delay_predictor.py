import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import joblib
import os
from datetime import datetime
from config_db import DatabaseConfig

class DelayPredictor:
    def __init__(self, model_path='models/delay_model.pkl'):
        self.db = DatabaseConfig()
        self.model = None
        self.model_path = model_path
        self.le_status = LabelEncoder()
        self.le_funcao = LabelEncoder()
        os.makedirs(os.path.dirname(model_path) if os.path.dirname(model_path) else '.', exist_ok=True)
    
    def prepare_dataset(self):
        query = """
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
        
        data = self.db.execute_query(query)
        if not data:
            print("Sem dados para treinar modelo")
            return None
        
        df = pd.DataFrame(data)
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
    
    def train_model(self):
        df = self.prepare_dataset()
        if df is None:
            return False
        
        features = ['id_usuario', 'dias_duracao_prevista', 'mes_retirada', 
                   'dia_semana_retirada', 'historico_emprestimos', 'historico_atrasos', 'taxa_atraso']
        
        df['funcao_encoded'] = self.le_funcao.fit_transform(df['funcao'])
        features.append('funcao_encoded')
        
        X = df[features]
        y = df['atrasado']
        
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        self.model = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=10)
        self.model.fit(X_train, y_train)
        
        y_pred = self.model.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred)
        
        print(f"Modelo treinado com sucesso!")
        print(f"Acurácia: {accuracy:.2%}")
        print("\nRelatório de Classificação:")
        print(classification_report(y_test, y_pred))
        
        joblib.dump(self.model, self.model_path)
        print(f"Modelo salvo em: {self.model_path}")
        
        return True
    
    def load_model(self):
        if os.path.exists(self.model_path):
            self.model = joblib.load(self.model_path)
            print(f"Modelo carregado de: {self.model_path}")
            return True
        return False
    
    def predict_loan(self, id_usuario, dias_duracao, mes_retirada, dia_semana, 
                     historico_emprestimos, historico_atrasos, taxa_atraso, funcao):
        if self.model is None:
            if not self.load_model():
                return None
        
        funcao_encoded = self.le_funcao.transform([funcao])[0]
        
        X = [[id_usuario, dias_duracao, mes_retirada, dia_semana, 
              historico_emprestimos, historico_atrasos, taxa_atraso, funcao_encoded]]
        
        prediction = self.model.predict(X)[0]
        probability = self.model.predict_proba(X)[0]
        
        return {
            'vai_atrasar': bool(prediction),
            'probabilidade_atraso': float(probability[1]),
            'confianca': float(max(probability))
        }
