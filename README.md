# AmeyoJ CRM (CakePHP 5)

> **Static build notice:** This `5_newcrm_static` copy removes the legacy custom-field
> module entirely. Customers/contacts now use fixed, hard-coded fields defined in
> `AppController::$defaultFieldConfig`, and the Custom Fields admin UI no longer exists.

Modernised OMS/CRM rebuilt from the legacy CakePHP 2 app. This README provides both technical notes for developers and business-level guidance for operators/PMs.

---

## 1. Technical Guide

### Stack
* PHP 8.x + CakePHP 5.x
* MySQL 5.7/8.0
* Composer for dependency management

### Setup / Key Commands
```bash
cd 5_newcrm_static
composer install
cp config/app_local.example.php config/app_local.php   # update DB creds
bin/cake migrations migrate                            # apply database schema
bin/cake server                                        # optional dev server
bin/cake cache clear_all                               # clear caches when needed
```

### Migrations
* Stored under `config/Migrations`.
* Always run `bin/cake migrations migrate` after pulling.
* To create a new migration: `bin/cake bake migration AddSomething`.
* Notable migrations:
  * `20250521152000_MinifySchema.php` – shrinks base tables to minimal columns.
  * `20250522110000_DropCustomFieldTables.php` – permanently removes the legacy custom_fields tables.

### Deployment Steps
1. `git pull`
2. `composer install --no-dev`
3. Update `config/app_local.php` with production DB/SMTP, etc.
4. `bin/cake migrations migrate`
5. Ensure `tmp/` and `logs/` have write permissions by web user.
6. Clear caches (`bin/cake cache clear_all`).

### Testing Status
* Currently manual only.
* Recommended future work: PHPUnit tests around the new static field metadata, search builders, and CSV rendering.

### Coding Conventions
* CakePHP PSR-12 style; controllers thin, models handle querying.
* Dynamic UI/CSV/search should use metadata (no hard-coded field lists).
* Use migrations for any DB change; avoid ad-hoc SQL.

### Troubleshooting / FAQ
* **Add new field:** update `AppController::$defaultFieldConfig` and run any required migrations.
* **Search not showing new field:** ensure the field entry has `'searchable' => true` (and set `search_order` if needed).
* **Reset AUTO_INCREMENT:** `TRUNCATE TABLE customers; TRUNCATE TABLE contacts;` (only if wiping data).
* **CSV columns wrong:** adjust the definition in `AppController::$defaultFieldConfig` (`show_in_export` flag).

### Static Field Metadata
* All business-facing fields are described in `AppController::$defaultFieldConfig`.
* Each field controls:
  * Form rendering (section, column span).
  * List columns (show_in_index / show_in_contact_index).
  * CSV columns (show_in_export).
  * Search behavior (`searchable`, `search_type`, `search_order`).
* Controllers call `getCustomFields()` / `getSearchFields()` to consume the static metadata uniformly.

---

## 2. Module Overview (Functional + Technical)

### Customers
* **Purpose:** Manage customer master records.
* **Key screens/actions:** search, index, add, edit, delete, CSV export.
* **Roles/permissions:** view for all; delete requires auth > 0; custom fields creation requires auth == 99.
* **Dynamic behavior:**
  * Forms and list columns render from custom fields.
  * Search form is built from metadata (system + custom). System fields (name/phone/email/fax/IDs/dates/users) always exist.
  * CSV export uses custom field “エクスポート” flags.

### Contacts
* **Purpose:** Log interactions/notes tied to customers.
* **Key screens:** search/index, add (per customer), edit, delete.
* **Permissions:** similar to Customers (delete restricted).
* **Dynamic features:** same as Customers—forms/list/search/CSV all rely on metadata.
* **Additional info:** Contact add page shows customer summary using core fields (name/phone/email/fax).

### Settings (legacy modules)
* **Purpose:** Manage queues/statuses/import settings inherited from legacy CRM.
* **Permissions:** typically auth > 0; adjust as needed.
* **Dynamic behavior:** largely static, but future custom fields can target these modules if desired.

### Imports
* **Purpose:** Bulk import customers via CSV.
* **Actions:** download template, upload CSV, review summary.
* **Notes:** Template matches current minimal fields + essential legacy placeholders.

