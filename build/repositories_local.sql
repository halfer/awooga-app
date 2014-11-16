/* Using IGNORE in these statements to skip items that already exist */

USE awooga;

INSERT IGNORE INTO repository (id, url, mount_path, created_at) VALUES
	(1, 'file:///home/jon/Development/Personal/Awooga/reports', 'halfer_awooga', '2014-11-15 21:33')
;
