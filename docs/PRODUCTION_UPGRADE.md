# JSFIT: existing CakePHP 2 production database to CakePHP 5

Status: deployment plan; the production upgrade migration is NOT prepared or approved for execution yet. The production table definitions and indexes have not been inspected. No production database changes have been made.

## Runtime and release

The current composer.lock pins CakePHP 5.2.9, Authentication 3.3.3, Migrations 4.8.2 and PhpSpreadsheet 5.6.0. These packages require PHP 8.1 or later; choose a currently supported compatible PHP release and rehearse this exact release on it. Do not try running this application with the old production PHP version. PHP CLI and the web server's PHP handler must both use the intended new runtime.

Keep the CakePHP 2 release and its PHP handler separately available during the rehearsal. Deploy CakePHP 5 into a separate release directory; set the new virtual host document root to that release's webroot/. Do not merge the old app directory into the new webroot.

Install the locked production dependencies using the new PHP environment:

```sh
php -v
composer install --no-dev --prefer-dist --optimize-autoloader
composer check-platform-reqs --no-dev
```

Run Composer with the intended PHP binary. Do not use composer update or --ignore-platform-reqs for deployment. Platform checks cover the dependency extensions, including those needed for spreadsheet imports. Also check the PDO driver required by the database.

Create server-specific config/app_local.php with debug=false, database settings, app URL, security salt, session configuration and the intended timezone. Do not copy development credentials. Give the web/PHP user write access to tmp/ and logs/.

Repository-specific dependency trap: config/bootstrap.php loads josegonzalez/php-dotenv when config/.env exists and APP_NAME is absent, but this package is currently require-dev. With --no-dev, use app_local.php/server environment configuration without that .env loader path, or correct the dependency setup before deployment. Do not remove an existing server environment file without first transferring its required settings.

## Existing migration files are not a production upgrade sequence

Do NOT run an unqualified `php bin/cake.php migrations migrate` on the legacy production database. Also, `--target` is an upper version limit, not a command to execute only one file: earlier pending migrations can run too.

| Existing migration | Why it must not be blindly replayed on legacy data |
| --- | --- |
| 20250521152000_MinifySchema.php | Removes customer addresses/telephone fields and contact summary/detail/remark/status/queue fields. Re-adding columns later does not restore their data. |
| 20250522110000_DropCustomFieldTables.php | Removes tables; requires a separate decision about their data. |
| CreateInitialTables / CreateUsers | Initial schema definitions are not a complete reconciliation of already existing legacy tables. |
| 20251222035645_AddMissingCustomerContactColumns.php | Uses legacy-column assumptions, including placement after name_kn and backfill from name_kj. Must be checked against actual production columns. Its down() can remove columns that existed before it ran. |
| 20251225062328_RemoveMemoAndActiveFlgFromMtStatuses.php | Copies active_flg into active but removes the original flag and item_memo. Prefer preserving the legacy columns during initial cutover. |
| 20260424064118_AddUniqueToPhoneNewNew.php | Adds a unique telephone index; existing duplicate numbers can make it fail, and the business rule must be confirmed. |
| 20260507082651_AddSendSmsApiUrlToCoreSettings.php | Despite its name, it removes sendSMS_api_url if present. |
| 20261105000000_RepairLegacyCustomerContactParity.php | Adds some missing fields, but is not a standalone CakePHP 2 upgrade: backfills reference name/phone, it adds client_cd as integer when missing, and it does not cover every table/setting needed by the application. Current incoming auto-create uses string client_cd values. |

Do not import schema-dump-default.lock or a development database over production. The lock file is schema tooling metadata, not a legacy data conversion script. Do not mark all old migrations as executed without establishing a reviewed baseline.

## Information needed to prepare the exact migration

Collect server PHP versions for CLI and web, database version, table definitions, indexes, storage engines, collations and migration history. Share schema only, not customer records, passwords or connection credentials.

Example schema-only export from the database server (replace placeholders):

```sh
mysqldump -h DB_HOST -u DB_USER -p --no-data --skip-triggers --no-tablespaces --result-file=jsfit-production-schema.sql DB_NAME
```

Use a database-client version compatible with the server; omit unsupported client options as needed. `-p` prompts for the password. A schema export is NOT a data backup. Review the export for secrets in defaults/comments before sharing it.

Read-only checks:

```sql
SELECT VERSION();
SHOW TABLES;
SHOW CREATE TABLE customers;
SHOW CREATE TABLE contacts;
SHOW CREATE TABLE users;
SHOW CREATE TABLE core_settings;
SHOW CREATE TABLE mt_statuses;
SHOW CREATE TABLE mt_queues;
SHOW CREATE TABLE mt_phonelists;
SHOW CREATE TABLE mt_cids;
SHOW CREATE TABLE callbacks;
```

