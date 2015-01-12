/* Run this file as MySQL root */

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
	/* Are we using this? */
	updated_at DATETIME
);

CREATE TABLE issue (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	code VARCHAR(30) NOT NULL,
	description VARCHAR(1024)
);

/* Users are separate from their auth usernames, so we can support several per user */
CREATE TABLE user (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	/* This can be for a nicer username that does not relate to the auth provider */
	username VARCHAR(128) NOT NULL,
	access_level ENUM ('reporter', 'admin') DEFAULT 'reporter' NOT NULL
);

/* Store provider-specific data here */
CREATE TABLE user_auth (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	user_id INTEGER NOT NULL,
	/* Provider-specific username */
	username VARCHAR(128) NOT NULL,
	provider VARCHAR(32) NOT NULL,
	last_login_at DATETIME NOT NULL,

	CONSTRAINT user_auth_user_fk FOREIGN KEY (user_id) REFERENCES `user` (id)
);

CREATE TABLE report (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	/* We use either a repo +or+ a user, hence the lack of NOT NULL constraints */
	repository_id INTEGER,
	user_id INTEGER,
	title VARCHAR(256) NOT NULL,
	/* Description is in markdown */
	description VARCHAR(1024),
	description_html VARCHAR(1024),
	/* Only day and not time is important here */
	author_notified_at DATE,
	is_enabled BOOLEAN NOT NULL DEFAULT TRUE,

	CONSTRAINT report_repo_fk FOREIGN KEY (repository_id) REFERENCES repository (id),
	CONSTRAINT report_user_fk FOREIGN KEY (user_id) REFERENCES `user` (id)
);

CREATE TABLE resource_url (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	report_id INTEGER NOT NULL,
	url VARCHAR(256) NOT NULL,

	FOREIGN KEY (report_id) REFERENCES report (id)
);

CREATE TABLE report_issue (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	report_id INTEGER NOT NULL,
	/* Description is in markdown */
	description VARCHAR(1024),
	description_html VARCHAR(1024),
	issue_id INTEGER NOT NULL,
	/* Optional fixed date to indicate something has been improved or removed */
	resolved_at DATE,

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
	('variable-injection'),
	('email-header-injection'),
	('upload-arbitrary-file'),
	('uncategorised')
;

CREATE TABLE run (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	created_at DATETIME NOT NULL
);

/* The retry count is the last X rows for a repo that are unsuccessful */
CREATE TABLE repository_log (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	repository_id INTEGER NOT NULL,
	run_id INTEGER NOT NULL,
	/* These are the phases associated with updating a repo */
    log_type ENUM('fetch', 'move', 'scan', 'resched'),
	/* Successful ops probably don't need a log */
	message VARCHAR(256),
	created_at DATETIME NOT NULL,
	log_level ENUM ('success', 'trivial', 'serious') NOT NULL,

	CONSTRAINT repository_log_repo FOREIGN KEY (repository_id) REFERENCES repository (id),
	CONSTRAINT repository_log_run FOREIGN KEY (run_id) REFERENCES run (id)
);
