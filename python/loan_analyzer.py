import pandas as pd
from datetime import datetime
from config_db import DatabaseConfig

class LoanAnalyzer:
    def __init__(self):
        self.db = DatabaseConfig()
    
    def get_all_loans(self):
        query = """
            SELECT 
                e.id_emprestimo,
                e.id_usuario,
                u.nome as usuario_nome,
                e.id_objeto,
                o.nome as objeto_nome,
                o.marca,
                e.data_retirada,
                e.data_devolucao_prevista,
                e.data_devolucao_real,
                e.status
            FROM emprestimo e
            JOIN usuario u ON e.id_usuario = u.id_usuario
            JOIN objeto o ON e.id_objeto = o.id_objeto
            ORDER BY e.data_retirada DESC
        """
        return self.db.execute_query(query)
    
    def analyze_overdue_loans(self):
        query = """
            SELECT 
                e.id_emprestimo,
                e.id_usuario,
                u.nome,
                u.email,
                o.nome as objeto,
                e.data_retirada,
                e.data_devolucao_prevista,
                DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso,
                CASE 
                    WHEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 30 THEN 'Crítico'
                    WHEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 14 THEN 'Alto'
                    WHEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 7 THEN 'Médio'
                    ELSE 'Baixo'
                END as nivel_risco
            FROM emprestimo e
            JOIN usuario u ON e.id_usuario = u.id_usuario
            JOIN objeto o ON e.id_objeto = o.id_objeto
            WHERE e.data_devolucao_real IS NULL 
            AND CURDATE() > e.data_devolucao_prevista
            ORDER BY dias_atraso DESC
        """
        return self.db.execute_query(query)
    
    def get_user_statistics(self):
        query = """
            SELECT 
                u.id_usuario,
                u.nome,
                COUNT(e.id_emprestimo) as total_emprestimos,
                SUM(CASE WHEN e.data_devolucao_real IS NULL AND CURDATE() > e.data_devolucao_prevista THEN 1 ELSE 0 END) as emprestimos_atrasados,
                AVG(DATEDIFF(e.data_devolucao_real, e.data_retirada)) as media_dias_emprestimo,
                MAX(e.data_retirada) as ultimo_emprestimo
            FROM usuario u
            LEFT JOIN emprestimo e ON u.id_usuario = e.id_usuario
            GROUP BY u.id_usuario, u.nome
            ORDER BY emprestimos_atrasados DESC
        """
        return self.db.execute_query(query)
    
    def get_most_borrowed_items(self, limit=10):
        query = f"""
            SELECT 
                o.id_objeto,
                o.nome,
                o.marca,
                o.modelo,
                COUNT(e.id_emprestimo) as total_emprestimos,
                SUM(CASE WHEN e.data_devolucao_real IS NULL THEN 1 ELSE 0 END) as emprestimos_ativos
            FROM objeto o
            LEFT JOIN emprestimo e ON o.id_objeto = e.id_objeto
            GROUP BY o.id_objeto, o.nome, o.marca, o.modelo
            ORDER BY total_emprestimos DESC
            LIMIT {limit}
        """
        return self.db.execute_query(query)
    
    def generate_summary_report(self):
        loans = self.get_all_loans()
        overdue = self.analyze_overdue_loans()
        
        if not loans:
            return None
        
        df = pd.DataFrame(loans)
        df['data_retirada'] = pd.to_datetime(df['data_retirada'])
        df['data_devolucao_prevista'] = pd.to_datetime(df['data_devolucao_prevista'])
        
        report = {
            'data_geracao': datetime.now().isoformat(),
            'resumo_geral': {
                'total_emprestimos': len(df),
                'emprestimos_ativos': len(df[df['data_devolucao_real'].isna()]),
                'emprestimos_devolvidos': len(df[df['data_devolucao_real'].notna()]),
                'emprestimos_atrasados': len(overdue) if overdue else 0,
            },
            'emprestimos_atrasados': overdue if overdue else [],
            'usuarios_com_maior_atraso': self.get_user_statistics()[:5] if self.get_user_statistics() else [],
            'objetos_mais_emprestados': self.get_most_borrowed_items(5)
        }
        
        return report
