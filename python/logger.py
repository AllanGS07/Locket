import logging
from datetime import datetime
import os
from functools import wraps

class Registrador:
    def __init__(self, nome, arquivo_log='logs/app.log', nivel=logging.INFO):
        self.registrador = logging.getLogger(nome)
        self.registrador.setLevel(nivel)
        
        os.makedirs(os.path.dirname(arquivo_log) if os.path.dirname(arquivo_log) else '.', exist_ok=True)
        
        manipulador_arquivo = logging.FileHandler(arquivo_log)
        manipulador_arquivo.setLevel(nivel)
        
        manipulador_console = logging.StreamHandler()
        manipulador_console.setLevel(logging.WARNING)
        
        formatador = logging.Formatter(
            '%(asctime)s - %(name)s - %(levelname)s - %(message)s'
        )
        manipulador_arquivo.setFormatter(formatador)
        manipulador_console.setFormatter(formatador)
        
        self.registrador.addHandler(manipulador_arquivo)
        self.registrador.addHandler(manipulador_console)
    
    def info(self, mensagem):
        self.registrador.info(mensagem)
    
    def aviso(self, mensagem):
        self.registrador.warning(mensagem)
    
    def erro(self, mensagem, info_exc=False):
        self.registrador.error(mensagem, exc_info=info_exc)
    
    def debug(self, mensagem):
        self.registrador.debug(mensagem)

class ErroAPI(Exception):
    def __init__(self, mensagem, codigo_status=400, detalhes=None):
        self.mensagem = mensagem
        self.codigo_status = codigo_status
        self.detalhes = detalhes or {}
        super().__init__(self.mensagem)

class ErroValidacao(ErroAPI):
    def __init__(self, mensagem, detalhes=None):
        super().__init__(mensagem, 400, detalhes)

class ErroNaoEncontrado(ErroAPI):
    def __init__(self, mensagem, detalhes=None):
        super().__init__(mensagem, 404, detalhes)

class ErroServidor(ErroAPI):
    def __init__(self, mensagem, detalhes=None):
        super().__init__(mensagem, 500, detalhes)

def tratar_erros(funcao):
    @wraps(funcao)
    def invólucro(*args, **kwargs):
        try:
            return funcao(*args, **kwargs)
        except ErroValidacao as e:
            registrador.aviso(f"Erro validacao em {funcao.__name__}: {e.mensagem}")
            return {
                'sucesso': False,
                'codigo_status': e.codigo_status,
                'mensagem': e.mensagem,
                'detalhes': e.detalhes,
                'timestamp': datetime.now().isoformat()
            }, e.codigo_status
        except ErroNaoEncontrado as e:
            registrador.aviso(f"Erro nao encontrado em {funcao.__name__}: {e.mensagem}")
            return {
                'sucesso': False,
                'codigo_status': e.codigo_status,
                'mensagem': e.mensagem,
                'detalhes': e.detalhes,
                'timestamp': datetime.now().isoformat()
            }, e.codigo_status
        except ErroAPI as e:
            registrador.erro(f"Erro API em {funcao.__name__}: {e.mensagem}", info_exc=True)
            return {
                'sucesso': False,
                'codigo_status': e.codigo_status,
                'mensagem': e.mensagem,
                'detalhes': e.detalhes,
                'timestamp': datetime.now().isoformat()
            }, e.codigo_status
        except Exception as e:
            registrador.erro(f"Erro inesperado em {funcao.__name__}: {str(e)}", info_exc=True)
            return {
                'sucesso': False,
                'codigo_status': 500,
                'mensagem': 'Erro interno do servidor',
                'timestamp': datetime.now().isoformat()
            }, 500
    
    return invólucro

registrador = Registrador('locket_api')
