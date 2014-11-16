/* Run this file as MySQL root */

CREATE DATABASE awooga DEFAULT CHARACTER SET utf8;
USE awooga;

DROP USER awooga_user@localhost;
CREATE USER 'awooga_user'@'localhost' IDENTIFIED BY 'password';
GRANT
	SELECT, INSERT, UPDATE, DELETE ON awooga.*
	TO 'awooga_user'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

CREATE TABLE repository (
	/* Not auto-increment, so keys can be guaranteed */
	id INTEGER PRIMARY KEY NOT NULL,
	url VARCHAR(256) NOT NULL,
	/* The relative path in the locally mounted file system, null before first pull */
	mount_path VARCHAR(256),
	is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
	created_at DATETIME NOT NULL,
	/* If this is not set, it can be set to now, or in a few hours */
	due_at DATETIME,
	updated_at DATETIME
);

CREATE TABLE issue (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	code VARCHAR(30) NOT NULL,
	description VARCHAR(1024)
);

CREATE TABLE report (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	repository_id INTEGER NOT NULL,
	title VARCHAR(256) NOT NULL,
	description VARCHAR(1024),
	/* Only day and not time is important here */
	author_notified_at DATE,
	is_enabled BOOLEAN NOT NULL DEFAULT TRUE,

	CONSTRAINT report_repo_fk FOREIGN KEY (repository_id) REFERENCES repository (id)
);

CREATE TABLE resource_url (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	report_id INTEGER NOT NULL,
	url VARCHAR(256) NOT NULL,

	FOREIGN KEY (report_id) REFERENCES report (id)
);

CREATE TABLE report_issue(
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	report_id INTEGER NOT NULL,
	description VARCHAR(1024),
	issue_id INTEGER NOT NULL,

	FOREIGN KEY (report_id) REFERENCES report (id),
	FOREIGN KEY (issue_id) REFERENCES issue (id)
);

INSERT INTO issue (code) VALUES
	('xss'),
	('sql-injection'),
	('password-clear'),
	('password-inadequate-hashing'),
	('deprecated-library'),
	('sql-needs-parameterisation'),
	('uncategorised')
;

/* The retry count is the last X rows for a repo that are unsuccessful */
CREATE TABLE repository_log (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	repository_id INTEGER NOT NULL,
	/* These are the phases associated with updating a repo */
    log_type ENUM('fetch', 'move', 'scan'),
	/* Successful ops probably don't need a log */
	message VARCHAR(256),
	created_at DATETIME NOT NULL,
	is_success BOOLEAN NOT NULL,

	CONSTRAINT repository_log_repo FOREIGN KEY (repository_id) REFERENCES repository (id)
);
