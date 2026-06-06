CREATE USER IF NOT EXISTS 'locket_app'@'localhost' IDENTIFIED BY 'senha_app_forte_123!@#';
GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE, TRIGGER ON locket_db.* TO 'locket_app'@'localhost';

CREATE USER IF NOT EXISTS 'locket_readonly'@'localhost' IDENTIFIED BY 'senha_readonly_forte_456!@#';
GRANT SELECT ON locket_db.* TO 'locket_readonly'@'localhost';

CREATE USER IF NOT EXISTS 'locket_admin'@'localhost' IDENTIFIED BY 'senha_admin_forte_789!@#';
GRANT ALL PRIVILEGES ON locket_db.* TO 'locket_admin'@'localhost' WITH GRANT OPTION;

CREATE USER IF NOT EXISTS 'locket_migrate'@'localhost' IDENTIFIED BY 'senha_migrate_forte_012!@#';
GRANT CREATE, ALTER, INDEX, SELECT, INSERT, UPDATE, DELETE ON locket_db.* TO 'locket_migrate'@'localhost';

FLUSH PRIVILEGES;