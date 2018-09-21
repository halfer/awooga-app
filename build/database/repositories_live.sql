/* Using IGNORE in these statements to skip items that already exist */

USE awooga;

INSERT IGNORE INTO repository (id, url, created_at) VALUES
	(1, 'https://github.com/halfer/awooga-reports.git', NOW())
;
