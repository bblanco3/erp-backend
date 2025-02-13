#!/bin/bash

# Execute the SQL file to grant permissions
mysql -h mysql-db -u root -p"Syn-r-g5!5laravel" < database/grant_permissions.sql

# Verify the grants
mysql -h mysql-db -u root -p"Syn-r-g5!5laravel" -e "SHOW GRANTS FOR 'laravel'@'%';"
