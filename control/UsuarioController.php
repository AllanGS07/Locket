<?php

require_once __DIR__ . '/Models/UsuarioModel.php';

class UsuarioController
{
    private $connection;
    private $auth;
    private $usuarioModel;
    
    public function __construct($connection, $auth)
    {
        $this->connection = $connection;
        $this->auth = $auth;
        $this->usuarioModel = new UsuarioModel($connection);
    }
    
    public function listar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        try {
            $id_instituicao = $this->auth['id'] ?? 0;
            
            $usuarios = $this->usuarioModel->listarPorInstituicao($id_instituicao);
            
            ApiResponse::send(
                ApiResponse::success($usuarios, 'Usuários listados com sucesso', 200)
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
            
            if ($this->auth['id'] != $id && $this->auth['funcao'] !== 'PROFESSOR') {
                ApiResponse::send(
                    ApiResponse::error('Acesso negado', 403)
                );
            }
            
            $usuario = $this->usuarioModel->obterPorId($id);

            if (! $usuario) {
                ApiResponse::send(
                    ApiResponse::error('Usuário não encontrado', 404)
                );
            }
            
            ApiResponse::send(
                ApiResponse::success($usuario, 'Usuário obtido com sucesso', 200)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
    
    public function deletar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }

        try {
            $id = InputValidator::sanitizeInteger($id);

            if (! $this->auth || ! isset($this->auth['id'])) {
                ApiResponse::send(
                    ApiResponse::error('Token inválido', 401)
                );
            }

            $this->usuarioModel->deletar($id);

            ApiResponse::send(
                ApiResponse::success(['id' => $id], 'Usuário excluído com sucesso', 200)
            );
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }

    public function atualizar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        try {
            $id = InputValidator::sanitizeInteger($id);
            
            if ($this->auth['id'] != $id) {
                ApiResponse::send(
                    ApiResponse::error('Acesso negado', 403)
                );
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (! $input) {
                throw new Exception('Corpo da requisição inválido');
            }
            
            $nome = InputValidator::sanitizeString($input['nome'] ?? null);
            
            if (! $nome) {
                throw new Exception('Nome é obrigatório');
            }
            
            $this->usuarioModel->atualizarNome($id, $nome);
            
            ApiResponse::send(
                ApiResponse::success(['id' => $id], 'Usuário atualizado com sucesso', 200)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
}
