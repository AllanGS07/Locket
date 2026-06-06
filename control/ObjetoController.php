<?php

require_once __DIR__ . '/Models/ObjetoModel.php';

class ObjetoController
{
    private $connection;
    private $auth;
    private $objetoModel;
    
    public function __construct($connection, $auth)
    {
        $this->connection = $connection;
        $this->auth = $auth;
        $this->objetoModel = new ObjetoModel($connection);
    }
    
    public function listar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        try {
            $objetos = $this->objetoModel->listarTodos();
            
            ApiResponse::send(
                ApiResponse::success($objetos, 'Objetos listados com sucesso', 200)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
    
    public function obter($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        try {
            $id = InputValidator::sanitizeInteger($id);
            
            $objeto = $this->objetoModel->obterPorId($id);

            if (! $objeto) {
                ApiResponse::send(
                    ApiResponse::error('Objeto não encontrado', 404)
                );
            }
            
            ApiResponse::send(
                ApiResponse::success($objeto, 'Objeto obtido com sucesso', 200)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
    
    public function criar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        if ($this->auth['funcao'] !== 'TECNICO_ADMINISTRATIVO') {
            ApiResponse::send(
                ApiResponse::error('Acesso negado', 403)
            );
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (! $input) {
                throw new Exception('Corpo da requisição inválido');
            }
            
            $numero_tombamento = InputValidator::sanitizeInteger($input['numero_tombamento'] ?? 0);
            $nome = InputValidator::sanitizeString($input['nome'] ?? '');
            $marca = InputValidator::sanitizeString($input['marca'] ?? '');
            $modelo = InputValidator::sanitizeString($input['modelo'] ?? '');
            $numero_serie = InputValidator::sanitizeString($input['numero_serie'] ?? '');
            $id_instituicao = InputValidator::sanitizeInteger($input['id_instituicao'] ?? 0);
            
            $erros = [];
            if (! $numero_tombamento) $erros[] = 'Número de tombamento é obrigatório';
            if (! $nome) $erros[] = 'Nome é obrigatório';
            if (! $marca) $erros[] = 'Marca é obrigatória';
            if (! $modelo) $erros[] = 'Modelo é obrigatório';
            if (! $numero_serie) $erros[] = 'Número de série é obrigatório';
            
            if (! empty($erros)) {
                ApiResponse::send(
                    ApiResponse::error('Validação falhou', 400, $erros)
                );
            }
            
            $objetoId = $this->objetoModel->criar([
                'numero_tombamento' => $numero_tombamento,
                'nome' => $nome,
                'marca' => $marca,
                'modelo' => $modelo,
                'numero_serie' => $numero_serie,
                'id_instituicao' => $id_instituicao,
            ]);
            
            ApiResponse::send(
                ApiResponse::success(['id' => $objetoId], 'Objeto criado com sucesso', 201)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
}
