import json
from datetime import datetime
from config_db import DatabaseConfig

class AlertService:
    def __init__(self):
        self.db = DatabaseConfig()
        self.alerts = []
    
    def check_critical_delays(self):
        query = """
            SELECT 
                e.id_emprestimo,
                u.id_usuario,
                u.nome,
                u.email,
                o.nome as objeto,
                DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso
            FROM emprestimo e
            JOIN usuario u ON e.id_usuario = u.id_usuario
            JOIN objeto o ON e.id_objeto = o.id_objeto
            WHERE e.data_devolucao_real IS NULL 
            AND DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 30
            ORDER BY dias_atraso DESC
        """
        
        results = self.db.execute_query(query)
        if results:
            for item in results:
                self.alerts.append({
                    'tipo': 'CRÍTICO',
                    'prioridade': 1,
                    'emprestimo_id': item['id_emprestimo'],
                    'usuario_id': item['id_usuario'],
                    'usuario_nome': item['nome'],
                    'usuario_email': item['email'],
                    'objeto': item['objeto'],
                    'dias_atraso': item['dias_atraso'],
                    'mensagem': f"CRÍTICO: {item['nome']} deve devolver {item['objeto']} há {item['dias_atraso']} dias!",
                    'timestamp': datetime.now().isoformat()
                })
        
        return results
    
    def check_high_delays(self):
        query = """
            SELECT 
                e.id_emprestimo,
                u.id_usuario,
                u.nome,
                u.email,
                o.nome as objeto,
                DATEDIFF(CURDATE(), e.data_devolucao_prevista) as dias_atraso
            FROM emprestimo e
            JOIN usuario u ON e.id_usuario = u.id_usuario
            JOIN objeto o ON e.id_objeto = o.id_objeto
            WHERE e.data_devolucao_real IS NULL 
            AND DATEDIFF(CURDATE(), e.data_devolucao_prevista) BETWEEN 14 AND 30
        """
        
        results = self.db.execute_query(query)
        if results:
            for item in results:
                self.alerts.append({
                    'tipo': 'ALTO',
                    'prioridade': 2,
                    'emprestimo_id': item['id_emprestimo'],
                    'usuario_id': item['id_usuario'],
                    'usuario_nome': item['nome'],
                    'usuario_email': item['email'],
                    'objeto': item['objeto'],
                    'dias_atraso': item['dias_atraso'],
                    'mensagem': f"AVISO: {item['nome']} está com atraso de {item['dias_atraso']} dias",
                    'timestamp': datetime.now().isoformat()
                })
        
        return results
    
    def check_habitual_offenders(self):
        query = """
            SELECT 
                u.id_usuario,
                u.nome,
                u.email,
                COUNT(e.id_emprestimo) as total_emprestimos,
                SUM(CASE WHEN e.data_devolucao_real IS NULL AND CURDATE() > e.data_devolucao_prevista THEN 1 ELSE 0 END) as atrasos_atuais,
                SUM(CASE WHEN e.data_devolucao_real IS NOT NULL AND e.data_devolucao_real > e.data_devolucao_prevista THEN 1 ELSE 0 END) as atrasos_historicos,
                ROUND((SUM(CASE WHEN e.data_devolucao_real IS NOT NULL AND e.data_devolucao_real > e.data_devolucao_prevista THEN 1 ELSE 0 END) / COUNT(e.id_emprestimo)) * 100, 2) as taxa_atraso_percentual
            FROM usuario u
            JOIN emprestimo e ON u.id_usuario = e.id_usuario
            GROUP BY u.id_usuario, u.nome, u.email
            HAVING (SUM(CASE WHEN e.data_devolucao_real IS NOT NULL AND e.data_devolucao_real > e.data_devolucao_prevista THEN 1 ELSE 0 END) / COUNT(e.id_emprestimo)) > 0.3
            ORDER BY taxa_atraso_percentual DESC
        """
        
        results = self.db.execute_query(query)
        if results:
            for item in results:
                self.alerts.append({
                    'tipo': 'REINCIDENTE',
                    'prioridade': 3,
                    'usuario_id': item['id_usuario'],
                    'usuario_nome': item['nome'],
                    'usuario_email': item['email'],
                    'atrasos_historicos': item['atrasos_historicos'],
                    'taxa_atraso': item['taxa_atraso_percentual'],
                    'mensagem': f"ALERTA: {item['nome']} tem {item['taxa_atraso_percentual']}% de atrasos históricos",
                    'timestamp': datetime.now().isoformat()
                })
        
        return results
    
    def generate_alerts(self):
        self.alerts = []
        self.check_critical_delays()
        self.check_high_delays()
        self.check_habitual_offenders()
        self.alerts.sort(key=lambda x: x['prioridade'])
        return self.alerts
    
    def save_alerts(self, filepath='alerts.json'):
        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(self.alerts, f, indent=2, ensure_ascii=False, default=str)
        return filepath
    
    def print_alerts(self):
        if not self.alerts:
            print("Nenhum alerta gerado")
            return
        
        print("\n" + "="*60)
        print("SISTEMA DE ALERTAS - EMPRÉSTIMOS")
        print("="*60)
        
        for alert in self.alerts:
            print(f"\n[{alert['tipo']}] {alert['mensagem']}")
        
        print("\n" + "="*60)
        print(f"Total de alertas: {len(self.alerts)}")
        print("="*60)
