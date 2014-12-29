/* Users are separate from their auth usernames, so we can support several per user */
CREATE TABLE user (
	id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	/* This can be for a nicer username that does not relate to the auth provider */
	username VARCHAR(128) NOT NULL,
	access_level ENUM ('reporter', 'admin') DEFAULT 'reporter' NOT NULL,
	last_login_at DATETIME NOT NULL
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

ALTER TABLE report MODIFY repository_id INTEGER DEFAULT NULL;
ALTER TABLE report ADD COLUMN user_id INTEGER;
ALTER TABLE report ADD CONSTRAINT report_user_fk FOREIGN KEY (user_id) REFERENCES `user` (id);
