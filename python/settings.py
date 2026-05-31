import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    DB_HOST = os.getenv('DB_HOST', 'localhost')
    DB_USER = os.getenv('DB_USER', 'root')
    DB_PASSWORD = os.getenv('DB_PASSWORD', '')
    DB_NAME = os.getenv('DB_NAME', 'locket_db')
    DB_PORT = int(os.getenv('DB_PORT', 3306))
    
    FLASK_HOST = os.getenv('FLASK_HOST', '0.0.0.0')
    FLASK_PORT = int(os.getenv('FLASK_PORT', 5000))
    FLASK_DEBUG = os.getenv('FLASK_DEBUG', 'False').lower() == 'true'
    
    REDIS_HOST = os.getenv('REDIS_HOST', 'localhost')
    REDIS_PORT = int(os.getenv('REDIS_PORT', 6379))
    REDIS_DB = int(os.getenv('REDIS_DB', 0))
    
    CACHE_TTL_SHORT = int(os.getenv('CACHE_TTL_SHORT', 60))
    CACHE_TTL_MEDIUM = int(os.getenv('CACHE_TTL_MEDIUM', 300))
    CACHE_TTL_LONG = int(os.getenv('CACHE_TTL_LONG', 600))
    
    MODEL_PATH = os.getenv('MODEL_PATH', 'models/delay_model.pkl')
    REPORTS_DIR = os.getenv('REPORTS_DIR', 'reports')
    
    @staticmethod
    def get_db_config():
        return {
            'host': Config.DB_HOST,
            'user': Config.DB_USER,
            'password': Config.DB_PASSWORD,
            'database': Config.DB_NAME,
            'port': Config.DB_PORT
        }
    
    @staticmethod
    def get_cache_config():
        return {
            'host': Config.REDIS_HOST,
            'port': Config.REDIS_PORT,
            'db': Config.REDIS_DB
        }
