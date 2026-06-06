DROP DATABASE IF EXISTS locket_db;
CREATE DATABASE locket_db;
USE locket_db;

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
    Email VARCHAR(100) UNIQUE NOT NULL,
    Senha_Hash VARCHAR(255) NOT NULL,
    Funcao ENUM('ALUNO', 'PROFESSOR', 'TECNICO_ADMINISTRATIVO', 'TERCEIRIZADO') NOT NULL,
    ID_Instituicao INT NOT NULL,
    Data_Criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Ativo BOOLEAN DEFAULT TRUE,
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
    Status_Item ENUM('DISPONIVEL', 'EMPRESTADO', 'EM_MANUTENCAO', 'DANIFICADO', 'DESAPARECIDO', 'INUTILIZAVEL') NOT NULL DEFAULT 'DISPONIVEL', 
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
    Status_Emprestimo ENUM('PENDENTE', 'EM_ANDAMENTO', 'DEVOLVIDO', 'ATRASADO', 'PERDIDO', 'CANCELADO') NOT NULL,
    FOREIGN KEY (ID_Usuario) REFERENCES Usuario(ID_Usuario),
    FOREIGN KEY (ID_Objeto) REFERENCES Objeto(ID_Objeto),
    CONSTRAINT chk_datas_emprestimo CHECK (Data_Devolucao_Prevista >= Data_Retirada)
);

CREATE INDEX idx_status_emprestimo ON Emprestimos (Status_Emprestimo);
CREATE INDEX idx_usuario_ativo ON Usuario (Ativo);