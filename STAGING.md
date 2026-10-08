# Staging setup

This repository contains the web document root. Clone or deploy its contents into the staging site's `web/` directory. Keep `.htaccess` and all application assets.

## Private files and database

Transfer the updated `private/db.php` and `private/dbcon.php` manually into the sibling `private/` directory, outside the document root. Their runtime include refers to `../web/wccms/include/runtime.php`; preserve this directory layout. Configure staging database credentials in those private files and restore the site database separately. Credentials and database dumps are excluded from Git.

## PHP and filesystem

Use PHP 8.4 with MySQLi, sessions, mbstring and the extensions needed by TCPDF (including GD for image processing). The shared runtime selects Europe/London for GMT/BST. Compare upload, post, memory and execution limits with the development/old server for representative imports and reports.

Give the staging PHP user directory traversal and read access to the sibling private directory and both database files. Give that user write access to `wccms/importfiles/`, `wccms/importfiles/done/` and `wccms/uploadedfiles/`, including inherited permissions for new files. Git does not transfer ACLs or ownership. Replace the user and path below with staging values before running as root:

```sh
setfacl -m u:STAGING_PHP_USER:--x /STAGING_SITE/private
setfacl -m u:STAGING_PHP_USER:r-- /STAGING_SITE/private/db.php /STAGING_SITE/private/dbcon.php
setfacl -R -m u:STAGING_PHP_USER:rwX /STAGING_SITE/web/wccms/importfiles /STAGING_SITE/web/wccms/uploadedfiles
find /STAGING_SITE/web/wccms/importfiles /STAGING_SITE/web/wccms/uploadedfiles -type d -exec setfacl -m d:u:STAGING_PHP_USER:rwx {} +
```

Configure HTTPS and Apache rewrite support. Links use the current request host and port. Shared Bootstrap 5.3.8 assets are local; existing other third-party libraries still include external services/CDNs. The fixed NEW DEV SITE banner remains enabled for testing. Two-factor authentication remains disabled.

## Client checks

Test login/logout, navigation on desktop and mobile, record add/edit/copy, password recovery/email, imports, report filtering, CSV/PDF exports and billing with the staging database. Import history and generated upload files are not versioned; copy these separately only if required for testing.

## Known deferred work

The preferences constructor issue, unused/missing plugin loading and future two-factor implementation remain deferred. The existing record-copy path also needs workflow testing. This snapshot upgrades Bootstrap, not every JavaScript library. Do not run `wccms/setup-wccms.sh` on the staging clone: it is a legacy script that changes the Git remote.

## Saved-rate schema and migration preview

Run `migrations/001_timesheet_saved_rates.sql` against the selected staging database in phpMyAdmin's SQL tab. This repeatable MariaDB script adds eight nullable DECIMAL(10,2) columns to `npe_timesheets`; it does not populate rates. NULL means not migrated and is distinct from a saved zero rate. The SQL changes the database schema only; Git deployment cannot apply it automatically.

Administrators and Tech users can open `/wccms/rateMigrationPreview.php` after deploying the migration tool. Preview first, using inclusive work-date bounds or leaving both blank to cover all valid-dated records. Applying fills only NULL saved-rate fields; existing saved rates are preserved. Archived and hidden records are included. Invalid dates are skipped and their IDs reported. Missing or duplicate active rates block applying.

The one-off migration applies two confirmed historical overrides: mileage is 0.50 before 1 May 2026 and OV is 2.49 before 1 September 2026. Otherwise it copies currently active rate values from `npe_rates`. It never changes that table. The full three-period migration can therefore run in one operation without temporarily changing current rates. Applying requires an authenticated Admin/Tech POST with CSRF protection and an unchanged preview, and runs in one database transaction. Original modification timestamps are preserved.

For go-live, import the live timesheets, rerun the schema SQL if necessary, then preview and apply against the fresh data. Repeat runs preserve already populated values. Do not expect this NULL-only tool to correct incorrectly populated saved rates. The three invalid-date development records must be corrected or handled separately before they can be migrated.

Existing record save handling and reports still use their original logic; V5 handling and report changes are later stages. New records created through the old handling will have NULL saved rates until migrated. Date-based rate lookup can be added later without changing the saved-rate columns.

## Timesheet V5 entry handling

Deploy the V5 pages and shared controller changes before running `migrations/002_timesheet_v5_menu.sql`. Verified menu IDs: 62 adds form 13; 69 lists form 13 (ALL); 78 lists form 21 (CURRENT, using data form 13). These three entries switch to V5. The Billing Reports menu and global `cms_actions` definitions stay unchanged. V5 lists rewrite their timesheet action links and expose an Admin/Tech link to the retained migration tool.

New timesheets created via the generic add form, booking modal or direct time-entry page save all eight current rates in the initial INSERT, including backdated new work. Copies get current rates and remain hidden as before. Normal edits preserve saved rates and bulk editing of saved-rate columns is blocked. The current rates table must contain exactly one active valid rate per charge type. Existing V4 timesheet creation routes use the same save helper for compatibility.

Financial report calculations have not yet switched to these saved values; that remains the third stage. Client-test add, edit, copy and booking-modal workflows before live deployment.
