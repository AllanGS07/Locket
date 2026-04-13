<?php
// Script de teste da API
// Use este arquivo para testar os endpoints da API

class ApiTester
{
    private $baseUrl = 'http://localhost/locket/control/index.php';
    private $token = null;
    
    public function testHealthCheck()
    {
        echo "\n=== Health Check ===\n";
        $response = $this->makeRequest('GET', '/health');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    public function testRegister($userData)
    {
        echo "\n=== Registro de Usuário ===\n";
        $response = $this->makeRequest('POST', '/auth/register', $userData);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        return $response;
    }
    
    public function testLogin($email, $password)
    {
        echo "\n=== Login ===\n";
        $loginData = ['email' => $email, 'password' => $password];
        $response = $this->makeRequest('POST', '/auth/login', $loginData);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            echo "Token obtido com sucesso!\n";
        }
        
        return $response;
    }
    
    public function testListUsuarios()
    {
        if (! $this->token) {
            echo "Token não disponível. Faça login primeiro.\n";
            return;
        }
        
        echo "\n=== Listar Usuários ===\n";
        $response = $this->makeRequest('GET', '/usuarios');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    public function testListObjetos()
    {
        if (! $this->token) {
            echo "Token não disponível. Faça login primeiro.\n";
            return;
        }
        
        echo "\n=== Listar Objetos ===\n";
        $response = $this->makeRequest('GET', '/objetos');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    public function testCreateObjeto($objetoData)
    {
        if (! $this->token) {
            echo "Token não disponível. Faça login primeiro.\n";
            return;
        }
        
        echo "\n=== Criar Objeto ===\n";
        $response = $this->makeRequest('POST', '/objetos', $objetoData);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    public function testCreateEmprestimo($emprestimoData)
    {
        if (! $this->token) {
            echo "Token não disponível. Faça login primeiro.\n";
            return;
        }
        
        echo "\n=== Criar Empréstimo ===\n";
        $response = $this->makeRequest('POST', '/emprestimos', $emprestimoData);
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $headers = ['Content-Type: application/json'];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}

// === EXEMPLO DE USO ===

$tester = new ApiTester();

// Testar saúde da API
$tester->testHealthCheck();

// Registrar novo usuário
$novoUsuario = [
    'email' => 'teste@example.com',
    'password' => 'SenhaForte@123',
    'nome' => 'João Silva',
    'cpf' => '12345678901',
    'matricula' => '2024001',
    'data_nascimento' => '2005-01-15',
    'id_instituicao' => 1,
    'funcao' => 'ALUNO'
];
$tester->testRegister($novoUsuario);

// Fazer login
$tester->testLogin('teste@example.com', 'SenhaForte@123');

// Listar usuários
$tester->testListUsuarios();

// Listar objetos
$tester->testListObjetos();

// Criar novo objeto (requer TECNICO_ADMINISTRATIVO)
$novoObjeto = [
    'numero_tombamento' => 1001,
    'nome' => 'Notebook',
    'marca' => 'Dell',
    'modelo' => 'Latitude 5000',
    'numero_serie' => 'DEL123456789',
    'id_instituicao' => 1
];
$tester->testCreateObjeto($novoObjeto);

// Criar empréstimo
$novoEmprestimo = [
    'id_objeto' => 1,
    'data_retirada' => date('Y-m-d'),
    'data_devolucao_prevista' => date('Y-m-d', strtotime('+7 days'))
];
$tester->testCreateEmprestimo($novoEmprestimo);
