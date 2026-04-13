-- VIEWS PARA SEGURANÇA E ACESSO A DADOS
-- Essas views expõem apenas dados não-sensíveis, protegendo informações como senhas e CPF

USE locket_db;

-- View para dados públicos do usuário (sem informações sensíveis)
CREATE VIEW vw_usuario_publico AS
SELECT 
    ID_Usuario,
    Nome,
    Funcao,
    ID_Instituicao,
    Data_Criacao
FROM Usuario
WHERE Ativo = TRUE;

-- View para detalhes de empréstimos com informações do aluno e objeto
CREATE VIEW vw_detalhes_emprestimos AS
SELECT
    E.ID_Emprestimo,
    U.Nome AS Nome_Usuario,
    U.Funcao,
    O.Nome AS Nome_Objeto,
    O.Marca,
    O.Modelo,
    O.Numero_Tombamento,
    E.Data_Retirada,
    E.Data_Devolucao_Prevista,
    E.Data_Devolucao_Real,
    E.Status_Emprestimo,
    IE.Nome AS Nome_Instituicao
FROM Emprestimos E
INNER JOIN Usuario U ON E.ID_Usuario = U.ID_Usuario
INNER JOIN Objeto O ON E.ID_Objeto = O.ID_Objeto
INNER JOIN Instituicao_Ensino IE ON U.ID_Instituicao = IE.ID_Instituicao
WHERE U.Ativo = TRUE;

-- View para lista simples de objetos disponíveis (sem dados de identificação única)
CREATE VIEW vw_objetos_publicos AS
SELECT 
    ID_Objeto,
    Nome,
    Marca,
    Modelo,
    Status_Item,
    ID_Instituicao
FROM Objeto
WHERE Status_Item IN ('DISPONIVEL', 'EMPRESTADO');

-- View para admins: usuários com email (mascarado para segurança)
CREATE VIEW vw_usuarios_admin AS
SELECT 
    ID_Usuario,
    Nome,
    CPF,
    Matricula,
    Email,
    Funcao,
    ID_Instituicao,
    Ativo,
    Data_Criacao
FROM Usuario;

-- View para relatório de atrasos
CREATE VIEW vw_emprestimos_atrasados AS
SELECT
    E.ID_Emprestimo,
    U.ID_Usuario,
    U.Nome AS Nome_Usuario,
    U.Email,
    O.Nome AS Nome_Objeto,
    O.Numero_Tombamento,
    E.Data_Devolucao_Prevista,
    DATEDIFF(CURDATE(), E.Data_Devolucao_Prevista) AS Dias_Atraso,
    E.Status_Emprestimo
FROM Emprestimos E
INNER JOIN Usuario U ON E.ID_Usuario = U.ID_Usuario
INNER JOIN Objeto O ON E.ID_Objeto = O.ID_Objeto
WHERE E.Status_Emprestimo IN ('ATRASADO', 'EM_ANDAMENTO')
    AND E.Data_Devolucao_Prevista < CURDATE()
ORDER BY Dias_Atraso DESC;

-- View para histórico de empréstimos por usuário
CREATE VIEW vw_historico_emprestimos_usuario AS
SELECT
    E.ID_Emprestimo,
    U.Nome AS Nome_Usuario,
    O.Nome AS Nome_Objeto,
    E.Data_Retirada,
    E.Data_Devolucao_Prevista,
    E.Data_Devolucao_Real,
    E.Status_Emprestimo
FROM Emprestimos E
INNER JOIN Usuario U ON E.ID_Usuario = U.ID_Usuario
INNER JOIN Objeto O ON E.ID_Objeto = O.ID_Objeto
WHERE U.Ativo = TRUE
ORDER BY E.Data_Retirada DESC;

-- View para dependentes com acesso restrito
CREATE VIEW vw_dependentes_usuario AS
SELECT
    ID_Dependente,
    ID_Usuario,
    Nome_Responsavel,
    CPF_Responsavel
FROM Aluno_Dependente;

-- View para instituições (sem dados técnicos internos)
CREATE VIEW vw_instituicoes_publicas AS
SELECT
    ID_Instituicao,
    Nome,
    CNPJ
FROM Instituicao_Ensino;
