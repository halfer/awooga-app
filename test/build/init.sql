/* Run this file as MySQL root */

CREATE DATABASE awooga_test DEFAULT CHARACTER SET utf8;
USE awooga_test;

DROP USER awooga_user_test@localhost;
CREATE USER 'awooga_user_test'@'localhost' IDENTIFIED BY 'password';
GRANT
	* ON awooga.*
	TO 'awooga_user_test'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
