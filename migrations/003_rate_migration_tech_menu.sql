-- Add the saved-rate migration under Techie Stuff, without a fixed menu ID.
-- Safe to rerun: an existing link is not duplicated.
INSERT INTO `cms_admin-menu`
    (`title`, `form`, `section`, `subsection`, `url`, `var1`, `target`, `icon`, `userrole`, `showonweb`, `archived`)
SELECT
    'Saved Rates Migration', 0, parent.`section`,
    COALESCE((SELECT MAX(child.`subsection`) FROM `cms_admin-menu` AS child WHERE child.`section` = parent.`section`), 0) + 10,
    'rateMigrationPreview.php', NULL, '', 17, 'Tech', 'Yes', 0
FROM `cms_admin-menu` AS parent
WHERE parent.`title` = 'Techie Stuff' AND parent.`subsection` = 0
  AND parent.`userrole` = 'Tech'
  AND NOT EXISTS (
      SELECT 1 FROM `cms_admin-menu` AS existing
      WHERE existing.`url` = 'rateMigrationPreview.php'
        AND existing.`section` = parent.`section`
  )
LIMIT 1;
