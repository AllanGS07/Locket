import mysql.connector
from mysql.connector import Error
import os
from dotenv import load_dotenv

load_dotenv()

class ConfiguracaoBD:
    def __init__(self):
        self.host = os.getenv('BD_HOST', 'localhost')
        self.usuario = os.getenv('BD_USUARIO', 'root')
        self.senha = os.getenv('BD_SENHA', '')
        self.banco = os.getenv('BD_NOME', 'locket_db')
        self.porta = int(os.getenv('BD_PORTA', 3306))
    
    def obter_conexao(self):
        try:
            conexao = mysql.connector.connect(
                host=self.host,
                user=self.usuario,
                password=self.senha,
                database=self.banco,
                port=self.porta
            )
            if conexao.is_connected():
                return conexao
        except Error as e:
            print(f"Erro ao conectar ao banco: {e}")
            return None
    
    def executar_consulta(self, consulta, parametros=None):
        conexao = self.obter_conexao()
        if not conexao:
            return None
        
        try:
            cursor = conexao.cursor(dictionary=True)
            if parametros:
                cursor.execute(consulta, parametros)
            else:
                cursor.execute(consulta)
            
            if consulta.strip().upper().startswith('SELECT'):
                return cursor.fetchall()
            else:
                conexao.commit()
                return cursor.rowcount
        except Error as e:
            print(f"Erro ao executar query: {e}")
            return None
        finally:
            cursor.close()
            conexao.close()
