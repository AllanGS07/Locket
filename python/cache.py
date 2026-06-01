from abc import ABC, abstractmethod
from datetime import datetime, timedelta
import json
import hashlib

class ProvedorCache(ABC):
    @abstractmethod
    def obter(self, chave: str):
        pass

    @abstractmethod
    def definir(self, chave: str, valor, ttl: int = 300):
        pass

    @abstractmethod
    def deletar(self, chave: str):
        pass

    @abstractmethod
    def limpar(self):
        pass

class CacheMemoria(ProvedorCache):
    def __init__(self):
        self.armazenamento = {}
        self.expiracao = {}

    def obter(self, chave: str):
        if chave not in self.armazenamento:
            return None
        
        if chave in self.expiracao:
            if datetime.now() > self.expiracao[chave]:
                self.deletar(chave)
                return None
        
        return self.armazenamento[chave]

    def definir(self, chave: str, valor, ttl: int = 300):
        self.armazenamento[chave] = valor
        if ttl > 0:
            self.expiracao[chave] = datetime.now() + timedelta(seconds=ttl)

    def deletar(self, chave: str):
        if chave in self.armazenamento:
            del self.armazenamento[chave]
        if chave in self.expiracao:
            del self.expiracao[chave]

    def limpar(self):
        self.armazenamento.clear()
        self.expiracao.clear()

class CacheRedis(ProvedorCache):
    def __init__(self, host='localhost', porta=6379, db=0):
        self.reserva = CacheMemoria()
        try:
            import redis
            self.cliente = redis.Redis(host=host, port=porta, db=db, decode_responses=True, socket_connect_timeout=5)
            self.cliente.ping()
        except Exception:
            self.cliente = None

    def obter(self, chave: str):
        if self.cliente:
            try:
                valor = self.cliente.get(chave)
                return json.loads(valor) if valor else None
            except Exception:
                return self.reserva.obter(chave)
        return self.reserva.obter(chave)

    def definir(self, chave: str, valor, ttl: int = 300):
        if self.cliente:
            try:
                self.cliente.setex(chave, ttl, json.dumps(valor, default=str))
            except Exception:
                self.reserva.definir(chave, valor, ttl)
        else:
            self.reserva.definir(chave, valor, ttl)

    def deletar(self, chave: str):
        if self.cliente:
            try:
                self.cliente.delete(chave)
            except Exception:
                self.reserva.deletar(chave)
        else:
            self.reserva.deletar(chave)

    def limpar(self):
        if self.cliente:
            try:
                self.cliente.flushdb()
            except Exception:
                self.reserva.limpar()
        else:
            self.reserva.limpar()

def gerar_chave_cache(prefixo: str, *args, **kwargs) -> str:
    partes_chave = [prefixo]
    partes_chave.extend(str(arg) for arg in args)
    partes_chave.extend(f"{k}={v}" for k, v in sorted(kwargs.items()))
    chave_cache = ":".join(partes_chave)
    return chave_cache

def em_cache(ttl: int = 300, prefixo_chave: str = ""):
    def decorador(funcao):
        def invólucro(*args, **kwargs):
            chave_cache = gerar_chave_cache(prefixo_chave or funcao.__name__, *args, **kwargs)
            
            valor_em_cache = cache.obter(chave_cache)
            if valor_em_cache is not None:
                return valor_em_cache
            
            resultado = funcao(*args, **kwargs)
            cache.definir(chave_cache, resultado, ttl)
            return resultado
        
        return invólucro
    return decorador

cache = CacheMemoria()
