<?php

class UsuarioController
{
    private $connection;
    private $auth;
    
    public function __construct($connection, $auth)
    {
        $this->connection = $connection;
        $this->auth = $auth;
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
            
            $stmt = $this->connection->prepare(
                'SELECT ID_Usuario, Nome, Funcao, Data_Criacao FROM vw_usuario_publico WHERE ID_Instituicao = ? LIMIT 100'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('i', $id_instituicao);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $usuarios = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
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
            
            $stmt = $this->connection->prepare(
                'SELECT ID_Usuario, CPF, Nome, Email, Funcao, Data_Criacao FROM vw_usuario_publico WHERE ID_Usuario = ? LIMIT 1'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                ApiResponse::send(
                    ApiResponse::error('Usuário não encontrado', 404)
                );
            }
            
            $usuario = $result->fetch_assoc();
            $stmt->close();
            
            ApiResponse::send(
                ApiResponse::success($usuario, 'Usuário obtido com sucesso', 200)
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
            
            $stmt = $this->connection->prepare(
                'UPDATE Usuario SET Nome = ? WHERE ID_Usuario = ? AND Ativo = TRUE'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('si', $nome, $id);
            
            if (! $stmt->execute()) {
                throw new Exception('Erro ao atualizar usuário');
            }
            
            $stmt->close();
            
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
