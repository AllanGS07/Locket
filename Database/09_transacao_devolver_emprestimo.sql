USE locket_db;

DROP PROCEDURE IF EXISTS proc_devolver_emprestimo;

DELIMITER //
CREATE PROCEDURE proc_devolver_emprestimo(
    IN p_ID_Emprestimo INT,
    IN p_Data_Devolucao_Real DATE,
    OUT p_Sucesso BOOLEAN,
    OUT p_Mensagem VARCHAR(255)
)
BEGIN
    DECLARE v_Status_Atual VARCHAR(20);
    DECLARE v_Dias_Atraso INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_Sucesso = FALSE;
        SET p_Mensagem = 'ERRO: Falha ao processar devolução. Transação cancelada.';
    END;
    
    START TRANSACTION;
    
    SELECT Status_Emprestimo, DATEDIFF(p_Data_Devolucao_Real, Data_Devolucao_Prevista) 
    INTO v_Status_Atual, v_Dias_Atraso
    FROM Emprestimos WHERE ID_Emprestimo = p_ID_Emprestimo;
    
    IF v_Status_Atual NOT IN ('EM_ANDAMENTO', 'ATRASADO') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Status inválido para devolução';
    END IF;
    
    UPDATE Emprestimos
    SET Data_Devolucao_Real = p_Data_Devolucao_Real, Status_Emprestimo = 'DEVOLVIDO'
    WHERE ID_Emprestimo = p_ID_Emprestimo;
    
    COMMIT;
    SET p_Sucesso = TRUE;
    IF v_Dias_Atraso > 0 THEN
        SET p_Mensagem = CONCAT('Empréstimo devolvido com ', v_Dias_Atraso, ' dia(s) de atraso');
    ELSE
        SET p_Mensagem = 'Empréstimo devolvido no prazo';
    END IF;
END //
DELIMITER ;