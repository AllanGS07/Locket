from abc import ABC, abstractmethod
from datetime import datetime, timedelta
import json
import hashlib

class CacheProvider(ABC):
    @abstractmethod
    def get(self, key: str):
        pass

    @abstractmethod
    def set(self, key: str, value, ttl: int = 300):
        pass

    @abstractmethod
    def delete(self, key: str):
        pass

    @abstractmethod
    def clear(self):
        pass

class InMemoryCache(CacheProvider):
    def __init__(self):
        self.store = {}
        self.expiry = {}

    def get(self, key: str):
        if key not in self.store:
            return None
        
        if key in self.expiry:
            if datetime.now() > self.expiry[key]:
                self.delete(key)
                return None
        
        return self.store[key]

    def set(self, key: str, value, ttl: int = 300):
        self.store[key] = value
        if ttl > 0:
            self.expiry[key] = datetime.now() + timedelta(seconds=ttl)

    def delete(self, key: str):
        if key in self.store:
            del self.store[key]
        if key in self.expiry:
            del self.expiry[key]

    def clear(self):
        self.store.clear()
        self.expiry.clear()

class RedisCache(CacheProvider):
    def __init__(self, host='localhost', port=6379, db=0):
        try:
            import redis
            self.client = redis.Redis(host=host, port=port, db=db, decode_responses=True)
            self.client.ping()
        except Exception as e:
            print(f"Redis connection failed: {e}. Falling back to InMemoryCache")
            self.fallback = InMemoryCache()
            self.client = None

    def get(self, key: str):
        if self.client:
            try:
                value = self.client.get(key)
                return json.loads(value) if value else None
            except Exception as e:
                print(f"Redis get error: {e}")
                return self.fallback.get(key) if hasattr(self, 'fallback') else None
        return self.fallback.get(key) if hasattr(self, 'fallback') else None

    def set(self, key: str, value, ttl: int = 300):
        if self.client:
            try:
                self.client.setex(key, ttl, json.dumps(value, default=str))
            except Exception as e:
                print(f"Redis set error: {e}")
                if hasattr(self, 'fallback'):
                    self.fallback.set(key, value, ttl)
        elif hasattr(self, 'fallback'):
            self.fallback.set(key, value, ttl)

    def delete(self, key: str):
        if self.client:
            try:
                self.client.delete(key)
            except Exception as e:
                print(f"Redis delete error: {e}")
                if hasattr(self, 'fallback'):
                    self.fallback.delete(key)
        elif hasattr(self, 'fallback'):
            self.fallback.delete(key)

    def clear(self):
        if self.client:
            try:
                self.client.flushdb()
            except Exception as e:
                print(f"Redis clear error: {e}")
                if hasattr(self, 'fallback'):
                    self.fallback.clear()
        elif hasattr(self, 'fallback'):
            self.fallback.clear()

def generate_cache_key(prefix: str, *args, **kwargs) -> str:
    key_parts = [prefix]
    key_parts.extend(str(arg) for arg in args)
    key_parts.extend(f"{k}={v}" for k, v in sorted(kwargs.items()))
    cache_key = ":".join(key_parts)
    return cache_key

def cached(ttl: int = 300, key_prefix: str = ""):
    def decorator(func):
        def wrapper(*args, **kwargs):
            cache_key = generate_cache_key(key_prefix or func.__name__, *args, **kwargs)
            
            cached_value = cache.get(cache_key)
            if cached_value is not None:
                return cached_value
            
            result = func(*args, **kwargs)
            cache.set(cache_key, result, ttl)
            return result
        
        return wrapper
    return decorator

cache = InMemoryCache()
