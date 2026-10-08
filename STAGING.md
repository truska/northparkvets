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
