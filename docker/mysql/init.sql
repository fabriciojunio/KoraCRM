-- Inicialização do banco KoraCRM
CREATE DATABASE IF NOT EXISTS koracrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS koracrm_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON koracrm.* TO 'koracrm_user'@'%';
GRANT ALL PRIVILEGES ON koracrm_test.* TO 'koracrm_user'@'%';
FLUSH PRIVILEGES;
