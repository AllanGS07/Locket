import logging
from datetime import datetime
import os
from functools import wraps

class Logger:
    def __init__(self, name, log_file='logs/app.log', level=logging.INFO):
        self.logger = logging.getLogger(name)
        self.logger.setLevel(level)
        
        os.makedirs(os.path.dirname(log_file) if os.path.dirname(log_file) else '.', exist_ok=True)
        
        file_handler = logging.FileHandler(log_file)
        file_handler.setLevel(level)
        
        console_handler = logging.StreamHandler()
        console_handler.setLevel(logging.WARNING)
        
        formatter = logging.Formatter(
            '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
        )
        file_handler.setFormatter(formatter)
        console_handler.setFormatter(formatter)
        
        self.logger.addHandler(file_handler)
        self.logger.addHandler(console_handler)
    
    def info(self, message):
        self.logger.info(message)
    
    def warning(self, message):
        self.logger.warning(message)
    
    def error(self, message, exc_info=False):
        self.logger.error(message, exc_info=exc_info)
    
    def debug(self, message):
        self.logger.debug(message)

class APIError(Exception):
    def __init__(self, message, status_code=400, details=None):
        self.message = message
        self.status_code = status_code
        self.details = details or {}
        super().__init__(self.message)

class ValidationError(APIError):
    def __init__(self, message, details=None):
        super().__init__(message, 400, details)

class NotFoundError(APIError):
    def __init__(self, message, details=None):
        super().__init__(message, 404, details)

class ServerError(APIError):
    def __init__(self, message, details=None):
        super().__init__(message, 500, details)

def handle_errors(func):
    @wraps(func)
    def wrapper(*args, **kwargs):
        try:
            return func(*args, **kwargs)
        except ValidationError as e:
            logger.warning(f"Validation error in {func.__name__}: {e.message}")
            return {
                'success': False,
                'status_code': e.status_code,
                'message': e.message,
                'details': e.details,
                'timestamp': datetime.now().isoformat()
            }, e.status_code
        except NotFoundError as e:
            logger.warning(f"Not found error in {func.__name__}: {e.message}")
            return {
                'success': False,
                'status_code': e.status_code,
                'message': e.message,
                'details': e.details,
                'timestamp': datetime.now().isoformat()
            }, e.status_code
        except APIError as e:
            logger.error(f"API error in {func.__name__}: {e.message}", exc_info=True)
            return {
                'success': False,
                'status_code': e.status_code,
                'message': e.message,
                'details': e.details,
                'timestamp': datetime.now().isoformat()
            }, e.status_code
        except Exception as e:
            logger.error(f"Unexpected error in {func.__name__}: {str(e)}", exc_info=True)
            return {
                'success': False,
                'status_code': 500,
                'message': 'Internal server error',
                'timestamp': datetime.now().isoformat()
            }, 500
    
    return wrapper

logger = Logger('locket_api')
