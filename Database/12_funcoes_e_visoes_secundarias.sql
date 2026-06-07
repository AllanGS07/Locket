USE locket_db;

-- 1. Visões Complementares
CREATE OR REPLACE VIEW vw_objetos_publicos AS
SELECT ID_Objeto, Nome, Marca, Modelo, Status_Item, ID_Instituicao
FROM Objeto WHERE Status_Item IN ('DISPONIVEL', 'EMPRESTADO');

CREATE OR REPLACE VIEW vw_emprestimos_atrasados AS
SELECT E.ID_Emprestimo, U.Nome AS Nome_Usuario, O.Nome AS Nome_Objeto, 
       E.Data_Devolucao_Prevista, DATEDIFF(CURDATE(), E.Data_Devolucao_Prevista) AS Dias_Atraso
FROM Emprestimos E
JOIN Usuario U ON E.ID_Usuario = U.ID_Usuario
JOIN Objeto O ON E.ID_Objeto = O.ID_Objeto
WHERE E.Status_Emprestimo = 'ATRASADO';

-- 2. Funções Complementares
DELIMITER //

DROP FUNCTION IF EXISTS UsuarioTemEmprestimosAtivos//
CREATE FUNCTION UsuarioTemEmprestimosAtivos(p_id_usuario INT) RETURNS BOOLEAN DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_count INT;
    SELECT COUNT(*) INTO v_count FROM Emprestimos
    WHERE ID_Usuario = p_id_usuario AND Status_Emprestimo IN ('PENDENTE', 'EM_ANDAMENTO') AND Data_Devolucao_Real IS NULL;
    RETURN v_count > 0;
END//

DROP FUNCTION IF EXISTS QtdObjetosDisponiveis//
CREATE FUNCTION QtdObjetosDisponiveis(p_id_instituicao INT) RETURNS INTEGER DETERMINISTIC READS SQL DATA
BEGIN
    DECLARE v_quantidade INT;
    SELECT COUNT(*) INTO v_quantidade FROM Objeto
    WHERE ID_Instituicao = p_id_instituicao AND Status_Item = 'DISPONIVEL';
    RETURN v_quantidade;
END//

DELIMITER ;