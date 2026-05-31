<?php
/**
 * Script de Validação: Integração PHP + Banco de Dados
 * Testa: Conexão, Gatilhos, Transações, Stored Procedures
 */

require_once 'DatabaseConfig.php';

class ValidationTester
{
    private $conn;
    private $results = [];
    private $errors = [];

    public function __construct()
    {
        try {
            $this->conn = DatabaseConfig::getConnection();
            echo "✓ Conexão com banco de dados estabelecida\n\n";
        } catch (Exception $e) {
            die("✗ Erro ao conectar: " . $e->getMessage() . "\n");
        }
    }

    /**
     * 1. Testa Gatilhos (Triggers)
     */
    public function testTriggers()
    {
        echo "========== TESTANDO GATILHOS (TRIGGERS) ==========\n\n";

        // Verificar se os triggers existem
        $triggers = ['trg_atualizar_status_objeto_ao_emprestar', 'trg_verificar_atraso_emprestimo', 'trg_liberar_objeto_ao_devolver'];
        
        foreach ($triggers as $trigger) {
            $result = $this->conn->query("SHOW TRIGGERS WHERE `Trigger` = '$trigger'");
            if ($result && $result->num_rows > 0) {
                echo "✓ Trigger '$trigger' existe no banco\n";
                $this->results[] = "Trigger $trigger: OK";
            } else {
                echo "✗ Trigger '$trigger' NÃO encontrado\n";
                $this->errors[] = "Trigger $trigger não existe";
            }
        }
        echo "\n";
    }

    /**
     * 2. Testa Stored Procedures
     */
    public function testStoredProcedures()
    {
        echo "========== TESTANDO STORED PROCEDURES ==========\n\n";

        $procedures = ['proc_realizar_emprestimo', 'proc_devolver_emprestimo'];
        
        foreach ($procedures as $procedure) {
            $result = $this->conn->query("SHOW PROCEDURE STATUS WHERE `Name` = '$procedure'");
            if ($result && $result->num_rows > 0) {
                echo "✓ Procedure '$procedure' existe no banco\n";
                $this->results[] = "Procedure $procedure: OK";
            } else {
                echo "✗ Procedure '$procedure' NÃO encontrada\n";
                $this->errors[] = "Procedure $procedure não existe";
            }
        }
        echo "\n";
    }

    /**
     * 3. Testa Views
     */
    public function testViews()
    {
        echo "========== TESTANDO VIEWS ==========\n\n";

        $views = ['vw_emprestimos_atrasados', 'vw_relatorio_emprestimos_usuario'];
        
        foreach ($views as $view) {
            $result = $this->conn->query("SELECT * FROM INFORMATION_SCHEMA.VIEWS WHERE TABLE_NAME = '$view' AND TABLE_SCHEMA = 'locket_db'");
            if ($result && $result->num_rows > 0) {
                echo "✓ View '$view' existe no banco\n";
                $this->results[] = "View $view: OK";
            } else {
                echo "✗ View '$view' NÃO encontrada\n";
                $this->errors[] = "View $view não existe";
            }
        }
        echo "\n";
    }

