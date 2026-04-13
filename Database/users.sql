-- CRIAÇÃO DE USUÁRIOS E PERMISSÕES NO MYSQL
-- Remover privilégios do root e criar usuários específicos com permissões limitadas

-- Usuário para a aplicação (acesso geral)
CREATE USER IF NOT EXISTS 'locket_app'@'localhost' IDENTIFIED BY 'senha_app_forte_123!@#';
GRANT SELECT, INSERT, UPDATE, DELETE ON locket_db.* TO 'locket_app'@'localhost';

-- Usuário para leitura apenas (relatórios / consultas)
CREATE USER IF NOT EXISTS 'locket_readonly'@'localhost' IDENTIFIED BY 'senha_readonly_forte_456!@#';
GRANT SELECT ON locket_db.* TO 'locket_readonly'@'localhost';

-- Usuário para administração do banco (backups, manutenção)
CREATE USER IF NOT EXISTS 'locket_admin'@'localhost' IDENTIFIED BY 'senha_admin_forte_789!@#';
GRANT ALL PRIVILEGES ON locket_db.* TO 'locket_admin'@'localhost' WITH GRANT OPTION;

-- Usuário para migrações e scripts (sem permissão de DROP)
CREATE USER IF NOT EXISTS 'locket_migrate'@'localhost' IDENTIFIED BY 'senha_migrate_forte_012!@#';
GRANT CREATE, ALTER, INDEX, SELECT, INSERT, UPDATE, DELETE ON locket_db.* TO 'locket_migrate'@'localhost';

-- Aplicar as mudanças
FLUSH PRIVILEGES;

-- RESTRIÇÕES ADICIONAIS DE SEGURANÇA
-- Remover privilégios potencialmente perigosos do root (comentado, descomenta conforme necessário)
-- REVOKE FILE ON *.* FROM 'root'@'localhost';
-- REVOKE PROCESS ON *.* FROM 'root'@'localhost';
-- REVOKE RELOAD ON *.* FROM 'root'@'localhost';
-- REVOKE REPLICATION SLAVE ON *.* FROM 'root'@'localhost';
-- FLUSH PRIVILEGES;
