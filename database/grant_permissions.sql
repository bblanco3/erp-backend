-- First create the user if it doesn't exist
CREATE USER IF NOT EXISTS 'laravel'@'%' IDENTIFIED BY 'Syn-r-g5!5laravel';

-- Grant all privileges on master database
GRANT ALL PRIVILEGES ON `master`.* TO 'laravel'@'%';

-- Grant all privileges on existing tenant databases (using pattern tenant_db)
GRANT ALL PRIVILEGES ON `tenant_db`.* TO 'laravel'@'%';

-- Grant ability to create new databases
GRANT CREATE ON *.* TO 'laravel'@'%';

-- Allow the user to create and grant permissions in tenant databases
GRANT GRANT OPTION ON `tenant_db`.* TO 'laravel'@'%';

-- Make sure the user can create databases with the tenant prefix
GRANT CREATE ON `tenant_db`.* TO 'laravel'@'%';

-- Make privileges permanent
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, RELOAD, PROCESS, REFERENCES, INDEX, ALTER, SHOW DATABASES, CREATE TEMPORARY TABLES, LOCK TABLES, EXECUTE, REPLICATION SLAVE, REPLICATION CLIENT, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, CREATE USER, EVENT, TRIGGER ON *.* TO 'laravel'@'%' WITH GRANT OPTION;

-- Refresh privileges
FLUSH PRIVILEGES;
