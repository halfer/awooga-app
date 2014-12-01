/* Separate SQL file so it can be run on its own */

UPDATE issue
	SET description = "The resource may permit user input to be rendered as unauthorised JavaScript in a way that may permit session hijacking."
	WHERE code = 'xss';

UPDATE issue
	SET description = "SQL statements appear to be including unfiltered user input in a way that risks running unauthorised SQL against the database."
	WHERE code = 'sql-injection';

UPDATE issue
	SET description = "Passwords are being stored in plaintext, rather than using an appropriate hashing algorithm."
	WHERE code = 'password-clear';

UPDATE issue
	SET description = "Passwords are being stored using an inappropriate hashing algorithm."
	WHERE code = 'password-inadequate-hashing';

UPDATE issue
	SET description = "The resource makes use of a library that is officially deprecated."
	WHERE code = 'deprecated-library';

UPDATE issue
	SET description = "Whilst the resource may not specifically be at risk of SQL injection, it could do with making use of query parameterisation."
	WHERE code = 'sql-needs-parameterisation';

UPDATE issue
	SET description = "It is possible to present user input to a code example that would modify program variables in a way the author did not intend."
	WHERE code = 'variable-injection';

UPDATE issue
	SET description = "An issue that doesn't have a specific category."
	WHERE code = 'uncategorised';
