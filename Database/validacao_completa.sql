-- =====================================================
-- SCRIPT DE VALIDAÇÃO COMPLETA
-- Integração PHP + Banco de Dados (com Gatilhos e Transações)
-- =====================================================

USE locket_db;

-- =====================================================
-- 1. VALIDAÇÃO ESTRUTURAL
-- =====================================================

PRINT '========== 1. VALIDANDO GATILHOS ==========';
SHOW TRIGGERS IN locket_db;

PRINT '';
PRINT '========== 2. VALIDANDO PROCEDURES ==========';
SHOW PROCEDURE STATUS WHERE db = 'locket_db';

PRINT '';
PRINT '========== 3. VALIDANDO VIEWS ==========';
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS 
WHERE TABLE_SCHEMA = 'locket_db'
ORDER BY TABLE_NAME;

PRINT '';
PRINT '========== 4. CONTANDO REGISTROS NAS TABELAS ==========';
SELECT 'Usuario' as Tabela, COUNT(*) as Registros FROM Usuario
UNION ALL
SELECT 'Objeto', COUNT(*) FROM Objeto
UNION ALL
SELECT 'Emprestimos', COUNT(*) FROM Emprestimos;

PRINT '';
PRINT '========== 5. VALIDANDO CHARSET ==========';
SELECT @@character_set_client as Charset_Client,
       @@character_set_connection as Charset_Connection,
       @@character_set_database as Charset_Database;

-- =====================================================
-- 2. TESTE: CRIAR EMPRÉSTIMO COM TRIGGER
-- =====================================================

PRINT '';
PRINT '========== TESTE 1: CRIAR EMPRÉSTIMO COM TRIGGER ==========';

-- Encontrar um objeto disponível
SELECT @ID_Objeto := ID_Objeto FROM Objeto 
WHERE Status_Item = 'DISPONIVEL' LIMIT 1;

-- Encontrar um usuário ativo
SELECT @ID_Usuario := ID_Usuario FROM Usuario 
WHERE Ativo = TRUE LIMIT 1;

-- Mostrar valores selecionados
PRINT '';
PRINT CONCAT('Objeto ID: ', @ID_Objeto);
PRINT CONCAT('Usuario ID: ', @ID_Usuario);

IF @ID_Objeto IS NOT NULL AND @ID_Usuario IS NOT NULL THEN
    -- Mostrar status ANTES
    PRINT '';
    PRINT 'STATUS ANTES DO EMPRÉSTIMO:';
    SELECT ID_Objeto, Nome, Status_Item FROM Objeto WHERE ID_Objeto = @ID_Objeto;
    
    -- Chamar procedure
    PRINT '';
    PRINT 'EXECUTANDO: proc_realizar_emprestimo...';
    CALL proc_realizar_emprestimo(
        @ID_Usuario,
        @ID_Objeto,
        CURDATE(),
        DATE_ADD(CURDATE(), INTERVAL 7 DAY),
        @ID_Emprestimo,
        @Mensagem_Emprestimo
    );
    
    -- Mostrar resultado
    PRINT '';
    PRINT 'RESULTADO:';
    SELECT @ID_Emprestimo as ID_Emprestimo, 
           @Mensagem_Emprestimo as Mensagem;
    
    -- Mostrar status DEPOIS
    PRINT '';
    PRINT 'STATUS APÓS O EMPRÉSTIMO (TRIGGER ATIVADO?):';
    SELECT ID_Objeto, Nome, Status_Item FROM Objeto WHERE ID_Objeto = @ID_Objeto;
    
    -- Mostrar registro do empréstimo
    PRINT '';
    PRINT 'REGISTRO DO EMPRÉSTIMO:';
    SELECT ID_Emprestimo, ID_Usuario, ID_Objeto, 
           Data_Retirada, Data_Devolucao_Prevista, 
           Status_Emprestimo FROM Emprestimos 
    WHERE ID_Emprestimo = @ID_Emprestimo;
    
    PRINT '';
    IF (SELECT Status_Item FROM Objeto WHERE ID_Objeto = @ID_Objeto) = 'EMPRESTADO' THEN
        PRINT '✓ TRIGGER FUNCIONANDO: Status mudou para EMPRESTADO';
    ELSE
        PRINT '✗ TRIGGER NÃO FUNCIONOU: Status não foi alterado';
    END IF;
    
ELSE
    PRINT '✗ Não foi possível encontrar objeto disponível ou usuário ativo para teste';
END IF;

-- =====================================================
-- 3. TESTE: DEVOLVER EMPRÉSTIMO COM TRIGGER
-- =====================================================

PRINT '';
PRINT '========== TESTE 2: DEVOLVER EMPRÉSTIMO COM TRIGGER ==========';

-- Encontrar um empréstimo em andamento
SELECT @ID_Emprestimo_Devolucao := ID_Emprestimo,
       @ID_Objeto_Devolucao := ID_Objeto
FROM Emprestimos 
WHERE Status_Emprestimo = 'EM_ANDAMENTO' LIMIT 1;

PRINT '';
PRINT CONCAT('Emprestimo ID: ', @ID_Emprestimo_Devolucao);
PRINT CONCAT('Objeto ID: ', @ID_Objeto_Devolucao);

