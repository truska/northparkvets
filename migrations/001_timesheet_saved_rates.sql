-- MariaDB: repeatable schema change only. Existing rows remain NULL.
-- Run against the selected North Park Vets database on development/staging.
-- NULL means not migrated; 0.00 remains a valid deliberately saved zero rate.
ALTER TABLE `npe_timesheets`
    ADD COLUMN IF NOT EXISTS `rate_time_ov` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_time_cso` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_travel_units` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_travel_miles` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_certs` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_tanker_cert` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_sha_sa` DECIMAL(10,2) NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `rate_courier` DECIMAL(10,2) NULL DEFAULT NULL;
