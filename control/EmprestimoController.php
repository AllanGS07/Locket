<?php

require_once __DIR__ . '/Models/EmprestimoModel.php';

class EmprestimoController
{
    private $connection;
    private $auth;
    private $emprestimoModel;
    
    public function __construct($connection, $auth)
    {
        $this->connection = $connection;
        $this->auth = $auth;
        $this->emprestimoModel = new EmprestimoModel($connection);
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
            
            $emprestimos = $this->emprestimoModel->listarPorUsuario($idUsuario);
            
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
            
            $disponivel = $this->emprestimoModel->verificarDisponibilidade($idObjeto);

            if (! $disponivel) {
                ApiResponse::send(
                    ApiResponse::error('Objeto não está disponível', 400)
                );
            }
            
            $emprestimoId = $this->emprestimoModel->criar([
                'id_usuario' => $idUsuario,
                'id_objeto' => $idObjeto,
                'data_retirada' => $dataRetirada,
                'data_devolucao_prevista' => $dataDevolucaoPrevista,
            ]);
            
            $this->emprestimoModel->atualizarStatusObjeto($idObjeto, 'EMPRESTADO');
            
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
            
            $this->emprestimoModel->devolver($id);

            $emp = $this->emprestimoModel->buscarObjetoDoEmprestimo($id);
            
            if ($emp) {
                $this->emprestimoModel->atualizarStatusObjeto($emp['ID_Objeto'], 'DISPONIVEL');
            }
            
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
