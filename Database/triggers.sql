-- ========================================
-- TRIGGERS - GATILHOS DO SISTEMA
-- ========================================

USE locket_db;

-- ========================================
-- TRIGGER 1: Atualizar Status do Objeto ao INSERIR Empréstimo
-- ========================================
-- Quando um novo empréstimo é inserido, atualiza o status do objeto para EMPRESTADO
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

-- ========================================
-- TRIGGER 2: Verificar Atraso ao ATUALIZAR Empréstimo
-- ========================================
-- Quando um empréstimo é atualizado, verifica se está atrasado
-- Se Data_Devolucao_Prevista < CURDATE() e Status é EM_ANDAMENTO, marca como ATRASADO
DROP TRIGGER IF EXISTS trg_verificar_atraso_emprestimo;

DELIMITER //

CREATE TRIGGER trg_verificar_atraso_emprestimo
BEFORE UPDATE ON Emprestimos
FOR EACH ROW
BEGIN
    -- Se a devolução prevista é anterior a hoje e o empréstimo ainda está em andamento
    IF NEW.Data_Devolucao_Prevista < CURDATE() 
       AND NEW.Status_Emprestimo = 'EM_ANDAMENTO'
       AND NEW.Data_Devolucao_Real IS NULL THEN
        SET NEW.Status_Emprestimo = 'ATRASADO';
    END IF;
    
    -- Se foi devolvido e o status era ATRASADO, permite marcar como DEVOLVIDO
    IF NEW.Data_Devolucao_Real IS NOT NULL 
       AND (OLD.Status_Emprestimo = 'ATRASADO' OR OLD.Status_Emprestimo = 'EM_ANDAMENTO') THEN
        SET NEW.Status_Emprestimo = 'DEVOLVIDO';
    END IF;
END //

DELIMITER ;

-- ========================================
-- TRIGGER 3: Atualizar Status do Objeto ao DEVOLVER (INSERT em evento de devolução)
-- ========================================
-- Quando um empréstimo é marcado como DEVOLVIDO, libera o objeto
DROP TRIGGER IF EXISTS trg_liberar_objeto_ao_devolver;

DELIMITER //

CREATE TRIGGER trg_liberar_objeto_ao_devolver
AFTER UPDATE ON Emprestimos
FOR EACH ROW
BEGIN
    -- Se o empréstimo foi marcado como DEVOLVIDO, libera o objeto
    IF NEW.Status_Emprestimo = 'DEVOLVIDO' 
       AND OLD.Status_Emprestimo != 'DEVOLVIDO' THEN
        UPDATE Objeto
        SET Status_Item = 'DISPONIVEL'
        WHERE ID_Objeto = NEW.ID_Objeto;
    END IF;
END //

DELIMITER ;

-- ========================================
-- EVENT: Atualizar Empréstimos Atrasados Automaticamente (24h)
-- ========================================
-- Este evento verifica diariamente se há empréstimos que ficaram atrasados
-- Precisa que o MySQL tenha event_scheduler ativado: SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS evt_atualizar_emprestimos_atrasados;

DELIMITER //

CREATE EVENT evt_atualizar_emprestimos_atrasados
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    UPDATE Emprestimos
    SET Status_Emprestimo = 'ATRASADO'
    WHERE Status_Emprestimo = 'EM_ANDAMENTO'
      AND Data_Devolucao_Prevista < CURDATE()
      AND Data_Devolucao_Real IS NULL;
END //

DELIMITER ;

-- ========================================
-- ATIVAR EVENT SCHEDULER
-- ========================================
-- Execute este comando para ativar o scheduler (pode ser feito no startup do servidor)
-- SET GLOBAL event_scheduler = ON;
