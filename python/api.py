from flask import Flask, jsonify, request
from flask_cors import CORS
import os
from dotenv import load_dotenv
from datetime import datetime
from functools import wraps
from analisador_emprestimos import AnalisadorEmprestimos
from preditor_atraso import PreditorAtraso
from servico_alertas import ServicoAlertas
from gerador_relatorios import GeradorRelatorios
from cache import cache, gerar_chave_cache
from logger import registrador, tratar_erros
from configuracao import Configuracao

load_dotenv()

app = Flask(__name__)
CORS(app, origins=['http://localhost:3000', 'http://localhost:8000', 'http://localhost', 'http://127.0.0.1'])

analisador = AnalisadorEmprestimos()
servico_alertas = ServicoAlertas()
preditor = PreditorAtraso()
gerador = GeradorRelatorios()

def criar_resposta(dados, codigo_status=200, mensagem="Sucesso", em_cache=False):
    resposta = {
        'sucesso': codigo_status < 400,
        'codigo_status': codigo_status,
        'mensagem': mensagem,
        'dados': dados,
        'timestamp': datetime.now().isoformat()
    }
    if em_cache:
        resposta['em_cache'] = True
    return jsonify(resposta), codigo_status

def endpoint_em_cache(ttl=300):
    def decorador(funcao):
        @wraps(funcao)
        def invólucro(*args, **kwargs):
            chave_cache = gerar_chave_cache(funcao.__name__, request.path, request.args.to_dict())
            
            resultado_em_cache = cache.obter(chave_cache)
            if resultado_em_cache is not None:
                resultado = resultado_em_cache
                if isinstance(resultado, tuple):
                    return resultado[0], resultado[1]
                return resultado
            
            resultado = funcao(*args, **kwargs)
            if isinstance(resultado, tuple):
                cache.definir(chave_cache, resultado, ttl)
            return resultado
        
        return invólucro
    return decorador

def validar_json_request(*parametros_obrigatorios):
    def decorador(funcao):
        @wraps(funcao)
        def invólucro(*args, **kwargs):
            dados = request.get_json() or {}
            for param in parametros_obrigatorios:
                if param not in dados:
                    return criar_resposta(None, 400, f'Parametro obrigatorio ausente: {param}')
            return funcao(*args, **kwargs)
        return invólucro
    return decorador

@app.route('/api/emprestimos', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_LONGO)
@tratar_erros
def obter_todos_emprestimos():
    registrador.info('Buscando todos os emprestimos')
    emprestimos = analisador.obter_todos_emprestimos()
    return criar_resposta(emprestimos or [])

@app.route('/api/emprestimos/atrasados', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_MEDIO)
@tratar_erros
def obter_emprestimos_atrasados():
    registrador.info('Buscando emprestimos atrasados')
    atrasados = analisador.analisar_emprestimos_atrasados()
    return criar_resposta(atrasados or [])

@app.route('/api/emprestimos/resumo', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_LONGO)
@tratar_erros
def obter_resumo_emprestimos():
    registrador.info('Gerando resumo de emprestimos')
    relatorio = analisador.gerar_relatorio_resumido()
    return criar_resposta(relatorio or {})

@app.route('/api/usuarios/estatisticas', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_LONGO)
@tratar_erros
def obter_estatisticas_usuarios():
    registrador.info('Buscando estatisticas de usuarios')
    stats = analisador.obter_estatisticas_usuario()
    return criar_resposta(stats or [])

@app.route('/api/objetos/mais-emprestados', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_LONGO)
@tratar_erros
def obter_objetos_mais_emprestados():
    registrador.info('Buscando objetos mais emprestados')
    limite = request.args.get('limite', 10, type=int)
    objetos = analisador.obter_objetos_mais_emprestados(limite)
    return criar_resposta(objetos or [])

@app.route('/api/alertas', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_MEDIO)
@tratar_erros
def obter_alertas():
    registrador.info('Gerando alertas')
    alertas = servico_alertas.gerar_alertas()
    return criar_resposta(alertas or [])

@app.route('/api/alertas/criticos', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_MEDIO)
@tratar_erros
def obter_alertas_criticos():
    registrador.info('Buscando alertas criticos')
    alertas = servico_alertas.gerar_alertas()
    criticos = [a for a in alertas if a.get('tipo') == 'CRITICO']
    return criar_resposta(criticos or [])

