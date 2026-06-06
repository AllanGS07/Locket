<?php

class ControladorObjeto
{
    private $conexao;
    private $dadosAutenticacao;

    public function __construct($conexao, $dadosAutenticacao)
    {
        $this->conexao = $conexao;
        $this->dadosAutenticacao = $dadosAutenticacao;
    }

    public function listarObjetos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $instrucao = $this->conexao->prepare(
                'SELECT ID_Objeto, Nome, Marca, Modelo, Status_Item FROM vw_objetos_publicos ORDER BY Nome ASC LIMIT 100'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->execute();
            $resultado = $instrucao->get_result();

            $objetos = $resultado->fetch_all(MYSQLI_ASSOC);
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso($objetos, 'Objetos listados com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function obterDetalhesObjeto($idObjeto)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        try {
            $idObjeto = ValidadorEntrada::desinfectarNumeroInteiro($idObjeto);

            $instrucao = $this->conexao->prepare(
                'SELECT ID_Objeto, Nome, Marca, Modelo, Numero_Tombamento, Status_Item FROM Objeto WHERE ID_Objeto = ? LIMIT 1'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('i', $idObjeto);
            $instrucao->execute();
            $resultado = $instrucao->get_result();

            if ($resultado->num_rows === 0) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Objeto nao encontrado', 404)
                );
            }

            $objeto = $resultado->fetch_assoc();
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso($objeto, 'Objeto obtido com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function criarNovoObjeto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        if ($this->dadosAutenticacao['funcao'] !== 'TECNICO_ADMINISTRATIVO') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Acesso negado', 403)
            );
        }

        try {
            $entrada = json_decode(file_get_contents('php://input'), true);

            if (!$entrada) {
                throw new Exception('Corpo da requisicao invalido');
            }

            $numeroTombamento = ValidadorEntrada::desinfectarNumeroInteiro($entrada['numero_tombamento'] ?? 0);
            $nome = ValidadorEntrada::desinfectarTexto($entrada['nome'] ?? '');
            $marca = ValidadorEntrada::desinfectarTexto($entrada['marca'] ?? '');
            $modelo = ValidadorEntrada::desinfectarTexto($entrada['modelo'] ?? '');
            $numeroSerie = ValidadorEntrada::desinfectarTexto($entrada['numero_serie'] ?? '');
            $idInstituicao = ValidadorEntrada::desinfectarNumeroInteiro($entrada['id_instituicao'] ?? 0);

            $errosValidacao = [];
            if (!$numeroTombamento) $errosValidacao[] = 'Numero de tombamento e obrigatorio';
            if (!$nome) $errosValidacao[] = 'Nome e obrigatorio';
            if (!$marca) $errosValidacao[] = 'Marca e obrigatoria';
            if (!$modelo) $errosValidacao[] = 'Modelo e obrigatorio';
            if (!$numeroSerie) $errosValidacao[] = 'Numero de serie e obrigatorio';

            if (!empty($errosValidacao)) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Validacao falhou', 400, $errosValidacao)
                );
            }

            $instrucao = $this->conexao->prepare(
                'INSERT INTO Objeto (Numero_Tombamento, Nome, Marca, Modelo, Numero_Serie, ID_Instituicao, Status_Item)
                 VALUES (?, ?, ?, ?, ?, ?, "DISPONIVEL")'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('issssi', $numeroTombamento, $nome, $marca, $modelo, $numeroSerie, $idInstituicao);

            if (!$instrucao->execute()) {
                if ($this->conexao->errno === 1062) {
                    throw new Exception('Numero de tombamento ou serie ja registrados');
                }
                throw new Exception('Erro ao criar objeto');
            }

            $idObjeto = $instrucao->insert_id;
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso(['id' => $idObjeto], 'Objeto criado com sucesso', 201)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }
}
