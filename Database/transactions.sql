-- ========================================
-- TRANSAÇÕES - OPERAÇÕES TRANSACIONAIS
-- ========================================

USE locket_db;

-- ========================================
-- TRANSACTION 1: Realizar Empréstimo
-- ========================================
-- Operação transacional que:
-- 1. Insere novo registro em Emprestimos
-- 2. Atualiza Status_Item do Objeto para EMPRESTADO
-- 3. Registra na tabela de auditoria (se existir)
-- 
-- Uso: Chamada por stored procedure ou aplicação
DELIMITER //

CREATE PROCEDURE proc_realizar_emprestimo(
    IN p_ID_Usuario INT,
    IN p_ID_Objeto INT,
    IN p_Data_Retirada DATE,
    IN p_Data_Devolucao_Prevista DATE,
    OUT p_ID_Emprestimo INT,
    OUT p_Mensagem VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_Mensagem = 'ERRO: Falha ao realizar empréstimo. Transação cancelada.';
        SET p_ID_Emprestimo = -1;
    END;
    
    START TRANSACTION;
    
    -- Validar se o objeto está disponível
    IF NOT EXISTS (
        SELECT 1 FROM Objeto 
        WHERE ID_Objeto = p_ID_Objeto 
        AND Status_Item = 'DISPONIVEL'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Objeto não está disponível para empréstimo';
    END IF;
    
    -- Validar se o usuário existe
    IF NOT EXISTS (
        SELECT 1 FROM Usuario 
        WHERE ID_Usuario = p_ID_Usuario 
        AND Ativo = TRUE
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Usuário inválido ou inativo';
    END IF;
    
    -- Validar se a data de devolução prevista é posterior à de retirada
    IF p_Data_Devolucao_Prevista <= p_Data_Retirada THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Data de devolução deve ser posterior à data de retirada';
    END IF;
    
    -- Inserir novo empréstimo
    INSERT INTO Emprestimos (
        ID_Usuario,
        ID_Objeto,
        Data_Retirada,
        Data_Devolucao_Prevista,
        Status_Emprestimo
    ) VALUES (
        p_ID_Usuario,
        p_ID_Objeto,
        p_Data_Retirada,
        p_Data_Devolucao_Prevista,
        'EM_ANDAMENTO'
    );
    
    SET p_ID_Emprestimo = LAST_INSERT_ID();
    
    -- O trigger trg_atualizar_status_objeto_ao_emprestar atualiza o status do objeto automaticamente
    
    COMMIT;
    SET p_Mensagem = 'Empréstimo realizado com sucesso';
    
END //

DELIMITER ;

-- ========================================
-- TRANSACTION 2: Processar Devolução de Empréstimo
-- ========================================
-- Operação transacional que:
-- 1. Valida se o empréstimo existe e está em andamento
-- 2. Atualiza o status do empréstimo para DEVOLVIDO
-- 3. Atualiza a data de devolução real
-- 4. Libera o objeto (volta a DISPONIVEL)
-- 5. Registra auditoria (se aplicável)
--
-- Uso: Chamada por stored procedure ou aplicação
DELIMITER //

CREATE PROCEDURE proc_devolver_emprestimo(
    IN p_ID_Emprestimo INT,
    IN p_Data_Devolucao_Real DATE,
    OUT p_Sucesso BOOLEAN,
    OUT p_Mensagem VARCHAR(255)
)
BEGIN
    DECLARE v_Status_Atual VARCHAR(20);
    DECLARE v_ID_Objeto INT;
    DECLARE v_Dias_Atraso INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_Sucesso = FALSE;
        SET p_Mensagem = 'ERRO: Falha ao processar devolução. Transação cancelada.';
    END;
    
    START TRANSACTION;
    
    -- Obter informações do empréstimo
    SELECT 
        Status_Emprestimo, 
        ID_Objeto,
        DATEDIFF(p_Data_Devolucao_Real, Data_Devolucao_Prevista) AS dias_atraso
    INTO v_Status_Atual, v_ID_Objeto, v_Dias_Atraso
    FROM Emprestimos
    WHERE ID_Emprestimo = p_ID_Emprestimo;
    
    -- Validar se o empréstimo existe
    IF v_Status_Atual IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Empréstimo não encontrado';
    END IF;
    
    -- Validar se o empréstimo está em andamento ou atrasado
    IF v_Status_Atual NOT IN ('EM_ANDAMENTO', 'ATRASADO') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Empréstimo não pode ser devolvido neste status';
    END IF;
    
    -- Validar se a data de devolução real é válida
    IF p_Data_Devolucao_Real < CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Data de devolução não pode ser anterior a hoje';
    END IF;
    
    -- Atualizar empréstimo com data de devolução real
    UPDATE Emprestimos
    SET 
        Data_Devolucao_Real = p_Data_Devolucao_Real,
        Status_Emprestimo = 'DEVOLVIDO'
    WHERE ID_Emprestimo = p_ID_Emprestimo;
    
    -- O trigger trg_liberar_objeto_ao_devolver atualiza o status do objeto automaticamente
    
    COMMIT;
    
    SET p_Sucesso = TRUE;
    IF v_Dias_Atraso > 0 THEN
        SET p_Mensagem = CONCAT('Empréstimo devolvido com ', v_Dias_Atraso, ' dia(s) de atraso');
    ELSE
        SET p_Mensagem = 'Empréstimo devolvido no prazo';
    END IF;
    
END //

DELIMITER ;

-- ========================================
-- EXEMPLO DE USO DAS TRANSAÇÕES
-- ========================================
-- 
-- Realizar Empréstimo:
-- CALL proc_realizar_emprestimo(
--     1,              -- ID_Usuario
--     5,              -- ID_Objeto
--     '2025-01-15',   -- Data_Retirada
--     '2025-01-22',   -- Data_Devolucao_Prevista
--     @ID_Emprestimo,
--     @Mensagem
-- );
-- SELECT @ID_Emprestimo AS ID_Emprestimo, @Mensagem AS Mensagem;
--
-- Devolver Empréstimo:
-- CALL proc_devolver_emprestimo(
--     1,              -- ID_Emprestimo
--     '2025-01-25',   -- Data_Devolucao_Real
--     @Sucesso,
--     @Mensagem
-- );
-- SELECT @Sucesso AS Sucesso, @Mensagem AS Mensagem;
--
-- ========================================

-- ========================================
-- VIEWS PARA CONSULTAS TRANSACIONAIS
-- ========================================

-- View para monitorar empréstimos atrasados
CREATE OR REPLACE VIEW vw_emprestimos_atrasados AS
SELECT 
    e.ID_Emprestimo,
    u.Nome AS Nome_Usuario,
    o.Nome AS Nome_Objeto,
    e.Data_Retirada,
    e.Data_Devolucao_Prevista,
    DATEDIFF(CURDATE(), e.Data_Devolucao_Prevista) AS Dias_Atraso,
    e.Status_Emprestimo
FROM Emprestimos e
JOIN Usuario u ON e.ID_Usuario = u.ID_Usuario
JOIN Objeto o ON e.ID_Objeto = o.ID_Objeto
WHERE e.Status_Emprestimo IN ('EM_ANDAMENTO', 'ATRASADO')
  AND e.Data_Devolucao_Prevista < CURDATE()
ORDER BY e.Data_Devolucao_Prevista ASC;

-- View para relatório de empréstimos por usuário
CREATE OR REPLACE VIEW vw_relatorio_emprestimos_usuario AS
SELECT 
    u.ID_Usuario,
    u.Nome,
    COUNT(CASE WHEN e.Status_Emprestimo = 'EM_ANDAMENTO' THEN 1 END) AS Emprestimos_Ativos,
    COUNT(CASE WHEN e.Status_Emprestimo = 'ATRASADO' THEN 1 END) AS Emprestimos_Atrasados,
    COUNT(CASE WHEN e.Status_Emprestimo = 'DEVOLVIDO' THEN 1 END) AS Emprestimos_Devolvidos,
    COUNT(e.ID_Emprestimo) AS Total_Emprestimos
FROM Usuario u
LEFT JOIN Emprestimos e ON u.ID_Usuario = e.ID_Usuario
GROUP BY u.ID_Usuario, u.Nome
ORDER BY Emprestimos_Atrasados DESC;
