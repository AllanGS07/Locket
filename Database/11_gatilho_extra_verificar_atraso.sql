USE locket_db;

DROP TRIGGER IF EXISTS trg_verificar_atraso_emprestimo;

DELIMITER //
CREATE TRIGGER trg_verificar_atraso_emprestimo
BEFORE UPDATE ON Emprestimos
FOR EACH ROW
BEGIN
    IF NEW.Data_Devolucao_Prevista < CURDATE() 
       AND NEW.Status_Emprestimo = 'EM_ANDAMENTO'
       AND NEW.Data_Devolucao_Real IS NULL THEN
        SET NEW.Status_Emprestimo = 'ATRASADO';
    END IF;
    
    IF NEW.Data_Devolucao_Real IS NOT NULL 
       AND (OLD.Status_Emprestimo = 'ATRASADO' OR OLD.Status_Emprestimo = 'EM_ANDAMENTO') THEN
        SET NEW.Status_Emprestimo = 'DEVOLVIDO';
    END IF;
END //
DELIMITER ;