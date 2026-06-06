<?php

class ControladorEmprestimo
{
    private $conexao;
    private $dadosAutenticacao;

    public function __construct($conexao, $dadosAutenticacao)
    {
        $this->conexao = $conexao;
        $this->dadosAutenticacao = $dadosAutenticacao;
    }

    public function listarEmprestimos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $idUsuario = ValidadorEntrada::desinfectarNumeroInteiro($_GET['usuario_id'] ?? $this->dadosAutenticacao['id']);

            if ($this->dadosAutenticacao['id'] !== $idUsuario && $this->dadosAutenticacao['funcao'] === 'ALUNO') {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Acesso negado', 403)
                );
            }

            $instrucao = $this->conexao->prepare(
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

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $idUsuarioFiltro = ($this->dadosAutenticacao['funcao'] === 'ALUNO') ? 0 : 0;
            $instrucao->bind_param('ii', $idUsuario, $idUsuario);
            $instrucao->execute();
            $resultado = $instrucao->get_result();

            $emprestimos = $resultado->fetch_all(MYSQLI_ASSOC);
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso($emprestimos, 'Emprestimos listados com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function criarNovoEmprestimo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $entrada = json_decode(file_get_contents('php://input'), true);

            if (!$entrada) {
                throw new Exception('Corpo da requisicao invalido');
            }

            $idUsuario = ValidadorEntrada::desinfectarNumeroInteiro($entrada['id_usuario'] ?? $this->dadosAutenticacao['id']);
            $idObjeto = ValidadorEntrada::desinfectarNumeroInteiro($entrada['id_objeto'] ?? 0);
            $dataRetirada = ValidadorEntrada::desinfectarData($entrada['data_retirada'] ?? date('Y-m-d'));
            $dataDevolucaoPrevista = ValidadorEntrada::desinfectarData($entrada['data_devolucao_prevista'] ?? '');

            if (!$dataDevolucaoPrevista) {
                throw new Exception('Data de devolucao prevista e obrigatoria');
            }

            if (strtotime($dataDevolucaoPrevista) <= strtotime($dataRetirada)) {
                throw new Exception('Data de devolucao deve ser posterior a data de retirada');
            }

            if ($this->dadosAutenticacao['id'] !== $idUsuario && $this->dadosAutenticacao['funcao'] === 'ALUNO') {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Acesso negado', 403)
                );
            }

            $instrucaoVerificacao = $this->conexao->prepare(
                'SELECT ObjetoEstaDisponivel(?) AS disponivel'
            );

            if (!$instrucaoVerificacao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucaoVerificacao->bind_param('i', $idObjeto);
            $instrucaoVerificacao->execute();
            $resultadoVerificacao = $instrucaoVerificacao->get_result();

            $objetoDisponivel = $resultadoVerificacao->fetch_assoc();
            $instrucaoVerificacao->close();

            if (!$objetoDisponivel['disponivel']) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Objeto nao esta disponivel', 400)
                );
            }

            $instrucao = $this->conexao->prepare(
                'INSERT INTO Emprestimos (ID_Usuario, ID_Objeto, Data_Retirada, Data_Devolucao_Prevista, Status_Emprestimo)
                 VALUES (?, ?, ?, ?, "PENDENTE")'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('iiss', $idUsuario, $idObjeto, $dataRetirada, $dataDevolucaoPrevista);

            if (!$instrucao->execute()) {
                throw new Exception('Erro ao criar emprestimo');
            }

            $idEmprestimo = $instrucao->insert_id;

            $instrucaoAtualizacao = $this->conexao->prepare(
                'UPDATE Objeto SET Status_Item = "EMPRESTADO" WHERE ID_Objeto = ?'
            );
            $instrucaoAtualizacao->bind_param('i', $idObjeto);
            $instrucaoAtualizacao->execute();
            $instrucaoAtualizacao->close();

            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso(['id' => $idEmprestimo], 'Emprestimo criado com sucesso', 201)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function devolverEmprestimo($idEmprestimo)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $idEmprestimo = ValidadorEntrada::desinfectarNumeroInteiro($idEmprestimo);
            $dataDevolucao = date('Y-m-d');

            $instrucao = $this->conexao->prepare(
                'UPDATE Emprestimos 
                 SET Data_Devolucao_Real = ?, Status_Emprestimo = "DEVOLVIDO"
                 WHERE ID_Emprestimo = ?'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('si', $dataDevolucao, $idEmprestimo);

            if (!$instrucao->execute()) {
                throw new Exception('Erro ao devolver emprestimo');
            }

            $instrucaoObter = $this->conexao->prepare(
                'SELECT ID_Objeto FROM Emprestimos WHERE ID_Emprestimo = ?'
            );
            $instrucaoObter->bind_param('i', $idEmprestimo);
            $instrucaoObter->execute();
            $resultadoObter = $instrucaoObter->get_result();

            if ($resultadoObter->num_rows > 0) {
                $emprestimo = $resultadoObter->fetch_assoc();

                $instrucaoAtualizacao = $this->conexao->prepare(
                    'UPDATE Objeto SET Status_Item = "DISPONIVEL" WHERE ID_Objeto = ?'
                );
                $instrucaoAtualizacao->bind_param('i', $emprestimo['ID_Objeto']);
                $instrucaoAtualizacao->execute();
                $instrucaoAtualizacao->close();
            }

            $instrucaoObter->close();
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso(['id' => $idEmprestimo], 'Emprestimo devolvido com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }
}
