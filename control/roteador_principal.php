<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/erro.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/configuracao_banco_dados.php';
require_once __DIR__ . '/resposta_api.php';
require_once __DIR__ . '/validador_entrada.php';
require_once __DIR__ . '/autenticacao_jwt.php';
require_once __DIR__ . '/intermediario_autenticacao.php';
require_once __DIR__ . '/controlador_autenticacao.php';
require_once __DIR__ . '/controlador_usuario.php';
require_once __DIR__ . '/controlador_objeto.php';
require_once __DIR__ . '/controlador_emprestimo.php';

try {
    $conexao = ConfiguracaoBancoDados::obterConexao();
} catch (Exception $e) {
    RespostaApi::enviar(
        RespostaApi::enviarErro('Erro ao conectar ao banco de dados', 500)
    );
}

$metodo = $_SERVER['REQUEST_METHOD'];
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$caminho = preg_replace('|^/[^/]+\.php|', '', $caminho);
$caminho = rtrim($caminho, '/') ?: '/';

$dadosAutenticacao = IntermediarioAutenticacao::autenticar();

$partesCaminho = explode('/', array_filter(explode('/', $caminho)));

switch (true) {
    case preg_match('~^/auth/login~', $caminho) && $metodo === 'POST':
        $controlador = new ControladorAutenticacao($conexao);
        $controlador->realizarLogin();
        break;

    case preg_match('~^/auth/register~', $caminho) && $metodo === 'POST':
        $controlador = new ControladorAutenticacao($conexao);
        $controlador->registrarNovoUsuario();
        break;

    case preg_match('~^/health~', $caminho) && $metodo === 'GET':
        RespostaApi::enviar(
            RespostaApi::enviarSucesso(['status' => 'online'], 'API online', 200)
        );
        break;

    case preg_match('~^/usuarios$~', $caminho) && $metodo === 'GET':
        $controlador = new ControladorUsuario($conexao, $dadosAutenticacao);
        $controlador->listarUsuariosInstituicao();
        break;

    case preg_match('~^/usuarios/(\d+)$~', $caminho, $correspondencias) && $metodo === 'GET':
        $controlador = new ControladorUsuario($conexao, $dadosAutenticacao);
        $controlador->obterDetalhesUsuario($correspondencias[1]);
        break;

    case preg_match('~^/usuarios/(\d+)$~', $caminho, $correspondencias) && $metodo === 'PUT':
        $controlador = new ControladorUsuario($conexao, $dadosAutenticacao);
        $controlador->atualizarDadosUsuario($correspondencias[1]);
        break;

    case preg_match('~^/objetos$~', $caminho) && $metodo === 'GET':
        $controlador = new ControladorObjeto($conexao, $dadosAutenticacao);
        $controlador->listarObjetos();
        break;

    case preg_match('~^/objetos$~', $caminho) && $metodo === 'POST':
        $controlador = new ControladorObjeto($conexao, $dadosAutenticacao);
        $controlador->criarNovoObjeto();
        break;

    case preg_match('~^/objetos/(\d+)$~', $caminho, $correspondencias) && $metodo === 'GET':
        $controlador = new ControladorObjeto($conexao, $dadosAutenticacao);
        $controlador->obterDetalhesObjeto($correspondencias[1]);
        break;

    case preg_match('~^/emprestimos$~', $caminho) && $metodo === 'GET':
        $controlador = new ControladorEmprestimo($conexao, $dadosAutenticacao);
        $controlador->listarEmprestimos();
        break;

    case preg_match('~^/emprestimos$~', $caminho) && $metodo === 'POST':
        $controlador = new ControladorEmprestimo($conexao, $dadosAutenticacao);
        $controlador->criarNovoEmprestimo();
        break;

    case preg_match('~^/emprestimos/(\d+)/devolver$~', $caminho, $correspondencias) && $metodo === 'PUT':
        $controlador = new ControladorEmprestimo($conexao, $dadosAutenticacao);
        $controlador->devolverEmprestimo($correspondencias[1]);
        break;

    default:
        http_response_code(404);
        RespostaApi::enviar(
            RespostaApi::enviarErro('Rota nao encontrada', 404)
        );
}

$conexao->close();
