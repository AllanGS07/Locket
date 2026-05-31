"""
Configuração de conexão com MySQL (XAMPP)
"""
import mysql.connector
from mysql.connector import Error
import os
from dotenv import load_dotenv

load_dotenv()

class DatabaseConfig:
    """Gerencia conexão com banco MySQL"""
    
    def __init__(self):
        self.host = os.getenv('DB_HOST', 'localhost')
        self.user = os.getenv('DB_USER', 'root')
        self.password = os.getenv('DB_PASSWORD', '')
        self.database = os.getenv('DB_NAME', 'locket_db')
        self.port = int(os.getenv('DB_PORT', 3306))
    
    def get_connection(self):
        """Estabelece conexão com banco de dados"""
        try:
            conn = mysql.connector.connect(
                host=self.host,
                user=self.user,
                password=self.password,
                database=self.database,
                port=self.port
            )
            if conn.is_connected():
                return conn
        except Error as e:
            print(f"Erro ao conectar ao banco: {e}")
            return None
    
    def execute_query(self, query, params=None):
        """Executa query e retorna resultados"""
        conn = self.get_connection()
        if not conn:
            return None
        
        try:
            cursor = conn.cursor(dictionary=True)
            if params:
                cursor.execute(query, params)
            else:
                cursor.execute(query)
            
            # Verifica se é uma query SELECT
            if query.strip().upper().startswith('SELECT'):
                return cursor.fetchall()
            else:
                conn.commit()
                return cursor.rowcount
        except Error as e:
            print(f"Erro ao executar query: {e}")
            return None
        finally:
            cursor.close()
            conn.close()
