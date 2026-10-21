-- Both Form 1 time controls use 15-minute intervals.
-- CMS step is stored in seconds; the shared picker converts 900 to minuteStep 15.
UPDATE `cms_form_field`
SET `step` = 900
WHERE `form` = 1
  AND ((`id` = 11 AND `name` = 'expected_time')
    OR (`id` = 119 AND `name` = 'sealed'))
  AND (`step` IS NULL OR `step` <> 900);
