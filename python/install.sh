#!/bin/bash
# Script de instalação do sistema de inteligência (Linux/Mac)

echo ""
echo "========================================"
echo "Instalador - Sistema de Inteligência"
echo "========================================"
echo ""

# Verificar se Python está instalado
if ! command -v python3 &> /dev/null; then
    echo "[ERRO] Python3 não encontrado!"
    echo "Instale Python 3.8+ usando seu gerenciador de pacotes"
    exit 1
fi

echo "[OK] Python3 encontrado"

# Criar arquivo .env se não existir
if [ ! -f ".env" ]; then
    echo "[INFO] Criando arquivo .env..."
    cp ".env.example" ".env"
    echo "[OK] Arquivo .env criado"
fi

# Criar diretórios necessários
mkdir -p models
mkdir -p reports
echo "[OK] Diretórios criados"

# Instalar dependências
echo ""
echo "[INFO] Instalando dependências Python..."
pip3 install -r requirements.txt

if [ $? -ne 0 ]; then
    echo "[ERRO] Erro ao instalar dependências!"
    exit 1
fi

echo ""
echo "[OK] Teste de sistema..."
python3 test_system.py

echo ""
echo "========================================"
echo "Instalação concluída!"
echo "========================================"
echo ""
echo "Use:"
echo "  python3 main.py relatorio    - Gerar relatório"
echo "  python3 main.py alertas      - Ver alertas"
echo "  python3 main.py completo     - Análise completa"
echo ""
