<?php

class EmprestimoModel
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function listarPorUsuario($idUsuario)
    {
        $stmt = $this->connection->prepare(
            'SELECT ID_Emprestimo, Nome_Usuario, Nome_Objeto, Data_Retirada, Data_Devolucao_Prevista, Status_Emprestimo
             FROM vw_detalhes_emprestimos
             WHERE (? = 0 OR ID_Usuario = ?)
             ORDER BY Data_Retirada DESC
             LIMIT 50'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('ii', $idUsuario, $idUsuario);
        $stmt->execute();

        $result = $stmt->get_result();
        $emprestimos = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $emprestimos;
    }

    public function criar($dados)
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO Emprestimos (ID_Usuario, ID_Objeto, Data_Retirada, Data_Devolucao_Prevista, Status_Emprestimo)
             VALUES (?, ?, ?, ?, "PENDENTE")'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('iiss', $dados['id_usuario'], $dados['id_objeto'], $dados['data_retirada'], $dados['data_devolucao_prevista']);

        if (! $stmt->execute()) {
            throw new Exception('Erro ao criar empréstimo');
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return $id;
    }

    public function devolver($id)
    {
        $dataDevolucao = date('Y-m-d');

        $stmt = $this->connection->prepare(
            'UPDATE Emprestimos SET Data_Devolucao_Real = ?, Status_Emprestimo = "DEVOLVIDO" WHERE ID_Emprestimo = ?'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('si', $dataDevolucao, $id);

        if (! $stmt->execute()) {
            throw new Exception('Erro ao devolver empréstimo');
        }

        $stmt->close();
        return true;
    }

    public function buscarObjetoDoEmprestimo($id)
    {
        $stmt = $this->connection->prepare('SELECT ID_Objeto FROM Emprestimos WHERE ID_Emprestimo = ?');

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $emp = $result->fetch_assoc();
        $stmt->close();

        return $emp;
    }

    public function verificarDisponibilidade($idObjeto)
    {
        $stmt = $this->connection->prepare('SELECT ObjetoEstaDisponivel(?) AS disponivel');

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('i', $idObjeto);
        $stmt->execute();

        $result = $stmt->get_result();
        $dados = $result->fetch_assoc();
        $stmt->close();

        return (bool) ($dados['disponivel'] ?? false);
    }

    public function atualizarStatusObjeto($idObjeto, $status)
    {
        $stmt = $this->connection->prepare('UPDATE Objeto SET Status_Item = ? WHERE ID_Objeto = ?');

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('si', $status, $idObjeto);

        if (! $stmt->execute()) {
            throw new Exception('Erro ao atualizar status do objeto');
        }

        $stmt->close();
        return true;
    }
}
