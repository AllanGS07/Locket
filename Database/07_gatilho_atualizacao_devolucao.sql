USE locket_db;

DROP TRIGGER IF EXISTS trg_liberar_objeto_ao_devolver;

DELIMITER //
CREATE TRIGGER trg_liberar_objeto_ao_devolver
AFTER UPDATE ON Emprestimos
FOR EACH ROW
BEGIN
    IF NEW.Status_Emprestimo = 'DEVOLVIDO' AND OLD.Status_Emprestimo != 'DEVOLVIDO' THEN
        UPDATE Objeto
        SET Status_Item = 'DISPONIVEL'
        WHERE ID_Objeto = NEW.ID_Objeto;
    END IF;
END //
DELIMITER ;