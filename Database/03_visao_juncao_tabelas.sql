USE locket_db;

CREATE OR REPLACE VIEW vw_detalhes_emprestimos AS
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