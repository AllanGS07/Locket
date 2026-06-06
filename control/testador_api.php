<?php

class TestadorApi
{
    private $urlBase = 'http://localhost/locket/control/roteador_principal.php';
    private $tokenAutenticacao = null;

    public function testarVerificacaoSaude()
    {
        echo "\n=== Verificacao de Saude ===\n";
        $resposta = $this->fazerRequisicaoHttp('GET', '/health');
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function testarRegistroUsuario($dadosUsuario)
    {
        echo "\n=== Registro de Usuario ===\n";
        $resposta = $this->fazerRequisicaoHttp('POST', '/auth/register', $dadosUsuario);
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        return $resposta;
    }

    public function testarLoginUsuario($email, $senha)
    {
        echo "\n=== Login ===\n";
        $dadosLogin = ['email' => $email, 'password' => $senha];
        $resposta = $this->fazerRequisicaoHttp('POST', '/auth/login', $dadosLogin);
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        if (isset($resposta['dados']['token'])) {
            $this->tokenAutenticacao = $resposta['dados']['token'];
            echo "Token obtido com sucesso!\n";
        }

        return $resposta;
    }

    public function testarListagemUsuarios()
    {
        if (!$this->tokenAutenticacao) {
            echo "Token nao disponivel. Faca login primeiro.\n";
            return;
        }

        echo "\n=== Listar Usuarios ===\n";
        $resposta = $this->fazerRequisicaoHttp('GET', '/usuarios');
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function testarListagemObjetos()
    {
        if (!$this->tokenAutenticacao) {
            echo "Token nao disponivel. Faca login primeiro.\n";
            return;
        }

        echo "\n=== Listar Objetos ===\n";
        $resposta = $this->fazerRequisicaoHttp('GET', '/objetos');
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function testarCriacaoObjeto($dadosObjeto)
    {
        if (!$this->tokenAutenticacao) {
            echo "Token nao disponivel. Faca login primeiro.\n";
            return;
        }

        echo "\n=== Criar Objeto ===\n";
        $resposta = $this->fazerRequisicaoHttp('POST', '/objetos', $dadosObjeto);
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    public function testarCriacaoEmprestimo($dadosEmprestimo)
    {
        if (!$this->tokenAutenticacao) {
            echo "Token nao disponivel. Faca login primeiro.\n";
            return;
        }

        echo "\n=== Criar Emprestimo ===\n";
        $resposta = $this->fazerRequisicaoHttp('POST', '/emprestimos', $dadosEmprestimo);
        echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    private function fazerRequisicaoHttp($metodo, $endpoint, $dados = null)
    {
        $url = $this->urlBase . $endpoint;

        $processoCurl = curl_init($url);

        curl_setopt($processoCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($processoCurl, CURLOPT_CUSTOMREQUEST, $metodo);

        if ($dados) {
            curl_setopt($processoCurl, CURLOPT_POSTFIELDS, json_encode($dados));
        }

        $cabecalhos = ['Content-Type: application/json'];

        if ($this->tokenAutenticacao) {
            $cabecalhos[] = 'Authorization: Bearer ' . $this->tokenAutenticacao;
        }

        curl_setopt($processoCurl, CURLOPT_HTTPHEADER, $cabecalhos);

        $resposta = curl_exec($processoCurl);
        curl_close($processoCurl);

        return json_decode($resposta, true);
    }
}

$testador = new TestadorApi();

$testador->testarVerificacaoSaude();

$novoUsuario = [
    'email' => 'teste@example.com',
    'password' => 'SenhaForte@123',
    'nome' => 'Joao Silva',
    'cpf' => '12345678901',
    'matricula' => '2024001',
    'data_nascimento' => '2005-01-15',
    'id_instituicao' => 1,
    'funcao' => 'ALUNO'
];
$testador->testarRegistroUsuario($novoUsuario);

$testador->testarLoginUsuario('teste@example.com', 'SenhaForte@123');

$testador->testarListagemUsuarios();

$testador->testarListagemObjetos();

$novoObjeto = [
    'numero_tombamento' => 1001,
    'nome' => 'Notebook',
    'marca' => 'Dell',
    'modelo' => 'Latitude 5000',
    'numero_serie' => 'DEL123456789',
    'id_instituicao' => 1
];
$testador->testarCriacaoObjeto($novoObjeto);

$novoEmprestimo = [
    'id_objeto' => 1,
    'data_retirada' => date('Y-m-d'),
    'data_devolucao_prevista' => date('Y-m-d', strtotime('+7 days'))
];
$testador->testarCriacaoEmprestimo($novoEmprestimo);
