USE locket_db;

DROP TRIGGER IF EXISTS trg_atualizar_status_objeto_ao_emprestar;

DELIMITER //
CREATE TRIGGER trg_atualizar_status_objeto_ao_emprestar
AFTER INSERT ON Emprestimos
FOR EACH ROW
BEGIN
    UPDATE Objeto
    SET Status_Item = 'EMPRESTADO'
    WHERE ID_Objeto = NEW.ID_Objeto;
END //
DELIMITER ;