<?php

class UsuarioModel
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function listarPorInstituicao($idInstituicao)
    {
        $stmt = $this->connection->prepare(
            'SELECT ID_Usuario, Nome, Funcao, Data_Criacao FROM vw_usuario_publico WHERE ID_Instituicao = ? LIMIT 100'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('i', $idInstituicao);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $usuarios;
    }

    public function obterPorId($id)
    {
        $stmt = $this->connection->prepare(
            'SELECT ID_Usuario, CPF, Nome, Email, Funcao, Data_Criacao FROM vw_usuario_publico WHERE ID_Usuario = ? LIMIT 1'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();

        return $usuario;
    }

    public function atualizarNome($id, $nome)
    {
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
        return true;
    }

    public function deletar($id)
    {
        $stmt = $this->connection->prepare(
            'UPDATE Usuario SET Ativo = FALSE WHERE ID_Usuario = ?'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('i', $id);

        if (! $stmt->execute()) {
            throw new Exception('Erro ao excluir usuário');
        }

        $stmt->close();
        return true;
    }

    public function buscarPorEmail($email)
    {
        $stmt = $this->connection->prepare(
            'SELECT ID_Usuario, Senha_Hash, Funcao FROM Usuario WHERE Email = ? AND Ativo = TRUE LIMIT 1'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();

        return $usuario;
    }

    public function criar($dados)
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO Usuario (CPF, Matricula, Nome, Data_Nascimento, Email, Senha_Hash, Funcao, ID_Instituicao)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param(
            'sssssssi',
            $dados['cpf'],
            $dados['matricula'],
            $dados['nome'],
            $dados['data_nascimento'],
            $dados['email'],
            $dados['senha_hash'],
            $dados['funcao'],
            $dados['id_instituicao']
        );

        if (! $stmt->execute()) {
            if ($this->connection->errno === 1062) {
                throw new Exception('Email ou CPF já registrados');
            }
            throw new Exception('Erro ao registrar usuário');
        }

        $userId = $stmt->insert_id;
        $stmt->close();

        return $userId;
    }
}
