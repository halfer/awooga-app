/* Run this file as MySQL root */

CREATE DATABASE awooga DEFAULT CHARACTER SET utf8;
USE awooga;

DROP USER awooga_user@localhost;
CREATE USER 'awooga_user'@'localhost' IDENTIFIED BY 'password';
GRANT
	SELECT, INSERT, UPDATE, DELETE ON awooga.*
	TO 'awooga_user'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