    /**
     * 4. Testa Transação: Realizar Empréstimo
     */
    public function testTransactionEmprestimo()
    {
        echo "========== TESTANDO TRANSAÇÃO: REALIZAR EMPRÉSTIMO ==========\n\n";

        try {
            // Encontrar um objeto disponível
            $objResult = $this->conn->query("SELECT ID_Objeto FROM Objeto WHERE Status_Item = 'DISPONIVEL' LIMIT 1");
            if (!$objResult || $objResult->num_rows === 0) {
                echo "⚠ Nenhum objeto disponível para teste\n\n";
                return;
            }
            $obj = $objResult->fetch_assoc();
            $idObjeto = $obj['ID_Objeto'];

            // Encontrar um usuário ativo
            $userResult = $this->conn->query("SELECT ID_Usuario FROM Usuario WHERE Ativo = TRUE LIMIT 1");
            if (!$userResult || $userResult->num_rows === 0) {
                echo "⚠ Nenhum usuário ativo para teste\n\n";
                return;
            }
            $user = $userResult->fetch_assoc();
            $idUsuario = $user['ID_Usuario'];

            // Chamar a stored procedure
            $hoje = date('Y-m-d');
            $dataDevolvao = date('Y-m-d', strtotime('+7 days'));
            
            $stmt = $this->conn->prepare("CALL proc_realizar_emprestimo(?, ?, ?, ?, @ID_Emprestimo, @Mensagem)");
            $stmt->bind_param("iiss", $idUsuario, $idObjeto, $hoje, $dataDevolvao);
            
            if ($stmt->execute()) {
                // Recuperar valores de saída
                $result = $this->conn->query("SELECT @ID_Emprestimo AS ID_Emprestimo, @Mensagem AS Mensagem");
                $row = $result->fetch_assoc();
                
                echo "✓ Empréstimo criado com sucesso\n";
                echo "  ID: " . $row['ID_Emprestimo'] . "\n";
                echo "  Mensagem: " . $row['Mensagem'] . "\n";
                
                // Validar se o objeto foi atualizado para EMPRESTADO (trigger em ação)
                $checkObj = $this->conn->query("SELECT Status_Item FROM Objeto WHERE ID_Objeto = $idObjeto");
                $objStatus = $checkObj->fetch_assoc();
                
                if ($objStatus['Status_Item'] === 'EMPRESTADO') {
                    echo "✓ TRIGGER ATIVADO: Status do objeto foi atualizado para 'EMPRESTADO'\n";
                    $this->results[] = "Trigger ao emprestar: OK";
                } else {
                    echo "✗ TRIGGER NÃO ATIVOU: Status do objeto permanece como '" . $objStatus['Status_Item'] . "'\n";
                    $this->errors[] = "Trigger ao emprestar não funcionou";
                }
                
                $this->results[] = "Transação emprestar: OK";
            } else {
                echo "✗ Erro ao executar stored procedure: " . $this->conn->error . "\n";
                $this->errors[] = "Transação emprestar falhou: " . $this->conn->error;
            }
            $stmt->close();
            
        } catch (Exception $e) {
            echo "✗ Erro: " . $e->getMessage() . "\n";
            $this->errors[] = "Teste de empréstimo falhou: " . $e->getMessage();
        }
        echo "\n";
    }

    /**
     * 5. Testa Transação: Devolver Empréstimo
     */
    public function testTransactionDevolucao()
    {
        echo "========== TESTANDO TRANSAÇÃO: DEVOLVER EMPRÉSTIMO ==========\n\n";

        try {
            // Encontrar um empréstimo em andamento
            $empResult = $this->conn->query("SELECT ID_Emprestimo, ID_Objeto FROM Emprestimos WHERE Status_Emprestimo = 'EM_ANDAMENTO' LIMIT 1");
            
            if (!$empResult || $empResult->num_rows === 0) {
                echo "⚠ Nenhum empréstimo em andamento para teste\n\n";
                return;
            }
            
            $emp = $empResult->fetch_assoc();
            $idEmprestimo = $emp['ID_Emprestimo'];
            $idObjeto = $emp['ID_Objeto'];
            
            // Chamar a stored procedure de devolução
            $dataDevolvao = date('Y-m-d');
            
            $stmt = $this->conn->prepare("CALL proc_devolver_emprestimo(?, ?, @Sucesso, @Mensagem)");
            $stmt->bind_param("is", $idEmprestimo, $dataDevolvao);
            
            if ($stmt->execute()) {
                // Recuperar valores de saída
                $result = $this->conn->query("SELECT @Sucesso AS Sucesso, @Mensagem AS Mensagem");
                $row = $result->fetch_assoc();
                
                echo "✓ Devolução processada com sucesso\n";
                echo "  Status: " . ($row['Sucesso'] ? 'SIM' : 'NÃO') . "\n";
                echo "  Mensagem: " . $row['Mensagem'] . "\n";
                
                // Validar se o objeto foi liberado (trigger em ação)
                $checkObj = $this->conn->query("SELECT Status_Item FROM Objeto WHERE ID_Objeto = $idObjeto");
                $objStatus = $checkObj->fetch_assoc();
                
                if ($objStatus['Status_Item'] === 'DISPONIVEL') {
                    echo "✓ TRIGGER ATIVADO: Status do objeto foi atualizado para 'DISPONIVEL'\n";
                    $this->results[] = "Trigger ao devolver: OK";
                } else {
                    echo "✗ TRIGGER NÃO ATIVOU: Status do objeto é '" . $objStatus['Status_Item'] . "'\n";
                    $this->errors[] = "Trigger ao devolver não funcionou";
                }
                
                $this->results[] = "Transação devolver: OK";
            } else {
                echo "✗ Erro ao executar stored procedure: " . $this->conn->error . "\n";
                $this->errors[] = "Transação devolver falhou: " . $this->conn->error;
            }
            $stmt->close();
            
        } catch (Exception $e) {
            echo "✗ Erro: " . $e->getMessage() . "\n";
            $this->errors[] = "Teste de devolução falhou: " . $e->getMessage();
        }
        echo "\n";
    }