IF @ID_Emprestimo_Devolucao IS NOT NULL THEN
    -- Mostrar status ANTES
    PRINT '';
    PRINT 'STATUS ANTES DA DEVOLUÇÃO:';
    SELECT ID_Objeto, Nome, Status_Item FROM Objeto WHERE ID_Objeto = @ID_Objeto_Devolucao;
    SELECT ID_Emprestimo, Status_Emprestimo FROM Emprestimos WHERE ID_Emprestimo = @ID_Emprestimo_Devolucao;
    
    -- Chamar procedure
    PRINT '';
    PRINT 'EXECUTANDO: proc_devolver_emprestimo...';
    CALL proc_devolver_emprestimo(
        @ID_Emprestimo_Devolucao,
        CURDATE(),
        @Sucesso_Devolucao,
        @Mensagem_Devolucao
    );
    
    -- Mostrar resultado
    PRINT '';
    PRINT 'RESULTADO:';
    SELECT @Sucesso_Devolucao as Sucesso,
           @Mensagem_Devolucao as Mensagem;
    
    -- Mostrar status DEPOIS
    PRINT '';
    PRINT 'STATUS APÓS A DEVOLUÇÃO (TRIGGER ATIVADO?):';
    SELECT ID_Objeto, Nome, Status_Item FROM Objeto WHERE ID_Objeto = @ID_Objeto_Devolucao;
    SELECT ID_Emprestimo, Status_Emprestimo FROM Emprestimos WHERE ID_Emprestimo = @ID_Emprestimo_Devolucao;
    
    PRINT '';
    IF (SELECT Status_Item FROM Objeto WHERE ID_Objeto = @ID_Objeto_Devolucao) = 'DISPONIVEL' THEN
        PRINT '✓ TRIGGER FUNCIONANDO: Status mudou para DISPONIVEL';
    ELSE
        PRINT '✗ TRIGGER NÃO FUNCIONOU: Status não foi alterado';
    END IF;
    
ELSE
    PRINT '✗ Não há empréstimos em andamento para teste de devolução';
END IF;

-- =====================================================
-- 4. TESTE: VALIDAÇÃO DE TRANSAÇÕES (ROLLBACK)
-- =====================================================

PRINT '';
PRINT '========== TESTE 3: VALIDAÇÃO DE TRANSAÇÕES ==========';

-- Tentar emprestar um objeto que não está disponível (deve falhar)
SELECT @ID_Objeto_Indisponivel := ID_Objeto FROM Objeto 
WHERE Status_Item != 'DISPONIVEL' LIMIT 1;

PRINT '';
PRINT CONCAT('Tentando emprestar objeto indisponível (ID: ', @ID_Objeto_Indisponivel, ')...');

IF @ID_Objeto_Indisponivel IS NOT NULL THEN
    CALL proc_realizar_emprestimo(
        @ID_Usuario,
        @ID_Objeto_Indisponivel,
        CURDATE(),
        DATE_ADD(CURDATE(), INTERVAL 7 DAY),
        @ID_Emp_Falha,
        @Msg_Falha
    );
    
    PRINT '';
    SELECT @ID_Emp_Falha as ID_Emprestimo,
           @Msg_Falha as Mensagem;
    
    IF @ID_Emp_Falha = -1 THEN
        PRINT '✓ TRANSAÇÃO FUNCIONANDO: Erro detectado e tratado';
    ELSE IF @ID_Emp_Falha IS NULL THEN
        PRINT '✓ TRANSAÇÃO FUNCIONANDO: Procedure falhou como esperado';
    ELSE
        PRINT '✗ TRANSAÇÃO NÃO FUNCIONOU: Empréstimo foi criado mesmo inválido';
    END IF;
    END IF;
ELSE
    PRINT '✗ Não há objetos indisponíveis para teste';
END IF;

-- =====================================================
-- 5. TESTE: VIEWS
-- =====================================================

PRINT '';
PRINT '========== TESTE 4: VALIDAÇÃO DE VIEWS ==========';

PRINT '';
PRINT 'Empréstimos Atrasados (vw_emprestimos_atrasados):';
SELECT * FROM vw_emprestimos_atrasados LIMIT 10;

PRINT '';
PRINT 'Total de Empréstimos Atrasados:';
SELECT COUNT(*) as Total FROM vw_emprestimos_atrasados;

PRINT '';
PRINT 'Relatório de Empréstimos por Usuário (vw_relatorio_emprestimos_usuario):';
SELECT * FROM vw_relatorio_emprestimos_usuario 
WHERE Total_Emprestimos > 0 
ORDER BY Emprestimos_Atrasados DESC LIMIT 10;

-- =====================================================
-- 6. RELATÓRIO FINAL
-- =====================================================

PRINT '';
PRINT '╔════════════════════════════════════════════════════╗';
PRINT '║  VALIDAÇÃO CONCLUÍDA - Verificar resultados acima  ║';
PRINT '╚════════════════════════════════════════════════════╝';

-- =====================================================
-- 7. QUERIES ÚTEIS PARA DEBUGGING
-- =====================================================

PRINT '';
PRINT '========== QUERIES ÚTEIS PARA DEBUGGING ==========';

PRINT '';
PRINT '-- Listar últimos 10 empréstimos:';
PRINT 'SELECT ID_Emprestimo, ID_Usuario, ID_Objeto, Status_Emprestimo, Data_Retirada FROM Emprestimos ORDER BY ID_Emprestimo DESC LIMIT 10;';

PRINT '';
PRINT '-- Contar objetos por status:';
PRINT 'SELECT Status_Item, COUNT(*) FROM Objeto GROUP BY Status_Item;';

PRINT '';
PRINT '-- Contar empréstimos por status:';
PRINT 'SELECT Status_Emprestimo, COUNT(*) FROM Emprestimos GROUP BY Status_Emprestimo;';

PRINT '';
PRINT '-- Ver erro de conexão (execute no host MySQL):';
PRINT 'SHOW ENGINE INNODB STATUS;';
