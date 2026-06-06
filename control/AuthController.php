<?php

require_once __DIR__ . '/Models/UsuarioModel.php';

class AuthController
{
    private $connection;
    private $usuarioModel;
    
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->usuarioModel = new UsuarioModel($connection);
    }
    
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (! $input) {
            ApiResponse::send(
                ApiResponse::error('Corpo da requisição inválido', 400)
            );
        }
        
        try {
            $email = InputValidator::sanitizeEmail($input['email'] ?? '');
            $password = $input['password'] ?? '';
            
            if (empty($password)) {
                throw new Exception('Senha é obrigatória');
            }
            
            $user = $this->usuarioModel->buscarPorEmail($email);

            if (! $user) {
                ApiResponse::send(
                    ApiResponse::error('Credenciais inválidas', 401)
                );
            }
            
            if (! password_verify($password, $user['Senha_Hash'])) {
                ApiResponse::send(
                    ApiResponse::error('Credenciais inválidas', 401)
                );
            }
            
            $token = JwtAuth::generateToken([
                'id' => $user['ID_Usuario'],
                'email' => $email,
                'funcao' => $user['Funcao']
            ]);
            
            ApiResponse::send(
                ApiResponse::success([
                    'token' => $token,
                    'user_id' => $user['ID_Usuario'],
                    'funcao' => $user['Funcao']
                ], 'Login realizado com sucesso', 200)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
    
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (! $input) {
            ApiResponse::send(
                ApiResponse::error('Corpo da requisição inválido', 400)
            );
        }
        
        try {
            $email = InputValidator::sanitizeEmail($input['email'] ?? '');
            $password = $input['password'] ?? '';
            $nome = InputValidator::sanitizeString($input['nome'] ?? '');
            $cpf = InputValidator::sanitizeCPF($input['cpf'] ?? '');
            $matricula = InputValidator::sanitizeString($input['matricula'] ?? '');
            $data_nascimento = InputValidator::sanitizeDate($input['data_nascimento'] ?? '');
            $id_instituicao = InputValidator::sanitizeInteger($input['id_instituicao'] ?? 0);
            $funcao = InputValidator::validateEnum(
                $input['funcao'] ?? '',
                ['ALUNO', 'PROFESSOR', 'TECNICO_ADMINISTRATIVO', 'TERCEIRIZADO']
            );
            
            InputValidator::validatePassword($password);
            
            $senhaHash = password_hash($password, PASSWORD_BCRYPT);
            
            $userId = $this->usuarioModel->criar([
                'cpf' => $cpf,
                'matricula' => $matricula,
                'nome' => $nome,
                'data_nascimento' => $data_nascimento,
                'email' => $email,
                'senha_hash' => $senhaHash,
                'funcao' => $funcao,
                'id_instituicao' => $id_instituicao
            ]);
            
            ApiResponse::send(
                ApiResponse::success([
                    'user_id' => $userId,
                    'email' => $email,
                    'funcao' => $funcao
                ], 'Usuário registrado com sucesso', 201)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
}
