import json
from datetime import datetime
from configuracao_bd import ConfiguracaoBD

class ServicoAlertas:
    def __init__(self):
        self.bd = ConfiguracaoBD()
        self.alertas = []
        self._cache_alertas = None
    
    def verificar_atrasos_criticos(self):
        consulta = """
            SELECT 
                e.ID_Emprestimo,
                u.ID_Usuario,
                u.Nome,
                u.Email,
                o.Nome as objeto,
                DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) as dias_atraso
            FROM Emprestimos e
            JOIN Usuario u ON e.ID_Usuario = u.ID_Usuario
            JOIN Objeto o ON e.ID_Objeto = o.ID_Objeto
            WHERE e.Data_Devolucao_Real IS NULL 
            AND DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) > 30
            ORDER BY dias_atraso DESC
        """
        
        resultados = self.bd.executar_consulta(consulta)
        if resultados:
            for item in resultados:
                self.alertas.append({
                    'tipo': 'CRITICO',
                    'prioridade': 1,
                    'emprestimo_id': item.get('ID_Emprestimo'),
                    'usuario_id': item.get('ID_Usuario'),
                    'usuario_nome': item.get('Nome'),
                    'usuario_email': item.get('Email'),
                    'objeto': item.get('objeto'),
                    'dias_atraso': item.get('dias_atraso'),
                    'mensagem': f"CRITICO: {item.get('Nome')} deve devolver {item.get('objeto')} ha {item.get('dias_atraso')} dias!",
                    'timestamp': datetime.now().isoformat()
                })
        
        return resultados
    
    def verificar_atrasos_altos(self):
        consulta = """
            SELECT 
                e.ID_Emprestimo,
                u.ID_Usuario,
                u.Nome,
                u.Email,
                o.Nome as objeto,
                DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) as dias_atraso
            FROM Emprestimos e
            JOIN Usuario u ON e.ID_Usuario = u.ID_Usuario
            JOIN Objeto o ON e.ID_Objeto = o.ID_Objeto
            WHERE e.Data_Devolucao_Real IS NULL 
            AND DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) BETWEEN 14 AND 30
        """
        
        resultados = self.bd.executar_consulta(consulta)
        if resultados:
            for item in resultados:
                self.alertas.append({
                    'tipo': 'ALTO',
                    'prioridade': 2,
                    'emprestimo_id': item.get('ID_Emprestimo'),
                    'usuario_id': item.get('ID_Usuario'),
                    'usuario_nome': item.get('Nome'),
                    'usuario_email': item.get('Email'),
                    'objeto': item.get('objeto'),
                    'dias_atraso': item.get('dias_atraso'),
                    'mensagem': f"AVISO: {item.get('Nome')} esta com atraso de {item.get('dias_atraso')} dias",
                    'timestamp': datetime.now().isoformat()
                })
        
        return resultados
    
    def verificar_reincidentes(self):
        consulta = """
            SELECT 
                u.ID_Usuario,
                u.Nome,
                u.Email,
                COUNT(e.ID_Emprestimo) as total_emprestimos,
                SUM(CASE WHEN e.Data_Devolucao_Real IS NULL AND CURDATE() > e.Data_Devolucao_Prevista THEN 1 ELSE 0 END) as atrasos_atuais,
                SUM(CASE WHEN e.Data_Devolucao_Real IS NOT NULL AND e.Data_Devolucao_Real > e.Data_Devolucao_Prevista THEN 1 ELSE 0 END) as atrasos_historicos,
                ROUND((SUM(CASE WHEN e.Data_Devolucao_Real IS NOT NULL AND e.Data_Devolucao_Real > e.Data_Devolucao_Prevista THEN 1 ELSE 0 END) / COUNT(e.ID_Emprestimo)) * 100, 2) as taxa_atraso_percentual
            FROM Usuario u
            JOIN Emprestimos e ON u.ID_Usuario = e.ID_Usuario
            GROUP BY u.ID_Usuario, u.Nome, u.Email
            HAVING (SUM(CASE WHEN e.Data_Devolucao_Real IS NOT NULL AND e.Data_Devolucao_Real > e.Data_Devolucao_Prevista THEN 1 ELSE 0 END) / COUNT(e.ID_Emprestimo)) > 0.3
            ORDER BY taxa_atraso_percentual DESC
        """
        
        resultados = self.bd.executar_consulta(consulta)
        if resultados:
            for item in resultados:
                self.alertas.append({
                    'tipo': 'REINCIDENTE',
                    'prioridade': 3,
                    'usuario_id': item.get('id_usuario'),
                    'usuario_nome': item.get('nome'),
                    'usuario_email': item.get('email'),
                    'atrasos_historicos': item.get('atrasos_historicos'),
                    'taxa_atraso': item.get('taxa_atraso_percentual'),
                    'mensagem': f"ALERTA: {item.get('nome')} tem {item.get('taxa_atraso_percentual')}% de atrasos historicos",
                    'timestamp': datetime.now().isoformat()
                })
        
        return resultados
    
    def gerar_alertas(self):
        if self._cache_alertas is not None:
            return self._cache_alertas
        
        self.alertas = []
        self.verificar_atrasos_criticos()
        self.verificar_atrasos_altos()
        self.verificar_reincidentes()
        self.alertas.sort(key=lambda x: x.get('prioridade'))
        self._cache_alertas = self.alertas
        return self.alertas
    
    def limpar_cache(self):
        self._cache_alertas = None
    
    def salvar_alertas(self, caminho_arquivo='alertas.json'):
        with open(caminho_arquivo, 'w', encoding='utf-8') as f:
            json.dump(self.alertas, f, indent=2, ensure_ascii=False, default=str)
        return caminho_arquivo
    

