from datetime import datetime
from configuracao_bd import ConfiguracaoBD

class AnalisadorEmprestimos:
    def __init__(self):
        self.bd = ConfiguracaoBD()
    
    def obter_todos_emprestimos(self):
        consulta = """
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
        return self.bd.executar_consulta(consulta)
    
    def analisar_emprestimos_atrasados(self):
        consulta = """
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
                    WHEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 30 THEN 'Critico'
                    WHEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 14 THEN 'Alto'
                    WHEN DATEDIFF(CURDATE(), e.data_devolucao_prevista) > 7 THEN 'Medio'
                    ELSE 'Baixo'
                END as nivel_risco
            FROM emprestimo e
            JOIN usuario u ON e.id_usuario = u.id_usuario
            JOIN objeto o ON e.id_objeto = o.id_objeto
            WHERE e.data_devolucao_real IS NULL 
            AND CURDATE() > e.data_devolucao_prevista
            ORDER BY dias_atraso DESC
        """
        return self.bd.executar_consulta(consulta)
    
    def obter_estatisticas_usuario(self):
        consulta = """
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
        return self.bd.executar_consulta(consulta)
    
    def obter_objetos_mais_emprestados(self, limite=10):
        consulta = f"""
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
            LIMIT {limite}
        """
        return self.bd.executar_consulta(consulta)
    
    def gerar_relatorio_resumido(self):
        emprestimos = self.obter_todos_emprestimos()
        atrasados = self.analisar_emprestimos_atrasados()

        if not emprestimos:
            return None

        total_emprestimos = len(emprestimos)
        emprestimos_ativos = sum(1 for e in emprestimos if not e.get('data_devolucao_real'))
        emprestimos_devolvidos = total_emprestimos - emprestimos_ativos
        emprestimos_atrasados = len(atrasados) if atrasados else 0

        relatorio = {
            'data_geracao': datetime.now().isoformat(),
            'resumo_geral': {
                'total_emprestimos': total_emprestimos,
                'emprestimos_ativos': emprestimos_ativos,
                'emprestimos_devolvidos': emprestimos_devolvidos,
                'emprestimos_atrasados': emprestimos_atrasados,
            },
            'emprestimos_atrasados': atrasados if atrasados else [],
            'usuarios_com_maior_atraso': self.obter_estatisticas_usuario()[:5] if self.obter_estatisticas_usuario() else [],
            'objetos_mais_emprestados': self.obter_objetos_mais_emprestados(5)
        }

        return relatorio
