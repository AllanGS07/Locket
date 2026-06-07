USE locket_db;

SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS evt_atualizar_emprestimos_atrasados;

DELIMITER //
CREATE EVENT evt_atualizar_emprestimos_atrasados
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO
BEGIN
    UPDATE Emprestimos
    SET Status_Emprestimo = 'ATRASADO'
    WHERE Status_Emprestimo = 'EM_ANDAMENTO'
      AND Data_Devolucao_Prevista < CURDATE()
      AND Data_Devolucao_Real IS NULL;
END //
DELIMITER ;