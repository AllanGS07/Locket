import os
from dotenv import load_dotenv

load_dotenv()

class Configuracao:
    BD_HOST = os.getenv('BD_HOST', 'localhost')
    BD_USUARIO = os.getenv('BD_USUARIO', 'root')
    BD_SENHA = os.getenv('BD_SENHA', '')
    BD_NOME = os.getenv('BD_NOME', 'locket_db')
    BD_PORTA = int(os.getenv('BD_PORTA', 3306))
    
    FLASK_HOST = os.getenv('FLASK_HOST', '0.0.0.0')
    FLASK_PORTA = int(os.getenv('FLASK_PORTA', 5000))
    FLASK_DEBUG = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    
    REDIS_HOST = os.getenv('REDIS_HOST', 'localhost')
    REDIS_PORTA = int(os.getenv('REDIS_PORTA', 6379))
    REDIS_DB = int(os.getenv('REDIS_DB', 0))
    
    CACHE_TTL_CURTO = int(os.getenv('CACHE_TTL_CURTO', 60))
    CACHE_TTL_MEDIO = int(os.getenv('CACHE_TTL_MEDIO', 300))
    CACHE_TTL_LONGO = int(os.getenv('CACHE_TTL_LONGO', 600))

    APP_ENV = os.getenv('APP_ENV', 'producao')
    LOG_NIVEL = os.getenv('LOG_NIVEL', 'erro').lower()
    
    CAMINHO_MODELO = os.getenv('CAMINHO_MODELO', 'models/delay_model.pkl')
    DIRETORIO_RELATORIOS = os.getenv('DIRETORIO_RELATORIOS', 'reports')
    
    @staticmethod
    def obter_configuracao_bd():
        return {
            'host': Configuracao.BD_HOST,
            'user': Configuracao.BD_USUARIO,
            'password': Configuracao.BD_SENHA,
            'database': Configuracao.BD_NOME,
            'port': Configuracao.BD_PORTA
        }
    
    @staticmethod
    def obter_configuracao_cache():
        return {
            'host': Configuracao.REDIS_HOST,
            'port': Configuracao.REDIS_PORTA,
            'db': Configuracao.REDIS_DB
        }
