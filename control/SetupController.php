<?php

class SetupController
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function inicializarBancoFalso()
    {
        try {
            $this->connection->begin_transaction();

            // 1. Cria Instituição de Ensino se não existir
            $resultInst = $this->connection->query("SELECT ID_Instituicao FROM Instituicao_Ensino LIMIT 1");
            if ($resultInst->num_rows === 0) {
                $stmtInst = $this->connection->prepare("INSERT INTO Instituicao_Ensino (CNPJ, Nome, e_MEC_INEP) VALUES (?, ?, ?)");
                $cnpj = '00.000.000/0001-00';
                $nomeInst = 'Universidade Locket Demo';
                $mec = '12345678';
                $stmtInst->bind_param("sss", $cnpj, $nomeInst, $mec);
                $stmtInst->execute();
                $idInstituicao = $this->connection->insert_id;
            } else {
                $idInstituicao = $resultInst->fetch_assoc()['ID_Instituicao'];
            }

            // 2. Injeta Usuários Falsos se a tabela estiver vazia
            $resultUser = $this->connection->query("SELECT ID_Usuario FROM Usuario LIMIT 1");
            if ($resultUser->num_rows === 0) {
                // Insere Admin
                $this->inserirUsuario('00011122233', 'MAT001', 'Admin User', 'admin@test.com', 'admin_123', 'PROFESSOR', $idInstituicao);
                // Insere outros usuários baseados no seu HTML estático
                $this->inserirUsuario('11122233344', 'MAT002', 'João Silva', 'joao@test.com', 'joao_123', 'ALUNO', $idInstituicao);
                $this->inserirUsuario('22233344455', 'MAT003', 'Maria Santos', 'maria@test.com', 'maria_123', 'TECNICO_ADMINISTRATIVO', $idInstituicao);
                $this->inserirUsuario('33344455566', 'MAT004', 'Pedro Oliveira', 'pedro@test.com', 'pedro_123', 'ALUNO', $idInstituicao, 0); // Inativo
            }

            // 3. Injeta Ativos/Objetos se a tabela estiver vazia
             $resultObj = $this->connection->query("SELECT ID_Objeto FROM Objeto LIMIT 1");
             if ($resultObj->num_rows === 0) {
                 $this->inserirObjeto('Imovel Comercial', 'Genérica', 'Padrão', 'TOMB001', 'DISPONIVEL', $idInstituicao);
                 $this->inserirObjeto('Toyota Corolla 2022', 'Toyota', 'Corolla', 'TOMB002', 'EMPRESTADO', $idInstituicao);
             }

            $this->connection->commit();
            ApiResponse::send(ApiResponse::success(null, 'Banco de dados inicializado com dados falsos.', 200));

        } catch (Exception $e) {
            $this->connection->rollback();
            ApiResponse::send(ApiResponse::error('Erro ao inicializar o banco: ' . $e->getMessage(), 500));
        }
    }

    private function inserirUsuario($cpf, $matricula, $nome, $email, $senha, $funcao, $idInstituicao, $ativo = 1) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $dataNasc = '1990-01-01'; // Data fixa para simplificar
        $stmt = $this->connection->prepare("INSERT INTO Usuario (CPF, Matricula, Nome, Data_Nascimento, Email, Senha_Hash, Funcao, ID_Instituicao, Ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssii", $cpf, $matricula, $nome, $dataNasc, $email, $senhaHash, $funcao, $idInstituicao, $ativo);
        $stmt->execute();
    }

    private function inserirObjeto($nome, $marca, $modelo, $tombamento, $status, $idInstituicao) {
         $stmt = $this->connection->prepare("INSERT INTO Objeto (Nome, Marca, Modelo, Numero_Tombamento, Status_Item, ID_Instituicao) VALUES (?, ?, ?, ?, ?, ?)");
         $stmt->bind_param("sssssi", $nome, $marca, $modelo, $tombamento, $status, $idInstituicao);
         $stmt->execute();
    }
}