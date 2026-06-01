import json
from datetime import datetime
from configuracao_bd import ConfiguracaoBD

class ServicoAlertas:
    def __init__(self):
        self.bd = ConfiguracaoBD()
        self.alertas = []
    
    def verificar_atrasos_criticos(self):
        consulta = """
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
        
        resultados = self.bd.executar_consulta(consulta)
        if resultados:
            for item in resultados:
                self.alertas.append({
                    'tipo': 'CRITICO',
                    'prioridade': 1,
                    'emprestimo_id': item.get('id_emprestimo'),
                    'usuario_id': item.get('id_usuario'),
                    'usuario_nome': item.get('nome'),
                    'usuario_email': item.get('email'),
                    'objeto': item.get('objeto'),
                    'dias_atraso': item.get('dias_atraso'),
                    'mensagem': f"CRITICO: {item.get('nome')} deve devolver {item.get('objeto')} ha {item.get('dias_atraso')} dias!",
                    'timestamp': datetime.now().isoformat()
                })
        
        return resultados
    
    def verificar_atrasos_altos(self):
        consulta = """
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
        
        resultados = self.bd.executar_consulta(consulta)
        if resultados:
            for item in resultados:
                self.alertas.append({
                    'tipo': 'ALTO',
                    'prioridade': 2,
                    'emprestimo_id': item.get('id_emprestimo'),
                    'usuario_id': item.get('id_usuario'),
                    'usuario_nome': item.get('nome'),
                    'usuario_email': item.get('email'),
                    'objeto': item.get('objeto'),
                    'dias_atraso': item.get('dias_atraso'),
                    'mensagem': f"AVISO: {item.get('nome')} esta com atraso de {item.get('dias_atraso')} dias",
                    'timestamp': datetime.now().isoformat()
                })
        
        return resultados
    
    def verificar_reincidentes(self):
        consulta = """
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
        self.alertas = []
        self.verificar_atrasos_criticos()
        self.verificar_atrasos_altos()
        self.verificar_reincidentes()
        self.alertas.sort(key=lambda x: x.get('prioridade'))
        return self.alertas
    
    def salvar_alertas(self, caminho_arquivo='alertas.json'):
        with open(caminho_arquivo, 'w', encoding='utf-8') as f:
            json.dump(self.alertas, f, indent=2, ensure_ascii=False, default=str)
        return caminho_arquivo
    
    def exibir_alertas(self):
        if not self.alertas:
            print("Nenhum alerta gerado")
            return
        
        print("\n" + "="*60)
        print("SISTEMA DE ALERTAS - EMPRESTIMOS")
        print("="*60)
        
        for alerta in self.alertas:
            print(f"\n[{alerta.get('tipo')}] {alerta.get('mensagem')}")
        
        print("\n" + "="*60)
        print(f"Total de alertas: {len(self.alertas)}")
        print("="*60)
