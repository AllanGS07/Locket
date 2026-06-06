<?php

class ObjetoModel
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function listarTodos()
    {
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

        return $objetos;
    }

    public function obterPorId($id)
    {
        $stmt = $this->connection->prepare(
            'SELECT ID_Objeto, Nome, Marca, Modelo, Numero_Tombamento, Status_Item FROM Objeto WHERE ID_Objeto = ? LIMIT 1'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $objeto = $result->fetch_assoc();
        $stmt->close();

        return $objeto;
    }

    public function criar($dados)
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO Objeto (Numero_Tombamento, Nome, Marca, Modelo, Numero_Serie, ID_Instituicao, Status_Item)
             VALUES (?, ?, ?, ?, ?, ?, "DISPONIVEL")'
        );

        if (! $stmt) {
            throw new Exception('Erro na consulta ao banco');
        }

        $stmt->bind_param('issssi', $dados['numero_tombamento'], $dados['nome'], $dados['marca'], $dados['modelo'], $dados['numero_serie'], $dados['id_instituicao']);

        if (! $stmt->execute()) {
            if ($this->connection->errno === 1062) {
                throw new Exception('Número de tombamento ou série já registrados');
            }
            throw new Exception('Erro ao criar objeto');
        }

        $id = $stmt->insert_id;
        $stmt->close();

        return $id;
    }
}
