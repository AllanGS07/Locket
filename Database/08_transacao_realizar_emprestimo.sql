USE locket_db;

DROP PROCEDURE IF EXISTS proc_realizar_emprestimo;

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
    
    IF NOT EXISTS (SELECT 1 FROM Objeto WHERE ID_Objeto = p_ID_Objeto AND Status_Item = 'DISPONIVEL') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Objeto indisponível';
    END IF;
    
    INSERT INTO Emprestimos (ID_Usuario, ID_Objeto, Data_Retirada, Data_Devolucao_Prevista, Status_Emprestimo) 
    VALUES (p_ID_Usuario, p_ID_Objeto, p_Data_Retirada, p_Data_Devolucao_Prevista, 'EM_ANDAMENTO');
    
    SET p_ID_Emprestimo = LAST_INSERT_ID();
    COMMIT;
    SET p_Mensagem = 'Empréstimo realizado com sucesso';
END //
DELIMITER ;