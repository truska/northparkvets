-- MariaDB: optional-field defaults for strict SQL mode; repeatable.
-- Does not update existing rows, column types, nullability, rates, or passwords.
-- Recommended: use 004_required_defaults.php for preflight and target checks.
-- Manual phpMyAdmin use: verify destination status 3 = Overseas (OS),
-- vet type 2 = Staff (CSO), and snag status 1 = Snag Raised before running.
-- DDL commits per table; keep a schema backup before applying on live.

ALTER TABLE `products`
    ALTER COLUMN `sku` SET DEFAULT '',
    ALTER COLUMN `po` SET DEFAULT '',
    ALTER COLUMN `odo` SET DEFAULT '',
    ALTER COLUMN `notes` SET DEFAULT '',
    ALTER COLUMN `slug` SET DEFAULT '',
    ALTER COLUMN `section` SET DEFAULT 0,
    ALTER COLUMN `brand` SET DEFAULT 0,
    ALTER COLUMN `manuf` SET DEFAULT 0,
    ALTER COLUMN `supplier` SET DEFAULT 0,
    ALTER COLUMN `snag_text` SET DEFAULT '',
    ALTER COLUMN `importbatch` SET DEFAULT 0,
    ALTER COLUMN `destination_customer` SET DEFAULT '',
    ALTER COLUMN `product_type` SET DEFAULT '',
    ALTER COLUMN `pallet_type` SET DEFAULT '';

ALTER TABLE `product_import_exceptions`
    ALTER COLUMN `fieldname` SET DEFAULT '',
    ALTER COLUMN `fieldvalue` SET DEFAULT '',
    ALTER COLUMN `userid` SET DEFAULT '';

ALTER TABLE `npe_depot_visits`
    ALTER COLUMN `notes` SET DEFAULT '';

ALTER TABLE `gallery`
    ALTER COLUMN `alttag` SET DEFAULT '',
    ALTER COLUMN `caption` SET DEFAULT '',
    ALTER COLUMN `slug` SET DEFAULT '',
    ALTER COLUMN `keyword` SET DEFAULT '';

ALTER TABLE `npe_destination`
    ALTER COLUMN `destination_status_id` SET DEFAULT 3,
    ALTER COLUMN `vet_type` SET DEFAULT 2;

ALTER TABLE `npe_destination_status`
    ALTER COLUMN `staff_type` SET DEFAULT 'CSO',
    ALTER COLUMN `vet_type_id` SET DEFAULT 2;

ALTER TABLE `npe_snags`
    ALTER COLUMN `vet` SET DEFAULT 0,
    ALTER COLUMN `admin` SET DEFAULT 0,
    ALTER COLUMN `notes` SET DEFAULT '',
    ALTER COLUMN `status` SET DEFAULT 1;

ALTER TABLE `npe_snag_status`
    ALTER COLUMN `code` SET DEFAULT 'TBC';

ALTER TABLE `npe_rates`
    ALTER COLUMN `timeid` SET DEFAULT 'new_id';
