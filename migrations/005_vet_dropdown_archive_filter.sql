-- Exclude deleted vets from the Consignments and Time Sheets form dropdowns.
-- Depot Visits and Snags already filter archived vets. Safe to run again.
UPDATE `cms_form_field`
SET `sourcesql` = REPLACE(
    `sourcesql`,
    'WHERE `showonweb` = ''Yes''',
    'WHERE `showonweb` = ''Yes'' AND `archived` = 0'
)
WHERE `id` IN (7, 77)
  AND `name` = 'vet'
  AND `sourcesql` LIKE '%FROM `npe_vet`%'
  AND `sourcesql` NOT LIKE '%archived%';
