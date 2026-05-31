from flask import Flask, jsonify, request
from flask_cors import CORS
import os
from dotenv import load_dotenv
from datetime import datetime
from functools import wraps
from loan_analyzer import LoanAnalyzer
from delay_predictor import DelayPredictor
from alert_service import AlertService
from report_generator import ReportGenerator
from cache import cache, generate_cache_key
from logger import logger, handle_errors
from settings import Config

load_dotenv()

app = Flask(__name__)
CORS(app, origins=['http://localhost:3000', 'http://localhost:8000', 'http://localhost', 'http://127.0.0.1'])

def create_response(data, status_code=200, message="Success", cached=False):
    response = {
        'success': status_code < 400,
        'status_code': status_code,
        'message': message,
        'data': data,
        'timestamp': datetime.now().isoformat()
    }
    if cached:
        response['cached'] = True
    return jsonify(response), status_code

def cached_endpoint(ttl=300):
    def decorator(func):
        @wraps(func)
        def wrapper(*args, **kwargs):
            cache_key = generate_cache_key(func.__name__, request.path, request.args.to_dict())
            
            cached_result = cache.get(cache_key)
            if cached_result is not None:
                result = cached_result
                if isinstance(result, tuple):
                    return result[0], result[1]
                return result
            
            result = func(*args, **kwargs)
            if isinstance(result, tuple):
                cache.set(cache_key, result, ttl)
            return result
        
        return wrapper
    return decorator

@app.route('/api/health', methods=['GET'])
@cached_endpoint(ttl=60)
@handle_errors
def health_check():
    logger.info('Health check requested')
    return create_response({'status': 'healthy'})

@app.route('/api/loans', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_LONG)
@handle_errors
def get_all_loans():
    logger.info('Fetching all loans')
    analyzer = LoanAnalyzer()
    loans = analyzer.get_all_loans()
    return create_response(loans or [])

@app.route('/api/loans/overdue', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_MEDIUM)
@handle_errors
def get_overdue_loans():
    logger.info('Fetching overdue loans')
    analyzer = LoanAnalyzer()
    overdue = analyzer.analyze_overdue_loans()
    return create_response(overdue or [])

@app.route('/api/loans/summary', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_LONG)
@handle_errors
def get_loans_summary():
    logger.info('Generating loans summary')
    analyzer = LoanAnalyzer()
    report = analyzer.generate_summary_report()
    return create_response(report or {})

@app.route('/api/users/statistics', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_LONG)
@handle_errors
def get_user_statistics():
    logger.info('Fetching user statistics')
    analyzer = LoanAnalyzer()
    stats = analyzer.get_user_statistics()
    return create_response(stats or [])

@app.route('/api/items/most-borrowed', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_LONG)
@handle_errors
def get_most_borrowed_items():
    logger.info('Fetching most borrowed items')
    limit = request.args.get('limit', 10, type=int)
    analyzer = LoanAnalyzer()
    items = analyzer.get_most_borrowed_items(limit)
    return create_response(items or [])

@app.route('/api/alerts', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_MEDIUM)
@handle_errors
def get_alerts():
    logger.info('Generating alerts')
    service = AlertService()
    alerts = service.generate_alerts()
    return create_response(alerts or [])

@app.route('/api/alerts/critical', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_MEDIUM)
@handle_errors
def get_critical_alerts():
    logger.info('Fetching critical alerts')
    service = AlertService()
    alerts = service.generate_alerts()
    critical = [a for a in alerts if a.get('tipo') == 'CRÍTICO']
    return create_response(critical or [])

