<?php

class ObjetoController
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
            $stmt = $this->connection->prepare(
                'SELECT ID_Objeto, Nome, Marca, Modelo, Status_Item FROM vw_objetos_publicos ORDER BY Nome ASC LIMIT 100'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $objetos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
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
            
            $stmt = $this->connection->prepare(
                'SELECT ID_Objeto, Nome, Marca, Modelo, Numero_Tombamento, Status_Item FROM Objeto WHERE ID_Objeto = ? LIMIT 1'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                ApiResponse::send(
                    ApiResponse::error('Objeto não encontrado', 404)
                );
            }
            
            $objeto = $result->fetch_assoc();
            $stmt->close();
            
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
            
            $stmt = $this->connection->prepare(
                'INSERT INTO Objeto (Numero_Tombamento, Nome, Marca, Modelo, Numero_Serie, ID_Instituicao, Status_Item)
                 VALUES (?, ?, ?, ?, ?, ?, "DISPONIVEL")'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('issssi', $numero_tombamento, $nome, $marca, $modelo, $numero_serie, $id_instituicao);
            
            if (! $stmt->execute()) {
                if ($this->connection->errno === 1062) {
                    throw new Exception('Número de tombamento ou série já registrados');
                }
                throw new Exception('Erro ao criar objeto');
            }
            
            $objetoId = $stmt->insert_id;
            $stmt->close();
            
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
