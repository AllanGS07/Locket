<?php

class ControladorUsuario
{
    private $conexao;
    private $dadosAutenticacao;

    public function __construct($conexao, $dadosAutenticacao)
    {
        $this->conexao = $conexao;
        $this->dadosAutenticacao = $dadosAutenticacao;
    }

    public function listarUsuariosInstituicao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $idInstituicao = $this->dadosAutenticacao['id'] ?? 0;

            $instrucao = $this->conexao->prepare(
                'SELECT ID_Usuario, Nome, Funcao, Data_Criacao FROM vw_usuario_publico WHERE ID_Instituicao = ? LIMIT 100'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('i', $idInstituicao);
            $instrucao->execute();
            $resultado = $instrucao->get_result();

            $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso($usuarios, 'Usuarios listados com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function obterDetalhesUsuario($idUsuario)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $idUsuario = ValidadorEntrada::desinfectarNumeroInteiro($idUsuario);

            if ($this->dadosAutenticacao['id'] != $idUsuario && $this->dadosAutenticacao['funcao'] !== 'PROFESSOR') {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Acesso negado', 403)
                );
            }

            $instrucao = $this->conexao->prepare(
                'SELECT ID_Usuario, CPF, Nome, Email, Funcao, Data_Criacao FROM vw_usuario_publico WHERE ID_Usuario = ? LIMIT 1'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('i', $idUsuario);
            $instrucao->execute();
            $resultado = $instrucao->get_result();

            if ($resultado->num_rows === 0) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Usuario nao encontrado', 404)
                );
            }

            $usuario = $resultado->fetch_assoc();
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso($usuario, 'Usuario obtido com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function atualizarDadosUsuario($idUsuario)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $idUsuario = ValidadorEntrada::desinfectarNumeroInteiro($idUsuario);

            if ($this->dadosAutenticacao['id'] != $idUsuario) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Acesso negado', 403)
                );
            }

            $entrada = json_decode(file_get_contents('php://input'), true);

            if (!$entrada) {
                throw new Exception('Corpo da requisicao invalido');
            }

            $nome = ValidadorEntrada::desinfectarTexto($entrada['nome'] ?? null);

            if (!$nome) {
                throw new Exception('Nome e obrigatorio');
            }

            $instrucao = $this->conexao->prepare(
                'UPDATE Usuario SET Nome = ? WHERE ID_Usuario = ? AND Ativo = TRUE'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('si', $nome, $idUsuario);

            if (!$instrucao->execute()) {
                throw new Exception('Erro ao atualizar usuario');
            }

            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso(['id' => $idUsuario], 'Usuario atualizado com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }
}