@app.route('/api/predictions/loan', methods=['POST'])
@handle_errors
def predict_loan_delay():
    logger.info('Making loan delay prediction')
    data = request.get_json()
    predictor = DelayPredictor()
    prediction = predictor.predict_loan(
        id_usuario=data.get('id_usuario'),
        dias_duracao=data.get('dias_duracao'),
        mes_retirada=data.get('mes_retirada'),
        dia_semana=data.get('dia_semana'),
        historico_emprestimos=data.get('historico_emprestimos', 0),
        historico_atrasos=data.get('historico_atrasos', 0),
        taxa_atraso=data.get('taxa_atraso', 0.0),
        funcao=data.get('funcao', 'ALUNO')
    )
    return create_response(prediction or {})

@app.route('/api/model/train', methods=['POST'])
@handle_errors
def train_model():
    logger.info('Training delay prediction model')
    predictor = DelayPredictor()
    success = predictor.train_model()
    return create_response(
        {'trained': success},
        200 if success else 500,
        'Model trained successfully' if success else 'Failed to train model'
    )

@app.route('/api/reports/json', methods=['GET'])
@handle_errors
def generate_json_report():
    logger.info('Generating JSON report')
    generator = ReportGenerator()
    filepath = generator.generate_json_report()
    with open(filepath, 'r', encoding='utf-8') as f:
        import json
        report = json.load(f)
    return create_response(report)

@app.route('/api/reports/csv', methods=['GET'])
@handle_errors
def generate_csv_report():
    logger.info('Generating CSV report')
    generator = ReportGenerator()
    filepath = generator.generate_csv_report()
    return create_response({'filepath': filepath, 'message': 'CSV report generated'})

@app.route('/api/reports/predictions', methods=['GET'])
@handle_errors
def generate_predictions_report():
    logger.info('Generating predictions report')
    generator = ReportGenerator()
    filepath = generator.generate_predictions_report()
    with open(filepath, 'r', encoding='utf-8') as f:
        import json
        report = json.load(f)
    return create_response(report)

@app.route('/api/analysis/complete', methods=['GET'])
@handle_errors
def complete_analysis():
    logger.info('Running complete analysis')
    generator = ReportGenerator()
    analyzer = LoanAnalyzer()
    alert_service = AlertService()

    generator.generate_json_report()
    generator.generate_csv_report()
    generator.generate_overdue_report()

    alerts = alert_service.generate_alerts()
    alert_service.save_alerts('reports/alerts.json')

    summary = analyzer.generate_summary_report()

    return create_response({
        'summary': summary,
        'alerts': alerts
    })

@app.route('/api/cache/clear', methods=['POST'])
@handle_errors
def clear_cache():
    logger.info('Cache cleared')
    cache.clear()
    return create_response({'cleared': True}, message='Cache limpo com sucesso')

@app.route('/api/status', methods=['GET'])
@cached_endpoint(ttl=Config.CACHE_TTL_SHORT)
@handle_errors
def get_status():
    logger.info('Getting system status')
    analyzer = LoanAnalyzer()
    report = analyzer.generate_summary_report()
    
    if not report:
        return create_response({'status': 'No data'}, 200)
    
    return create_response({
        'total_loans': report['resumo_geral']['total_emprestimos'],
        'active_loans': report['resumo_geral']['emprestimos_ativos'],
        'overdue_loans': report['resumo_geral']['emprestimos_atrasados'],
        'last_generated': report['data_geracao']
    })

@app.errorhandler(404)
def not_found(error):
    logger.warning(f'404 error: {request.path}')
    return create_response(None, 404, 'Endpoint not found')

@app.errorhandler(500)
def internal_error(error):
    logger.error(f'500 error: {str(error)}', exc_info=True)
    return create_response(None, 500, 'Internal server error')

if __name__ == '__main__':
    app.run(
        host=Config.FLASK_HOST,
        port=Config.FLASK_PORT,
        debug=Config.FLASK_DEBUG
    )

if __name__ == '__main__':
    app.run(
        host=os.getenv('FLASK_HOST', '0.0.0.0'),
        port=int(os.getenv('FLASK_PORT', 5000)),
        debug=os.getenv('FLASK_DEBUG', False)
    )
