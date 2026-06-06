USE locket_db;

CREATE OR REPLACE VIEW vw_usuario_publico AS
SELECT 
    ID_Usuario,
    Nome,
    Funcao,
    ID_Instituicao,
    Data_Criacao
FROM Usuario
WHERE Ativo = TRUE;