Inspect mt_outline/mt_overview and phinxlog if they exist. Do not assume that either table is present. Check duplicate customer codes, telephone numbers, and whether contacts.queue_name stores queue IDs or names on the cloned database before deciding index or value conversions.

## Migration to prepare after schema comparison

Use a dedicated directory such as config/ProductionUpgrade/ containing only the reviewed legacy upgrade migration(s). This directory/migration does not exist yet. The migration should:

- Validate required legacy tables/columns before making changes; fail clearly for an unexpected schema.
- Add required missing columns without dropping existing customer/contact data or changing primary IDs.
- Preserve string customer codes (including leading zeros and new ameyo_ codes), phone numbers and branch codes. Audit existing types and constraints before any widening/conversion.
- Widen users.password to hold password hashes if the legacy definition is too short; preserve all existing password values. No bulk password encryption/decryption is needed for the current legacy password checker.
- Add/backfill customer aliases used by CakePHP 5, including name, phone, phone_only, tel_only, client_cd and company_name, without overwriting populated values.
- Preserve contact summary/detail/remark and add crt_object_id if needed.
- Populate mt_statuses.active from active_flg while keeping original data during cutover.
- Provide core_settings.limit_value from the legacy limit setting while planning compatibility for the old release.
- Create/prepare mt_outline if absent, or reconcile mt_overview if present: Contacts setListValue queries this table even with callback UI disabled.
- Check callback/master tables, date columns and application assumptions against the real schema.
- Resolve queue ID/name compatibility explicitly rather than guessing from the field name.
- Keep indexes consistent with the confirmed one-customer-code/one-record rule. Do not introduce new unique phone constraints merely because a historical migration contains them.
- Be repeat-safe and refuse automatic destructive rollback. MySQL DDL may commit independently, so backup/restore is essential even when application code uses transactions.

All future deployments must explicitly use the dedicated migration source until a deliberate migration-history consolidation is completed. --source selects a directory; it does not provide an independent migration log for that directory.

## Rehearsal and cutover order

1. Take a full recoverable database backup, application/config/upload backup, and verify a restore. For InnoDB a consistent logical backup may use --single-transaction; mixed/MyISAM engines require a backup/locking plan that covers all tables. Retain backups outside public web directories.
2. Restore the production database to an isolated staging database. Point only the new release to that clone. Prevent staging from dialing real customers or calling write-capable production integrations.
3. Prepare the dedicated upgrade migration from the schema comparison. Apply it to the clone, inspect row counts, existing customer/contact values, codes, indexes, status flags, limits and password-column capacity. Rehearse a repeat run.
4. Verify manual login for legacy plain-text users and new hashed users, SSO, logout, incoming lookup for every supported phone column, blank/unknown callers, multiple matches, customer/contact CRUD, invalid-update retry, Delete/New/Back buttons, CSV headers/data, spreadsheet import/reimport, phone/store popups and recording playback. Test Ameyo against an agreed test session.
5. During cutover stop writes from the old UI, imports, scheduled jobs and incoming integrations; take the final consistent backup, then apply the rehearsed migration to the intended production database with the new PHP runtime.
6. Clear schema cache, switch the virtual host/routing and Ameyo integration URLs as required, then smoke-test before reopening normal traffic. A new PHP runtime/session handler can require users to log in again.
7. Monitor application/web/PHP logs and failed imports/API responses. Retain the old release and tested recovery procedure.

The following are templates for AFTER the dedicated migration exists and has passed rehearsal. Do not substitute the historical Migrations directory:

```sh
php bin/cake.php migrations status --source ProductionUpgrade
php bin/cake.php migrations migrate --source ProductionUpgrade --dry-run --no-lock
php bin/cake.php migrations migrate --source ProductionUpgrade --no-lock
php bin/cake.php schema_cache clear
php bin/cake.php migrations status --source ProductionUpgrade
```

Dry-run is a review aid, not a substitute for a real rehearsal against a clone, particularly for migrations with schema-dependent branches. Do not execute PHP migration files directly.

If cutover fails before writes resume, restore the verified backup and old application/PHP/config as rehearsed. If users/integrations have written new data, reconcile those writes before restoring an older backup; switching only the code or blindly restoring the backup can lose data. New hashed passwords will not work with the old plain-text-only checker.

## References

- https://book.cakephp.org/5/en/deployment.html
- https://book.cakephp.org/migrations/4/en/index.html
- Runtime versions above come from this repository's composer.lock, not the latest CakePHP release documentation.
