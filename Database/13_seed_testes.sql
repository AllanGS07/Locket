-- =====================================================
-- Seed de dados de teste para o sistema Locket
-- Arquivo: 13_seed_testes.sql
-- Propósito: Popular o banco com dados de teste corretos
-- 
-- ESTRUTURA VERIFICADA COM:
-- ✅ 01_criacao_tabelas.sql
-- ✅ control/SetupController.php
-- ✅ Confirmado: Tabelas e colunas reais do banco
-- 
-- Executar com: mysql -u locket_app -p < Database/13_seed_testes.sql
-- =====================================================

USE locket_db;

-- =====================================================
-- 1. INSERIR INSTITUIÇÃO DE TESTE
-- =====================================================
-- Colunas corretas: ID_Instituicao, CNPJ, Nome, e_MEC_INEP
-- e_MEC_INEP é obrigatório e UNIQUE
INSERT IGNORE INTO Instituicao_Ensino (
    CNPJ,
    Nome,
    e_MEC_INEP
) VALUES (
    '12.345.678/0001-90',
    'Universidade Federal de Testes',
    'MEC2026TEST001'
);

-- =====================================================
-- 2. INSERIR USUÁRIOS DE TESTE
-- =====================================================
-- Funcao ENUM permite apenas: 'ALUNO', 'PROFESSOR', 'TECNICO_ADMINISTRATIVO', 'TERCEIRIZADO'
-- Hash bcrypt para "secret": $2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm
INSERT IGNORE INTO Usuario (
    CPF,
    Matricula,
    Nome,
    Data_Nascimento,
    Email,
    Senha_Hash,
    Funcao,
    ID_Instituicao,
    Ativo
) VALUES 
-- Usuário 1: Professor (Admin)
(
    '123.456.789-01',
    'PROF001',
    'Prof. Anderson Silva',
    '1985-03-15',
    'professor@universidade-testes.edu.br',
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
    'PROFESSOR',
    (SELECT ID_Instituicao FROM Instituicao_Ensino WHERE CNPJ = '12.345.678/0001-90' LIMIT 1),
    1
),
-- Usuário 2: Aluno
(
    '987.654.321-01',
    'ALN001',
    'João Pedro Santos',
    '2002-07-22',
    'joao@universidade-testes.edu.br',
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
    'ALUNO',
    (SELECT ID_Instituicao FROM Instituicao_Ensino WHERE CNPJ = '12.345.678/0001-90' LIMIT 1),
    1
),
-- Usuário 3: Técnico Administrativo
(
    '555.444.333-22',
    'TEC001',
    'Maria Oliveira Costa',
    '1990-11-08',
    'maria.operacional@universidade-testes.edu.br',
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm',
    'TECNICO_ADMINISTRATIVO',
    (SELECT ID_Instituicao FROM Instituicao_Ensino WHERE CNPJ = '12.345.678/0001-90' LIMIT 1),
    1
);

-- =====================================================
-- 3. INSERIR OBJETOS (ATIVOS) DE TESTE
-- =====================================================
-- Colunas corretas: ID_Objeto, Numero_Tombamento, Nome, Marca, Modelo, Numero_Serie, Status_Item, ID_Instituicao
INSERT IGNORE INTO Objeto (
    Numero_Tombamento,
    Nome,
    Marca,
    Modelo,
    Numero_Serie,
    Status_Item,
    ID_Instituicao
) VALUES
-- Ativo 1: Notebook
(
    1001,
    'Notebook Dell Inspiron',
    'Dell',
    'Inspiron 15',
    'DELL-NOTB-001-2026',
    'DISPONIVEL',
    (SELECT ID_Instituicao FROM Instituicao_Ensino WHERE CNPJ = '12.345.678/0001-90' LIMIT 1)
),
-- Ativo 2: Projetor
(
    1002,
    'Projetor BenQ',
    'BenQ',
    'MX535A',
    'BENQ-PROJ-002-2026',
    'DISPONIVEL',
    (SELECT ID_Instituicao FROM Instituicao_Ensino WHERE CNPJ = '12.345.678/0001-90' LIMIT 1)
),
-- Ativo 3: Câmera Fotográfica
(
    1003,
    'Câmera Canon EOS',
    'Canon',
    'EOS 250D',
    'CANON-CAM-003-2026',
    'DISPONIVEL',
    (SELECT ID_Instituicao FROM Instituicao_Ensino WHERE CNPJ = '12.345.678/0001-90' LIMIT 1)
);

-- =====================================================
-- DADOS DE TESTE INSERIDOS COM SUCESSO!
-- =====================================================
-- Credenciais de teste:
-- Email: professor@universidade-testes.edu.br
-- Senha: secret
-- Função: PROFESSOR
-- 
-- Email: joao@universidade-testes.edu.br
-- Senha: secret
-- Função: ALUNO
-- 
-- Email: maria.operacional@universidade-testes.edu.br
-- Senha: secret
-- Função: TECNICO_ADMINISTRATIVO
-- =====================================================

-- =====================================================
-- 4. INSERIR ALUNOS DEPENDENTES (se aplicável)
-- =====================================================
INSERT IGNORE INTO Aluno_Dependente (
    ID_Usuario,
    Nome_Responsavel,
    CPF_Responsavel
) VALUES 
-- Dependente do aluno João
(
    (SELECT ID_Usuario FROM Usuario WHERE CPF = '987.654.321-01' LIMIT 1),
    'Maria Silva Santos',
    '111.222.333-44'
),
-- Outro dependente do aluno João
(
    (SELECT ID_Usuario FROM Usuario WHERE CPF = '987.654.321-01' LIMIT 1),
    'Pedro Silva Santos',
    '222.333.444-55'
);

-- =====================================================
-- DADOS DE TESTE INSERIDOS COM SUCESSO!
-- =====================================================
