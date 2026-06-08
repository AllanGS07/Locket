📋 AUDITORIA DE INCONSISTÊNCIAS - BANCO DE DADOS LOCKET

═══════════════════════════════════════════════════════════════════════════════

🔴 PROBLEMAS ENCONTRADOS:

1. ARQUIVO: 13_seed_testes.sql (CRIADO POR MIM - INCORRETO)
   ├─ ❌ INSERT INTO Instituicao (NÃO EXISTE!)
   │  └─ Deveria ser: Instituicao_Ensino
   │
   ├─ ❌ Colunas inexistentes em Instituicao_Ensino:
   │  ├─ Email (não existe)
   │  ├─ Telefone (não existe)
   │  ├─ Endereco (não existe)
   │  ├─ Cidade (não existe)
   │  ├─ Estado (não existe)
   │  └─ CEP (não existe)
   │
   ├─ ❌ Colunas obrigatórias FALTANDO:
   │  └─ e_MEC_INEP (obrigatória, unique)
   │
   ├─ ❌ Valores incorretos para Funcao:
   │  ├─ 'Admin' → Deveria ser: 'PROFESSOR'
   │  ├─ 'Aluno' → Deveria ser: 'ALUNO'
   │  └─ 'Técnico' → Deveria ser: 'TECNICO_ADMINISTRATIVO'
   │
   └─ ❌ Tabela referenciada como 'Emprestimo' em outro lugar
      └─ Deveria ser: Emprestimos (com S!)

═══════════════════════════════════════════════════════════════════════════════

📊 VERDADEIRA ESTRUTURA DO BANCO (01_criacao_tabelas.sql):

┌─ INSTITUICAO_ENSINO
│  ├─ ID_Instituicao (INT, PRIMARY KEY, AUTO_INCREMENT)
│  ├─ CNPJ (VARCHAR 18, UNIQUE)
│  ├─ Nome (VARCHAR 100)
│  └─ e_MEC_INEP (VARCHAR 20, UNIQUE) ← OBRIGATÓRIO!
│
├─ USUARIO
│  ├─ ID_Usuario (INT, PRIMARY KEY, AUTO_INCREMENT)
│  ├─ CPF (VARCHAR 14, UNIQUE)
│  ├─ Matricula (VARCHAR 20, UNIQUE)
│  ├─ Nome (VARCHAR 100)
│  ├─ Data_Nascimento (DATE)
│  ├─ Email (VARCHAR 100, UNIQUE)
│  ├─ Senha_Hash (VARCHAR 255)
│  ├─ Funcao (ENUM: 'ALUNO', 'PROFESSOR', 'TECNICO_ADMINISTRATIVO', 'TERCEIRIZADO')
│  ├─ ID_Instituicao (INT, FK → Instituicao_Ensino)
│  ├─ Data_Criacao (TIMESTAMP)
│  └─ Ativo (BOOLEAN)
│
├─ ALUNO_DEPENDENTE
│  ├─ ID_Dependente (INT, PRIMARY KEY)
│  ├─ ID_Usuario (INT, FK)
│  ├─ Nome_Responsavel (VARCHAR 100)
│  └─ CPF_Responsavel (VARCHAR 14)
│
├─ OBJETO
│  ├─ ID_Objeto (INT, PRIMARY KEY, AUTO_INCREMENT)
│  ├─ Numero_Tombamento (INT, UNIQUE)
│  ├─ Nome (VARCHAR 50)
│  ├─ Marca (VARCHAR 50)
│  ├─ Modelo (VARCHAR 50)
│  ├─ Numero_Serie (VARCHAR 50, UNIQUE)
│  ├─ Status_Item (ENUM: 'DISPONIVEL', 'EMPRESTADO', 'EM_MANUTENCAO', 'DANIFICADO', 'DESAPARECIDO', 'INUTILIZAVEL')
│  └─ ID_Instituicao (INT, FK)
│
└─ EMPRESTIMOS ← ⭐ COM "S" NO FINAL!
   ├─ ID_Emprestimo (INT, PRIMARY KEY)
   ├─ ID_Usuario (INT, FK)
   ├─ ID_Objeto (INT, FK)
   ├─ Data_Retirada (DATE)
   ├─ Data_Devolucao_Prevista (DATE)
   ├─ Data_Devolucao_Real (DATE, NULL se não devolvido)
   └─ Status_Emprestimo (ENUM: 'PENDENTE', 'EM_ANDAMENTO', 'DEVOLVIDO', 'ATRASADO', 'PERDIDO', 'CANCELADO')

═══════════════════════════════════════════════════════════════════════════════

🐍 PYTHON - INCONSISTÊNCIAS:

Arquivo: analisador_emprestimos.py

Usa nomes em MINÚSCULAS:
   ├─ FROM emprestimo e        → Deveria ser: Emprestimos
   ├─ FROM usuario u           → Deveria ser: Usuario
   ├─ FROM objeto o            → Deveria ser: Objeto
   └─ Colunas em snake_case: id_emprestimo, id_usuario, etc
      → Deveria ser: ID_Emprestimo, ID_Usuario (CamelCase)

ℹ️ NOTA: No Windows, MySQL NÃO é case-sensitive para nomes de tabelas por padrão,
então o Python funciona. Mas no Linux seria ERRO! Para ser portável, deveria
usar o mesmo case do banco.

═══════════════════════════════════════════════════════════════════════════════

✅ AÇÕES NECESSÁRIAS:

1. Corrigir 13_seed_testes.sql:
   ├─ Mudar Instituicao → Instituicao_Ensino
   ├─ Remover colunas inexistentes (Email, Telefone, etc)
   ├─ Adicionar e_MEC_INEP obrigatório
   ├─ Atualizar valores de Funcao (ALUNO, PROFESSOR, etc)
   ├─ Remover colunas desnecessárias em Objeto
   └─ Referências corretas de Emprestimos

2. ✅ OPCIONAL: Corrigir queries Python (portabilidade)
   ├─ emprestimo → Emprestimos
   ├─ usuario → Usuario
   ├─ objeto → Objeto
   └─ Colunas em CamelCase correto

3. ✅ Verificar PHP (SetupController.php - ESTÁ CORRETO!)

═══════════════════════════════════════════════════════════════════════════════
