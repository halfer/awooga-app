ALTER TABLE report ADD COLUMN created_at DATETIME;
ALTER TABLE report ADD COLUMN updated_at DATETIME;
UPDATE report SET created_at = NOW() WHERE created_at IS NULL;

