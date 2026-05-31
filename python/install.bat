@echo off
REM Script de instalação do sistema de inteligência (Windows)
REM Compatível com Python 3.8+

echo.
echo ========================================
echo Instalador - Sistema de Inteligência
echo ========================================
echo.

REM Verificar se Python está instalado
python --version >nul 2>&1
if errorlevel 1 (
    echo [ERRO] Python não encontrado!
    echo Instale Python 3.8+ de https://www.python.org/
    exit /b 1
)

echo [OK] Python encontrado

REM Criar arquivo .env se não existir
if not exist ".env" (
    echo [INFO] Criando arquivo .env...
    copy ".env.example" ".env"
    echo [OK] Arquivo .env criado
)

REM Criar diretórios necessários
if not exist "models" (
    mkdir models
    echo [OK] Diretório 'models' criado
)

if not exist "reports" (
    mkdir reports
    echo [OK] Diretório 'reports' criado
)

REM Instalar dependências
echo.
echo [INFO] Instalando dependências Python...
pip install -r requirements.txt

if errorlevel 1 (
    echo [ERRO] Erro ao instalar dependências!
    exit /b 1
)

echo.
echo [OK] Teste de sistema...
python test_system.py

echo.
echo ========================================
echo Instalação concluída!
echo ========================================
echo.
echo Use:
echo   python main.py relatorio    - Gerar relatório
echo   python main.py alertas      - Ver alertas
echo   python main.py completo     - Análise completa
echo.
pause
