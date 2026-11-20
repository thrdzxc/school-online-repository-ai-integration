# School Online Repository

Laravel school repository for students, advisers, and administrators. The application now uses **MySQL/MariaDB**, managed through **phpMyAdmin**.

## Database and first run

Follow [database/IMPORT_MYSQL.md](database/IMPORT_MYSQL.md). Start Apache and MySQL in XAMPP, create an empty database named school_repository, and import [database/school_repository.sql](database/school_repository.sql). The import contains all 7 existing accounts with their original passwords and no old academic records.

The project .env already targets 127.0.0.1:3306 with database school_repository and the standard local XAMPP root account. Adjust the port/password for your own installation. Never run migrate:fresh against your imported accounts.

Then run:

```powershell
php artisan config:clear
php artisan migrate --force
php artisan cache:clear
php artisan view:clear
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://127.0.0.1:8000. Use existing credentials. SQL import resets remembered logins but preserves password hashes. The old SQLite database was backed up under storage/app/private/backups and cleaned while keeping accounts.

## Enrollment and activities

- Advisers create subjects and classrooms, and share a unique enrollment key.
- Joining displays an enrollment confirmation dialog and sends in-app notifications to the student and adviser. Admin enrollment uses the same validation and notifications.
- Repeating an enrollment does not duplicate enrollments or notifications. Closed/full rooms reject enrollment.
- Activities appear only for the student's enrolled subject and section. Classroom View Activities links preselect that subject.
- Direct activity URLs, instruction/rubric downloads, and submissions enforce the same enrollment boundary. Drafts remain hidden.
- Account forms show errors beside their fields. Names allow letters, spaces, hyphens, and apostrophes; numbers are rejected. Email requires a complete address, such as student@gmail.com. The server validates submissions even when JavaScript is disabled.
- Default seeding does not add subjects or reset accounts. DemoSeeder is an explicit automated-test fixture.

## Other functions

Student/adviser chat, versioned submissions, adviser grading, personal repositories, profile photos, administrator monitoring, and optional Groq AI grading remain available. Uploads are stored locally. Configure a Groq key in Admin > AI Settings or .env for live AI grading; external AI availability is separate from local application tests.

## Requirements and checks

PHP 8.2+ with pdo_mysql, mbstring, openssl, fileinfo, curl, DOM/XML, and preferably zip. XAMPP MariaDB is supported. pdo_sqlite is used for isolated automated tests.

```powershell
php system_check.php
php role_system_check.php
php artisan test
node --test tests/js/*.test.cjs
```

The PHP suite runs against in-memory SQLite by default and was also verified on an isolated MariaDB database. Keep SQL exports and backups private because they contain account data.
