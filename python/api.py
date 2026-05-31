from flask import Flask, jsonify, request
from flask_cors import CORS
import os
from dotenv import load_dotenv
from datetime import datetime
from loan_analyzer import LoanAnalyzer
from delay_predictor import DelayPredictor
from alert_service import AlertService
from report_generator import ReportGenerator

load_dotenv()

app = Flask(__name__)
CORS(app, origins=['http://localhost:3000', 'http://localhost:8000', 'http://localhost', 'http://127.0.0.1'])

def create_response(data, status_code=200, message="Success"):
    return jsonify({
        'success': status_code < 400,
        'status_code': status_code,
        'message': message,
        'data': data,
        'timestamp': datetime.now().isoformat()
    }), status_code

@app.route('/api/health', methods=['GET'])
def health_check():
    return create_response({'status': 'healthy'})

@app.route('/api/loans', methods=['GET'])
def get_all_loans():
    try:
        analyzer = LoanAnalyzer()
        loans = analyzer.get_all_loans()
        return create_response(loans or [])
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/loans/overdue', methods=['GET'])
def get_overdue_loans():
    try:
        analyzer = LoanAnalyzer()
        overdue = analyzer.analyze_overdue_loans()
        return create_response(overdue or [])
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/loans/summary', methods=['GET'])
def get_loans_summary():
    try:
        analyzer = LoanAnalyzer()
        report = analyzer.generate_summary_report()
        return create_response(report or {})
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/users/statistics', methods=['GET'])
def get_user_statistics():
    try:
        analyzer = LoanAnalyzer()
        stats = analyzer.get_user_statistics()
        return create_response(stats or [])
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/items/most-borrowed', methods=['GET'])
def get_most_borrowed_items():
    try:
        limit = request.args.get('limit', 10, type=int)
        analyzer = LoanAnalyzer()
        items = analyzer.get_most_borrowed_items(limit)
        return create_response(items or [])
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/alerts', methods=['GET'])
def get_alerts():
    try:
        service = AlertService()
        alerts = service.generate_alerts()
        return create_response(alerts or [])
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/alerts/critical', methods=['GET'])
def get_critical_alerts():
    try:
        service = AlertService()
        alerts = service.generate_alerts()
        critical = [a for a in alerts if a.get('tipo') == 'CRÍTICO']
        return create_response(critical or [])
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/predictions/loan', methods=['POST'])
def predict_loan_delay():
    try:
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
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/model/train', methods=['POST'])
def train_model():
    try:
        predictor = DelayPredictor()
        success = predictor.train_model()
        return create_response(
            {'trained': success},
            200 if success else 500,
            'Model trained successfully' if success else 'Failed to train model'
        )
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/reports/json', methods=['GET'])
def generate_json_report():
    try:
        generator = ReportGenerator()
        filepath = generator.generate_json_report()
        with open(filepath, 'r', encoding='utf-8') as f:
            import json
            report = json.load(f)
        return create_response(report)
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/reports/csv', methods=['GET'])
def generate_csv_report():
    try:
        generator = ReportGenerator()
        filepath = generator.generate_csv_report()
        return create_response({'filepath': filepath, 'message': 'CSV report generated'})
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/reports/predictions', methods=['GET'])
def generate_predictions_report():
    try:
        generator = ReportGenerator()
        filepath = generator.generate_predictions_report()
        with open(filepath, 'r', encoding='utf-8') as f:
            import json
            report = json.load(f)
        return create_response(report)
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/analysis/complete', methods=['GET'])
def complete_analysis():
    try:
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
    except Exception as e:
        return create_response(None, 500, str(e))

@app.route('/api/status', methods=['GET'])
def get_status():
    try:
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
    except Exception as e:
        return create_response(None, 500, str(e))

@app.errorhandler(404)
def not_found(error):
    return create_response(None, 404, 'Endpoint not found')

@app.errorhandler(500)
def internal_error(error):
    return create_response(None, 500, 'Internal server error')

if __name__ == '__main__':
    app.run(
        host=os.getenv('FLASK_HOST', '0.0.0.0'),
        port=int(os.getenv('FLASK_PORT', 5000)),
        debug=os.getenv('FLASK_DEBUG', False)
    )
