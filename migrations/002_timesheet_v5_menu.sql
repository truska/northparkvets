-- Timesheet forms only; shared cms_actions and other menus stay unchanged.
UPDATE `cms_admin-menu`
SET `url` = 'recordAddv5.php'
WHERE `id` = 62 AND `form` = 13 AND `url` IN ('recordAddv4.php', 'recordAddv5.php');
UPDATE `cms_admin-menu`
SET `url` = 'recordViewv5.php'
WHERE `id` IN (69, 78) AND `form` IN (13, 21)
  AND `url` IN ('recordViewv4.php', 'recordViewv5.php');
