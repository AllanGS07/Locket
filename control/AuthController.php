<?php

class AuthController
{
    private $connection;
    
    public function __construct($connection)
    {
        $this->connection = $connection;
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
            
            $stmt = $this->connection->prepare(
                'SELECT ID_Usuario, Senha_Hash, Funcao FROM Usuario WHERE Email = ? AND Ativo = TRUE LIMIT 1'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                ApiResponse::send(
                    ApiResponse::error('Credenciais inválidas', 401)
                );
            }
            
            $user = $result->fetch_assoc();
            
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
            
            $stmt->close();
            
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
            
            $stmt = $this->connection->prepare(
                'INSERT INTO Usuario (CPF, Matricula, Nome, Data_Nascimento, Email, Senha_Hash, Funcao, ID_Instituicao)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param(
                'sssssssi',
                $cpf,
                $matricula,
                $nome,
                $data_nascimento,
                $email,
                $senhaHash,
                $funcao,
                $id_instituicao
            );
            
            if (! $stmt->execute()) {
                if ($this->connection->errno === 1062) {
                    throw new Exception('Email ou CPF já registrados');
                }
                throw new Exception('Erro ao registrar usuário');
            }
            
            $userId = $stmt->insert_id;
            $stmt->close();
            
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