@app.route('/api/predicoes/emprestimo', methods=['POST'])
@validar_json_request('id_usuario', 'dias_duracao', 'mes_retirada')
@tratar_erros
def prever_atraso_emprestimo():
    registrador.info('Realizando predicao de atraso')
    dados = request.get_json()
    predicao = preditor.prever_emprestimo(
        id_usuario=dados.get('id_usuario'),
        dias_duracao=dados.get('dias_duracao'),
        mes_retirada=dados.get('mes_retirada'),
        dia_semana=dados.get('dia_semana', 0),
        historico_emprestimos=dados.get('historico_emprestimos', 0),
        historico_atrasos=dados.get('historico_atrasos', 0),
        taxa_atraso=dados.get('taxa_atraso', 0.0),
        funcao=dados.get('funcao', 'ALUNO')
    )
    return criar_resposta(predicao or {})

@app.route('/api/modelo/treinar', methods=['POST'])
@tratar_erros
def treinar_modelo():
    registrador.info('Treinando modelo de predicao')
    sucesso = preditor.treinar_modelo()
    analisador.limpar_cache()
    servico_alertas.limpar_cache() if hasattr(servico_alertas, 'limpar_cache') else None
    return criar_resposta(
        {'treinado': sucesso},
        200 if sucesso else 500,
        'Modelo treinado com sucesso' if sucesso else 'Falha ao treinar modelo'
    )

@app.route('/api/saude', methods=['GET'])
@endpoint_em_cache(ttl=60)
@tratar_erros
def verificacao_saude():
    registrador.info('Verificacao saude solicitada')
    return criar_resposta({'status': 'saudavel'})

@app.route('/api/relatorios/json', methods=['GET'])
@tratar_erros
def gerar_relatorio_json():
    registrador.info('Gerando relatorio JSON')
    caminho_arquivo = gerador.gerar_relatorio_json()
    with open(caminho_arquivo, 'r', encoding='utf-8') as f:
        import json
        relatorio = json.load(f)
    return criar_resposta(relatorio)

@app.route('/api/relatorios/csv', methods=['GET'])
@tratar_erros
def gerar_relatorio_csv():
    registrador.info('Gerando relatorio CSV')
    caminho_arquivo = gerador.gerar_relatorio_csv()
    return criar_resposta({'caminho_arquivo': caminho_arquivo, 'mensagem': 'Relatorio CSV gerado'})

@app.route('/api/relatorios/previsoes', methods=['GET'])
@tratar_erros
def gerar_relatorio_previsoes():
    registrador.info('Gerando relatorio de previsoes')
    caminho_arquivo = gerador.gerar_relatorio_previsoes()
    with open(caminho_arquivo, 'r', encoding='utf-8') as f:
        import json
        relatorio = json.load(f)
    return criar_resposta(relatorio)

@app.route('/api/analise/completa', methods=['GET'])
@tratar_erros
def analise_completa():
    registrador.info('Executando analise completa')
    gerador.gerar_relatorio_json()
    gerador.gerar_relatorio_csv()
    gerador.gerar_relatorio_atrasos()
    
    alertas = servico_alertas.gerar_alertas()
    servico_alertas.salvar_alertas('relatorios/alertas.json')
    
    resumo = analisador.gerar_relatorio_resumido()

    return criar_resposta({
        'resumo': resumo,
        'alertas': alertas
    })

@app.route('/api/cache/limpar', methods=['POST'])
@tratar_erros
def limpar_cache():
    registrador.info('Cache limpo')
    cache.limpar()
    analisador.limpar_cache()
    if hasattr(servico_alertas, 'limpar_cache'):
        servico_alertas.limpar_cache()
    return criar_resposta({'limpo': True}, mensagem='Cache limpo com sucesso')

@app.route('/api/status', methods=['GET'])
@endpoint_em_cache(ttl=Configuracao.CACHE_TTL_CURTO)
@tratar_erros
def obter_status():
    registrador.info('Obtendo status do sistema')
    relatorio = analisador.gerar_relatorio_resumido()
    
    if not relatorio:
        return criar_resposta({'status': 'Sem dados'}, 200)
    
    return criar_resposta({
        'total_emprestimos': relatorio.get('resumo_geral', {}).get('total_emprestimos'),
        'emprestimos_ativos': relatorio.get('resumo_geral', {}).get('emprestimos_ativos'),
        'emprestimos_atrasados': relatorio.get('resumo_geral', {}).get('emprestimos_atrasados'),
        'data_geracao': relatorio.get('data_geracao')
    })

@app.errorhandler(404)
def nao_encontrado(erro):
    registrador.aviso(f'Erro 404: {request.path}')
    return criar_resposta(None, 404, 'Endpoint nao encontrado')

@app.errorhandler(500)
def erro_interno(erro):
    registrador.erro(f'Erro 500: {str(erro)}', info_exc=True)
    return criar_resposta(None, 500, 'Erro interno do servidor')

if __name__ == '__main__':
    app.run(
        host=Configuracao.FLASK_HOST,
        port=Configuracao.FLASK_PORTA,
        debug=Configuracao.FLASK_DEBUG
    )