    /**
     * 6. Testa Charset e Encoding
     */
    public function testCharset()
    {
        echo "========== TESTANDO CHARSET (UTF-8) ==========\n\n";

        $result = $this->conn->query("SELECT @@character_set_client, @@character_set_connection, @@character_set_database");
        $row = $result->fetch_assoc();
        
        if ($row['@@character_set_client'] === 'utf8mb4') {
            echo "✓ Character set do cliente: " . $row['@@character_set_client'] . "\n";
            $this->results[] = "Charset client: OK";
        } else {
            echo "⚠ Character set do cliente: " . $row['@@character_set_client'] . " (esperado: utf8mb4)\n";
        }
        
        if ($row['@@character_set_connection'] === 'utf8mb4') {
            echo "✓ Character set da conexão: " . $row['@@character_set_connection'] . "\n";
            $this->results[] = "Charset connection: OK";
        } else {
            echo "⚠ Character set da conexão: " . $row['@@character_set_connection'] . " (esperado: utf8mb4)\n";
        }
        
        if ($row['@@character_set_database'] === 'utf8mb4') {
            echo "✓ Character set do banco: " . $row['@@character_set_database'] . "\n";
            $this->results[] = "Charset database: OK";
        } else {
            echo "⚠ Character set do banco: " . $row['@@character_set_database'] . " (esperado: utf8mb4)\n";
        }
        echo "\n";
    }

    /**
     * 7. Testa Queries Básicas
     */
    public function testBasicQueries()
    {
        echo "========== TESTANDO QUERIES BÁSICAS ==========\n\n";

        $tables = [
            'Usuario' => 'SELECT COUNT(*) as count FROM Usuario',
            'Objeto' => 'SELECT COUNT(*) as count FROM Objeto',
            'Emprestimos' => 'SELECT COUNT(*) as count FROM Emprestimos'
        ];

        foreach ($tables as $table => $query) {
            $result = $this->conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                echo "✓ Tabela '$table': " . $row['count'] . " registros\n";
                $this->results[] = "Tabela $table: OK";
            } else {
                echo "✗ Erro ao consultar tabela '$table': " . $this->conn->error . "\n";
                $this->errors[] = "Tabela $table erro: " . $this->conn->error;
            }
        }
        echo "\n";
    }

    /**
     * Exibir Relatório Final
     */
    public function printReport()
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════╗\n";
        echo "║           RELATÓRIO DE VALIDAÇÃO FINAL             ║\n";
        echo "╚════════════════════════════════════════════════════╝\n\n";

        echo "✓ SUCESSOS (" . count($this->results) . "):\n";
        foreach ($this->results as $result) {
            echo "  • $result\n";
        }

        if (count($this->errors) > 0) {
            echo "\n✗ ERROS (" . count($this->errors) . "):\n";
            foreach ($this->errors as $error) {
                echo "  • $error\n";
            }
        } else {
            echo "\n✓ Nenhum erro encontrado!\n";
        }

        echo "\n";
        echo "╔════════════════════════════════════════════════════╗\n";
        if (count($this->errors) === 0) {
            echo "║  ✓ INTEGRAÇÃO PHP-BANCO FUNCIONANDO PERFEITAMENTE  ║\n";
        } else {
            echo "║  ⚠ INTEGRAÇÃO COM PROBLEMAS - VEJA ERROS ACIMA    ║\n";
        }
        echo "╚════════════════════════════════════════════════════╝\n\n";
    }

    /**
     * Executar todos os testes
     */
    public function runAllTests()
    {
        echo "\n╔════════════════════════════════════════════════════╗\n";
        echo "║     VALIDAÇÃO: INTEGRAÇÃO PHP + BANCO DE DADOS     ║\n";
        echo "║         Gatilhos, Transações e Views              ║\n";
        echo "╚════════════════════════════════════════════════════╝\n\n";

        $this->testBasicQueries();
        $this->testCharset();
        $this->testTriggers();
        $this->testStoredProcedures();
        $this->testViews();
        $this->testTransactionEmprestimo();
        $this->testTransactionDevolucao();
        $this->printReport();
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Executar validação
$tester = new ValidationTester();
$tester->runAllTests();
