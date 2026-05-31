<?php

class ControladorAutenticacao
{
    private $conexao;

    public function __construct($conexao)
    {
        $this->conexao = $conexao;
    }

    public function realizarLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        $entrada = json_decode(file_get_contents('php://input'), true);

        if (!$entrada) {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Corpo da requisicao invalido', 400)
            );
        }

        try {
            $email = ValidadorEntrada::desinfectarEmail($entrada['email'] ?? '');
            $senha = $entrada['password'] ?? '';

            if (empty($senha)) {
                throw new Exception('Senha e obrigatoria');
            }

            $instrucao = $this->conexao->prepare(
                'SELECT ID_Usuario, Senha_Hash, Funcao FROM Usuario WHERE Email = ? AND Ativo = TRUE LIMIT 1'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param('s', $email);
            $instrucao->execute();
            $resultado = $instrucao->get_result();

            if ($resultado->num_rows === 0) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Credenciais invalidas', 401)
                );
            }

            $usuario = $resultado->fetch_assoc();

            if (!password_verify($senha, $usuario['Senha_Hash'])) {
                RespostaApi::enviar(
                    RespostaApi::enviarErro('Credenciais invalidas', 401)
                );
            }

            $token = AutenticacaoJwt::gerarToken([
                'id' => $usuario['ID_Usuario'],
                'email' => $email,
                'funcao' => $usuario['Funcao']
            ]);

            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso([
                    'token' => $token,
                    'user_id' => $usuario['ID_Usuario'],
                    'funcao' => $usuario['Funcao']
                ], 'Login realizado com sucesso', 200)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }

    public function registrarNovoUsuario()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Metodo nao permitido', 405)
            );
        }

        $entrada = json_decode(file_get_contents('php://input'), true);

        if (!$entrada) {
            RespostaApi::enviar(
                RespostaApi::enviarErro('Corpo da requisicao invalido', 400)
            );
        }

        try {
            $email = ValidadorEntrada::desinfectarEmail($entrada['email'] ?? '');
            $senha = $entrada['password'] ?? '';
            $nome = ValidadorEntrada::desinfectarTexto($entrada['nome'] ?? '');
            $cpf = ValidadorEntrada::desinfectarCPF($entrada['cpf'] ?? '');
            $matricula = ValidadorEntrada::desinfectarTexto($entrada['matricula'] ?? '');
            $dataNascimento = ValidadorEntrada::desinfectarData($entrada['data_nascimento'] ?? '');
            $idInstituicao = ValidadorEntrada::desinfectarNumeroInteiro($entrada['id_instituicao'] ?? 0);
            $funcao = ValidadorEntrada::validarValorEstaEmEnumeracao(
                $entrada['funcao'] ?? '',
                ['ALUNO', 'PROFESSOR', 'TECNICO_ADMINISTRATIVO', 'TERCEIRIZADO']
            );

            ValidadorEntrada::validarSegurancaSenha($senha);

            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

            $instrucao = $this->conexao->prepare(
                'INSERT INTO Usuario (CPF, Matricula, Nome, Data_Nascimento, Email, Senha_Hash, Funcao, ID_Instituicao)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            if (!$instrucao) {
                throw new Exception('Erro na consulta ao banco');
            }

            $instrucao->bind_param(
                'sssssssi',
                $cpf,
                $matricula,
                $nome,
                $dataNascimento,
                $email,
                $senhaHash,
                $funcao,
                $idInstituicao
            );

            if (!$instrucao->execute()) {
                if ($this->conexao->errno === 1062) {
                    throw new Exception('Email ou CPF ja registrados');
                }
                throw new Exception('Erro ao registrar usuario');
            }

            $idUsuario = $instrucao->insert_id;
            $instrucao->close();

            RespostaApi::enviar(
                RespostaApi::enviarSucesso([
                    'user_id' => $idUsuario,
                    'email' => $email,
                    'funcao' => $funcao
                ], 'Usuario registrado com sucesso', 201)
            );

        } catch (Exception $excecao) {
            RespostaApi::enviar(
                RespostaApi::enviarErro($excecao->getMessage(), 400)
            );
        }
    }
}
