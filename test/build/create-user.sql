CREATE USER 'awooga_user_test'@'localhost' IDENTIFIED BY 'password';
GRANT
	ALL ON awooga_test.*
	TO 'awooga_user_test'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

