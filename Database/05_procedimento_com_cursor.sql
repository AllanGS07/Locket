USE locket_db;

DROP PROCEDURE IF EXISTS proc_relatorio_atraso_instituicao;

DELIMITER //

CREATE PROCEDURE proc_relatorio_atraso_instituicao(
    IN p_ID_Instituicao INT,
    OUT p_Total_Dias_Atraso_Inst INT
)
BEGIN
    DECLARE v_finalizado INT DEFAULT FALSE;
    DECLARE v_id_usuario_cursor INT;
    DECLARE v_dias_atraso_usuario INT;
    
    DECLARE cursor_usuarios CURSOR FOR 
        SELECT ID_Usuario FROM Usuario 
        WHERE ID_Instituicao = p_ID_Instituicao AND Ativo = TRUE;
        
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finalizado = TRUE;
    
    SET p_Total_Dias_Atraso_Inst = 0;
    
    OPEN cursor_usuarios;
    
    busca_loop: LOOP
        FETCH cursor_usuarios INTO v_id_usuario_cursor;
        
        IF v_finalizado THEN
            LEAVE busca_loop;
        END IF;
        
        SELECT COALESCE(SUM(DATEDIFF(CURDATE(), Data_Devolucao_Prevista)), 0)
        INTO v_dias_atraso_usuario
        FROM Emprestimos
        WHERE ID_Usuario = v_id_usuario_cursor
            AND Status_Emprestimo = 'ATRASADO'
            AND Data_Devolucao_Prevista < CURDATE();
            
        SET p_Total_Dias_Atraso_Inst = p_Total_Dias_Atraso_Inst + v_dias_atraso_usuario;
    END LOOP;
    
    CLOSE cursor_usuarios;
END //

DELIMITER ;