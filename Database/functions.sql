-- FUNÇÕES ÚTEIS PARA O BANCO DE DADOS

USE locket_db;

DELIMITER //

-- Calcula a quantidade de dias em atraso para um usuário específico
DROP FUNCTION IF EXISTS CalcularAtrasoUsuario//
CREATE FUNCTION CalcularAtrasoUsuario(p_id_usuario INT)
RETURNS INTEGER
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_total_dias_atraso INTEGER DEFAULT 0;
    
    SELECT COALESCE(SUM(DATEDIFF(CURDATE(), Data_Devolucao_Prevista)), 0)
    INTO v_total_dias_atraso
    FROM Emprestimos
    WHERE ID_Usuario = p_id_usuario
        AND Status_Emprestimo = 'ATRASADO'
        AND Data_Devolucao_Prevista < CURDATE();
    
    RETURN v_total_dias_atraso;
END//

-- Verifica se um usuário tem empréstimos em andamento
DROP FUNCTION IF EXISTS UsuarioTemEmprestimosAtivos//
CREATE FUNCTION UsuarioTemEmprestimosAtivos(p_id_usuario INT)
RETURNS BOOLEAN
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_count INT;
    
    SELECT COUNT(*) INTO v_count
    FROM Emprestimos
    WHERE ID_Usuario = p_id_usuario
        AND Status_Emprestimo IN ('PENDENTE', 'EM_ANDAMENTO')
        AND Data_Devolucao_Real IS NULL;
    
    RETURN v_count > 0;
END//

-- Retorna a quantidade de objetos disponíveis para empréstimo em uma instituição
DROP FUNCTION IF EXISTS QtdObjetosDisponiveis//
CREATE FUNCTION QtdObjetosDisponiveis(p_id_instituicao INT)
RETURNS INTEGER
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_quantidade INT;
    
    SELECT COUNT(*) INTO v_quantidade
    FROM Objeto
    WHERE ID_Instituicao = p_id_instituicao
        AND Status_Item = 'DISPONIVEL';
    
    RETURN v_quantidade;
END//

-- Calcula dias até devolução prevista (negativo significa em atraso)
DROP FUNCTION IF EXISTS DiasAteDevoluçãoPrevista//
CREATE FUNCTION DiasAteDevoluçãoPrevista(p_id_emprestimo INT)
RETURNS INTEGER
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_dias INT;
    
    SELECT DATEDIFF(Data_Devolucao_Prevista, CURDATE())
    INTO v_dias
    FROM Emprestimos
    WHERE ID_Emprestimo = p_id_emprestimo;
    
    RETURN COALESCE(v_dias, 0);
END//

-- Verifica se um objeto está em manutenção ou indisponível
DROP FUNCTION IF EXISTS ObjetoEstaDisponivel//
CREATE FUNCTION ObjetoEstaDisponivel(p_id_objeto INT)
RETURNS BOOLEAN
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_status VARCHAR(20);
    
    SELECT Status_Item INTO v_status
    FROM Objeto
    WHERE ID_Objeto = p_id_objeto;
    
    RETURN v_status = 'DISPONIVEL';
END//

-- Retorna o status de um usuário (ativo/inativo)
DROP FUNCTION IF EXISTS UsuarioEstaAtivo//
CREATE FUNCTION UsuarioEstaAtivo(p_id_usuario INT)
RETURNS BOOLEAN
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_ativo BOOLEAN;
    
    SELECT Ativo INTO v_ativo
    FROM Usuario
    WHERE ID_Usuario = p_id_usuario;
    
    RETURN COALESCE(v_ativo, FALSE);
END//

DELIMITER ;
