"""
Script para testar conexão com banco e funcionalidades
"""
import sys
from config_db import DatabaseConfig
from loan_analyzer import LoanAnalyzer
from alert_service import AlertService

def test_database_connection():
    """Testa conexão com banco"""
    print("Testando conexão com banco de dados...")
    db = DatabaseConfig()
    
    try:
        query = "SELECT COUNT(*) as total FROM usuario"
        result = db.execute_query(query)
        
        if result is not None:
            print(f"✓ Conexão bem-sucedida!")
            print(f"  Total de usuários: {result[0]['total']}")
            return True
        else:
            print("✗ Erro ao conectar ao banco")
            return False
    except Exception as e:
        print(f"✗ Erro: {e}")
        return False

def test_loan_analyzer():
    """Testa analisador de empréstimos"""
    print("\nTestando analisador de empréstimos...")
    
    try:
        analyzer = LoanAnalyzer()
        
        loans = analyzer.get_all_loans()
        print(f"✓ Carregados {len(loans) if loans else 0} empréstimos")
        
        overdue = analyzer.analyze_overdue_loans()
        print(f"✓ Encontrados {len(overdue) if overdue else 0} empréstimos atrasados")
        
        stats = analyzer.get_user_statistics()
        print(f"✓ Calculadas estatísticas de {len(stats) if stats else 0} usuários")
        
        return True
    except Exception as e:
        print(f"✗ Erro: {e}")
        return False

def test_alerts():
    """Testa sistema de alertas"""
    print("\nTestando sistema de alertas...")
    
    try:
        alert_service = AlertService()
        alerts = alert_service.generate_alerts()
        
        print(f"✓ Gerados {len(alerts)} alertas")
        
        critical = len([a for a in alerts if a.get('tipo') == 'CRÍTICO'])
        high = len([a for a in alerts if a.get('tipo') == 'ALTO'])
        
        print(f"  - Críticos: {critical}")
        print(f"  - Altos: {high}")
        
        return True
    except Exception as e:
        print(f"✗ Erro: {e}")
        return False

def main():
    print("="*60)
    print("TESTE DO SISTEMA DE INTELIGÊNCIA")
    print("="*60 + "\n")
    
    tests = [
        test_database_connection,
        test_loan_analyzer,
        test_alerts
    ]
    
    results = []
    for test in tests:
        results.append(test())
    
    print("\n" + "="*60)
    passed = sum(results)
    total = len(results)
    print(f"Resultado: {passed}/{total} testes passaram")
    print("="*60 + "\n")
    
    return all(results)

if __name__ == '__main__':
    success = main()
    sys.exit(0 if success else 1)
