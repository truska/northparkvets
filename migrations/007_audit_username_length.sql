-- Required on production as well as development: audit usernames store login emails.
-- Preserve existing values and NOT NULL; safe to rerun on this schema.
ALTER TABLE `cms_log` MODIFY COLUMN `username` VARCHAR(255) NOT NULL;
