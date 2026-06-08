from datetime import datetime
from configuracao_bd import ConfiguracaoBD

class AnalisadorEmprestimos:
    def __init__(self):
        self.bd = ConfiguracaoBD()
        self._cache_emprestimos = None
        self._cache_atrasados = None
        self._cache_stats = None
    
    def obter_historico_usuario(self, id_usuario):
        consulta = """
            SELECT 
                COUNT(e.ID_Emprestimo) as total,
                SUM(CASE WHEN e.Data_Devolucao_Real IS NOT NULL 
                         AND e.Data_Devolucao_Real > e.Data_Devolucao_Prevista 
                    THEN 1 ELSE 0 END) as atrasos
            FROM Emprestimos e
            WHERE e.ID_Usuario = %s
        """
        resultado = self.bd.executar_consulta(consulta, (id_usuario,))
        if resultado and resultado[0]:
            total = resultado[0].get('total') or 0
            atrasos = resultado[0].get('atrasos') or 0
            taxa = (atrasos / total * 100) if total > 0 else 0.0
            return {
                'total_emprestimos': total,
                'total_atrasos': atrasos,
                'taxa_atraso': taxa
            }
        return {
            'total_emprestimos': 0,
            'total_atrasos': 0,
            'taxa_atraso': 0.0
        }
    def obter_todos_emprestimos(self):
        if self._cache_emprestimos is not None:
            return self._cache_emprestimos
        
        consulta = """
            SELECT 
                e.ID_Emprestimo,
                e.ID_Usuario,
                u.Nome as usuario_nome,
                e.ID_Objeto,
                o.Nome as objeto_nome,
                o.Marca,
                e.Data_Retirada,
                e.Data_Devolucao_Prevista,
                e.Data_Devolucao_Real,
                e.Status_Emprestimo as status
            FROM Emprestimos e
            JOIN Usuario u ON e.ID_Usuario = u.ID_Usuario
            JOIN Objeto o ON e.ID_Objeto = o.ID_Objeto
            ORDER BY e.Data_Retirada DESC
        """
        self._cache_emprestimos = self.bd.executar_consulta(consulta)
        return self._cache_emprestimos
    
    def limpar_cache(self):
        self._cache_emprestimos = None
        self._cache_atrasados = None
        self._cache_stats = None
    
    def analisar_emprestimos_atrasados(self):
        if self._cache_atrasados is not None:
            return self._cache_atrasados
        
        consulta = """
            SELECT 
                e.ID_Emprestimo,
                e.ID_Usuario,
                u.Nome,
                u.Email,
                o.Nome as objeto,
                e.Data_Retirada,
                e.Data_Devolucao_Prevista,
                DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) as dias_atraso,
                CASE 
                    WHEN DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) > 30 THEN 'Critico'
                    WHEN DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) > 14 THEN 'Alto'
                    WHEN DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) > 7 THEN 'Medio'
                    ELSE 'Baixo'
                END as nivel_risco
            FROM Emprestimos e
            JOIN Usuario u ON e.ID_Usuario = u.ID_Usuario
            JOIN Objeto o ON e.ID_Objeto = o.ID_Objeto
            WHERE e.Data_Devolucao_Real IS NULL 
            AND CURDATE() > e.Data_Devolucao_Prevista
            ORDER BY dias_atraso DESC
        """
        self._cache_atrasados = self.bd.executar_consulta(consulta)
        return self._cache_atrasados
    
    def obter_estatisticas_usuario(self):
        if self._cache_stats is not None:
            return self._cache_stats
        
        consulta = """
            SELECT 
                u.ID_Usuario,
                u.Nome,
                COUNT(e.ID_Emprestimo) as total_emprestimos,
                SUM(CASE WHEN e.Data_Devolucao_Real IS NULL AND CURDATE() > e.Data_Devolucao_Prevista THEN 1 ELSE 0 END) as emprestimos_atrasados,
                AVG(DATEDIFF(e.Data_Devolucao_Real, e.Data_Retirada)) as media_dias_emprestimo,
                MAX(e.Data_Retirada) as ultimo_emprestimo
            FROM Usuario u
            LEFT JOIN Emprestimos e ON u.ID_Usuario = e.ID_Usuario
            GROUP BY u.ID_Usuario, u.Nome
            ORDER BY emprestimos_atrasados DESC
        """
        self._cache_stats = self.bd.executar_consulta(consulta)
        return self._cache_stats
    
    def obter_objetos_mais_emprestados(self, limite=10):
        limite_seguro = max(1, min(int(limite), 100))
        
        consulta = """
            SELECT 
                o.ID_Objeto,
                o.Nome,
                o.Marca,
                o.Modelo,
                COUNT(e.ID_Emprestimo) as total_emprestimos,
                SUM(CASE WHEN e.Data_Devolucao_Real IS NULL THEN 1 ELSE 0 END) as emprestimos_ativos
            FROM Objeto o
            LEFT JOIN Emprestimos e ON o.ID_Objeto = e.ID_Objeto
            GROUP BY o.ID_Objeto, o.Nome, o.Marca, o.Modelo
            ORDER BY total_emprestimos DESC
            LIMIT %s
        """
        return self.bd.executar_consulta(consulta, (limite_seguro,))
    
    def gerar_relatorio_resumido(self):
        emprestimos = self.obter_todos_emprestimos()
        atrasados = self.analisar_emprestimos_atrasados()
        stats_usuarios = self.obter_estatistica_usuario()

        if not emprestimos:
            return None

        total_emprestimos = len(emprestimos)
        emprestimos_ativos = sum(1 for e in emprestimos if not e.get('Data_Devolucao_Real'))
        emprestimos_devolvidos = total_emprestimos - emprestimos_ativos
        count_atrasados = len(atrasados) if atrasados else 0

        relatorio = {
            'data_geracao': datetime.now().isoformat(),
            'resumo_geral': {
                'total_emprestimos': total_emprestimos,
                'emprestimos_ativos': emprestimos_ativos,
                'emprestimos_devolvidos': emprestimos_devolvidos,
                'emprestimos_atrasados': count_atrasados,
            },
            'emprestimos_atrasados': atrasados or [],
            'usuarios_com_maior_atraso': stats_usuarios[:5] if stats_usuarios else [],
            'objetos_mais_emprestados': self.obter_objetos_mais_emprestados(5)
        }

        return relatorio
