<?php

class EmprestimoController
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
            $idUsuario = InputValidator::sanitizeInteger($_GET['usuario_id'] ?? $this->auth['id']);
            
            if ($this->auth['id'] !== $idUsuario && $this->auth['funcao'] === 'ALUNO') {
                ApiResponse::send(
                    ApiResponse::error('Acesso negado', 403)
                );
            }
            
            $stmt = $this->connection->prepare(
                'SELECT 
                    ID_Emprestimo, 
                    Nome_Usuario, 
                    Nome_Objeto, 
                    Data_Retirada, 
                    Data_Devolucao_Prevista, 
                    Status_Emprestimo 
                FROM vw_detalhes_emprestimos 
                WHERE (? = 0 OR ID_Usuario = ?)
                ORDER BY Data_Retirada DESC
                LIMIT 50'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $parentesis = ($this->auth['funcao'] === 'ALUNO') ? 0 : 0;
            $stmt->bind_param('ii', $idUsuario, $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $emprestimos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            ApiResponse::send(
                ApiResponse::success($emprestimos, 'Empréstimos listados com sucesso', 200)
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
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (! $input) {
                throw new Exception('Corpo da requisição inválido');
            }
            
            $idUsuario = InputValidator::sanitizeInteger($input['id_usuario'] ?? $this->auth['id']);
            $idObjeto = InputValidator::sanitizeInteger($input['id_objeto'] ?? 0);
            $dataRetirada = InputValidator::sanitizeDate($input['data_retirada'] ?? date('Y-m-d'));
            $dataDevolucaoPrevista = InputValidator::sanitizeDate($input['data_devolucao_prevista'] ?? '');
            
            if (! $dataDevolucaoPrevista) {
                throw new Exception('Data de devolução prevista é obrigatória');
            }
            
            if (strtotime($dataDevolucaoPrevista) <= strtotime($dataRetirada)) {
                throw new Exception('Data de devolução deve ser posterior à data de retirada');
            }
            
            if ($this->auth['id'] !== $idUsuario && $this->auth['funcao'] === 'ALUNO') {
                ApiResponse::send(
                    ApiResponse::error('Acesso negado', 403)
                );
            }
            
            $stmtVerify = $this->connection->prepare(
                'SELECT ObjetoEstaDisponivel(?) AS disponivel'
            );
            
            if (! $stmtVerify) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmtVerify->bind_param('i', $idObjeto);
            $stmtVerify->execute();
            $resultVerify = $stmtVerify->get_result();
            
            $objDisp = $resultVerify->fetch_assoc();
            $stmtVerify->close();
            
            if (! $objDisp['disponivel']) {
                ApiResponse::send(
                    ApiResponse::error('Objeto não está disponível', 400)
                );
            }
            
            $stmt = $this->connection->prepare(
                'INSERT INTO Emprestimos (ID_Usuario, ID_Objeto, Data_Retirada, Data_Devolucao_Prevista, Status_Emprestimo)
                 VALUES (?, ?, ?, ?, "PENDENTE")'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('iiss', $idUsuario, $idObjeto, $dataRetirada, $dataDevolucaoPrevista);
            
            if (! $stmt->execute()) {
                throw new Exception('Erro ao criar empréstimo');
            }
            
            $emprestimoId = $stmt->insert_id;
            
            $stmtUpdate = $this->connection->prepare(
                'UPDATE Objeto SET Status_Item = "EMPRESTADO" WHERE ID_Objeto = ?'
            );
            $stmtUpdate->bind_param('i', $idObjeto);
            $stmtUpdate->execute();
            $stmtUpdate->close();
            
            $stmt->close();
            
            ApiResponse::send(
                ApiResponse::success(['id' => $emprestimoId], 'Empréstimo criado com sucesso', 201)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
    
    public function devolver($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            ApiResponse::send(
                ApiResponse::error('Método não permitido', 405)
            );
        }
        
        try {
            $id = InputValidator::sanitizeInteger($id);
            $dataDevolucao = date('Y-m-d');
            
            $stmt = $this->connection->prepare(
                'UPDATE Emprestimos 
                 SET Data_Devolucao_Real = ?, Status_Emprestimo = "DEVOLVIDO"
                 WHERE ID_Emprestimo = ?'
            );
            
            if (! $stmt) {
                throw new Exception('Erro na consulta ao banco');
            }
            
            $stmt->bind_param('si', $dataDevolucao, $id);
            
            if (! $stmt->execute()) {
                throw new Exception('Erro ao devolver empréstimo');
            }
            
            $stmtGet = $this->connection->prepare(
                'SELECT ID_Objeto FROM Emprestimos WHERE ID_Emprestimo = ?'
            );
            $stmtGet->bind_param('i', $id);
            $stmtGet->execute();
            $resultGet = $stmtGet->get_result();
            
            if ($resultGet->num_rows > 0) {
                $emp = $resultGet->fetch_assoc();
                
                $stmtUpdate = $this->connection->prepare(
                    'UPDATE Objeto SET Status_Item = "DISPONIVEL" WHERE ID_Objeto = ?'
                );
                $stmtUpdate->bind_param('i', $emp['ID_Objeto']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            
            $stmtGet->close();
            $stmt->close();
            
            ApiResponse::send(
                ApiResponse::success(['id' => $id], 'Empréstimo devolvido com sucesso', 200)
            );
            
        } catch (Exception $e) {
            ApiResponse::send(
                ApiResponse::error($e->getMessage(), 400)
            );
        }
    }
}