### Homes / dashboard
* **Purpose:** landing page after login (call history, API status, etc.).
* **Permissions:** standard.

### Users
* **Purpose:** auth management.
* **Permissions:** only admins (auth == 99).

### Other Controllers (Mt*, Settings, etc.)
* Manage supporting master data (queues/statuses). Follow existing pattern; dynamic fields can be added later if required.

---

## 3. User Guide (Non-technical)

(unchanged – see original section)

---

## 4. Technical Appendices

### Schema Summary
* **customers**: `id`, `name`, `phone`, `email`, `fax`, `zip_cd`, `addr1`, `addr2`, `sex`, `birth`, `remark`, audit columns.
* **contacts**: `id`, `customer_id`, `status`, `summary`, `detail`, `remark`, `phone`, `queue_name`, `callback_datetime`, audit columns.
* Custom field tables were removed in this static build; add new fields via migrations + `AppController::$defaultFieldConfig`.

### Migration Usage
* `bin/cake bake migration <Name>` creates a skeleton.
* File naming: `YYYYMMDDHHMMSS_Name.php`.
* After editing, run `bin/cake migrations migrate` locally and in deployments.

### Deployment Recap
1. Pull latest code.
2. `composer install --no-dev`.
3. Update `.env`/`config/app_local.php`.
4. Run migrations.
5. Set permissions for `tmp` & `logs`.
6. Clear caches.
7. Restart PHP-FPM/Apache if needed.

### Testing Instructions
* Manual smoke tests:
  * Customers/Contacts add/edit/delete/search/export.
  * Confirm fixed field layouts (forms/index/search/CSV) render as expected.
  * Verify search filters honor `AppController::$defaultFieldConfig` definitions.
* Future: add PHPUnit for controllers/models, Cypress for critical UI flows.

---

## 2. User Guide (Non-technical)

### Login & Navigation
* Visit `/users/login`.
* Main menu includes: Home, 顧客検索 (Customers), 対応履歴検索 (Contacts), 設定, ユーザ管理 (if permitted), ログアウト.

### Customers
* Search using the built-in filters (氏名/電話/メール/ID/ステータスなど).
* “新規登録” to add; “編集”/“削除” buttons in the list (delete requires admin).
* CSV export uses the current filter conditions.
* All visible fields and columns are defined in `AppController::$defaultFieldConfig`.

### Contacts
* Access via menu; search panel works like Customers.
* “新規登録” prompts you to select a customer (double-clicking a customer row also jumps here).
* Contact add view shows customer summary and the fixed contact fields.
* Delete buttons require admin privilege.

### Settings / Imports / Others
* `設定` retains legacy options (queues, statuses, etc.).
* Imports controller now supports template download and CSV upload (see UI for required columns).

### Tips for New Operators
1. **Need a new field?** Ask a developer to add it via migration + `AppController::$defaultFieldConfig`.
2. **Need a new search filter?** Update the corresponding field definition (`'searchable' => true`).
3. **CSV columns**: Controlled by each field's `'show_in_export'` flag in the config array.

### Support / Troubleshooting
* If search returns no results, verify the field is marked `'searchable' => true` in `AppController::$defaultFieldConfig`.
* If forms fail to save, validation errors appear in the flash message.
* For schema issues, rerun `bin/cake migrations migrate`.

---

## 3. Deployment Checklist
1. `git pull`
2. `composer install --no-dev` (for prod)
3. Copy/update `config/app_local.php`
4. `bin/cake migrations migrate`
5. Ensure webroot permissions for uploads/logs as needed
6. `bin/cake cache clear_all` if metadata changes

---

## 4. Future Enhancements
* Automated tests (PHPUnit) for controllers/models.
* Role-based access control on custom fields/settings.
* File attachments and audit logs.
* Multi-tenant deployments (env-based DB config).

---

For any onboarding questions, share this README and provide database credentials plus initial admin login to the new engineer. Field layouts/search/CSV columns are now configured code-side via `AppController::$defaultFieldConfig`, so changes ship via standard pull requests/deployments. Good luck!
