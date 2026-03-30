-- CRIAÇÃO DO BANCO --
DROP DATABASE IF EXISTS locket_db;
CREATE DATABASE locket_db;
USE locket_db;

-- CRIAÇÃO DAS TABELAS --
CREATE TABLE Instituicao_Ensino (
    ID_Instituicao INT AUTO_INCREMENT PRIMARY KEY,
    CNPJ VARCHAR(18) UNIQUE NOT NULL,
    Nome VARCHAR(100) NOT NULL,
    e_MEC_INEP VARCHAR(20) UNIQUE NOT NULL
);

CREATE TABLE Usuario (
    ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
    CPF VARCHAR(14) UNIQUE NOT NULL,
    Matricula VARCHAR(20) UNIQUE NOT NULL,
    Nome VARCHAR(100) NOT NULL,
    Data_Nascimento DATE NOT NULL,
    Funcao ENUM(
        'ALUNO',
        'PROFESSOR',
        'TECNICO_ADMINISTRATIVO',
        'TERCEIRIZADO'
    ) NOT NULL,
    ID_Instituicao INT NOT NULL,
    FOREIGN KEY (ID_Instituicao) REFERENCES Instituicao_Ensino(ID_Instituicao)
);

CREATE TABLE Aluno_Dependente (
    ID_Dependente INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario INT NOT NULL,
    Nome_Responsavel VARCHAR(100) NOT NULL,
    CPF_Responsavel VARCHAR(14) NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario)
);

CREATE TABLE Objeto (
    ID_Objeto INT AUTO_INCREMENT PRIMARY KEY,
    Numero_Tombamento INT UNIQUE NOT NULL,
    Nome VARCHAR(50) NOT NULL,
    Marca VARCHAR(50) NOT NULL,
    Modelo VARCHAR(50) NOT NULL,
    Numero_Serie VARCHAR(50) UNIQUE NOT NULL,
    Status_Item ENUM(
        'DISPONIVEL',
        'EMPRESTADO',
        'EM_MANUTENCAO',
        'DANIFICADO',
        'DESAPARECIDO',
        'INUTILIZAVEL'
    ) NOT NULL DEFAULT 'DISPONIVEL', 
    ID_Instituicao INT NOT NULL,
    FOREIGN KEY (ID_Instituicao) REFERENCES Instituicao_Ensino(ID_Instituicao)
);

CREATE TABLE Emprestimos (
    ID_Emprestimo INT AUTO_INCREMENT PRIMARY KEY,
    ID_Usuario INT NOT NULL,
    ID_Objeto INT NOT NULL,
    Data_Retirada DATE NOT NULL,
    Data_Devolucao_Prevista DATE NOT NULL,
    Data_Devolucao_Real DATE, 
    Status_Emprestimo ENUM(
        'PENDENTE',
        'EM_ANDAMENTO',
        'DEVOLVIDO',
        'ATRASADO',
        'PERDIDO',
        'CANCELADO'
    ) NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario),
    FOREIGN KEY (ID_Objeto) REFERENCES Objeto(ID_Objeto)
);

-- VISÕES --
CREATE VIEW detalhes_emprestimos AS
SELECT
	U.Nome AS Aluno,
    O.Nome AS Item,
    E.Data_Retirada,
    E.Status_Emprestimo
FROM Emprestimos E
JOIN Usuario U ON E.ID_Usuario = U.ID_Usuario
JOIN Objeto O ON E.ID_Objeto = O.ID_Objeto;

CREATE VIEW lista_simples_objetos AS
SELECT Nome, Marca, Modelo, Status_Item FROM Objeto;

-- FUNÇÕES --


DELIMITER //
DROP FUNCTION IF EXISTS CalcularAtrasoUsuario//

CREATE FUNCTION CalcularAtrasoUsuario (p_id_usuario INT)
RETURNS INTEGER
DETERMINISTIC
BEGIN
    DECLARE v_finished INTEGER DEFAULT 0;
    DECLARE v_total_dias_atraso INTEGER DEFAULT 0;
    DECLARE v_data_prevista DATE;
    
    DECLARE v_dias_aux INTEGER DEFAULT 0;

    DECLARE cur_prazos CURSOR FOR 
        SELECT Data_Devolucao_Prevista 
        FROM Emprestimos 
        WHERE ID_Usuario = p_id_usuario 
        AND Status_Emprestimo = 'ATRASADO';

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_finished = 1;

    OPEN cur_prazos;

    get_datas: LOOP
        FETCH cur_prazos INTO v_data_prevista;
        
        IF v_finished = 1 THEN 
            LEAVE get_datas;
        END IF;

        IF CURDATE() > v_data_prevista THEN 
            SET v_dias_aux = DATEDIFF(CURDATE(), v_data_prevista);
            SET v_total_dias_atraso = v_total_dias_atraso + v_dias_aux;
        END IF;
    END LOOP get_datas;

    CLOSE cur_prazos;

    RETURN v_total_dias_atraso;
END//

DELIMITER ;

-- GATILHOS --