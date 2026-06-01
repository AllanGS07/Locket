from api import app
from configuracao import Configuracao
from logger import registrador
import os

if __name__ == '__main__':
    registrador.info('Iniciando API Locket')
    registrador.info(f'Ambiente: {Configuracao.APP_ENV}')
    registrador.info(f'Banco de dados: {Configuracao.BD_HOST}:{Configuracao.BD_PORTA}/{Configuracao.BD_NOME}')
    
    app.run(
        host=Configuracao.FLASK_HOST,
        port=Configuracao.FLASK_PORTA,
        debug=Configuracao.FLASK_DEBUG
    )
