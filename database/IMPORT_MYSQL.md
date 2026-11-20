# Import into phpMyAdmin

1. Start **Apache** and **MySQL** in XAMPP.
2. Open `http://localhost/phpmyadmin`.
3. Create an **empty** database named `school_repository`, using `utf8mb4_unicode_ci`.
4. Select that database, click **Import**, and choose `database/school_repository.sql`. Keep the format as SQL, then click **Import / Go**.
5. In this project folder, run:

   ```powershell
   php artisan config:clear
   php artisan migrate --force
   php artisan cache:clear
   php artisan view:clear
   php role_system_check.php
   php artisan serve --host=127.0.0.1 --port=8000
   ```

6. Open `http://127.0.0.1:8000` and use your existing account and password.

The project `.env` is configured for local XAMPP:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_repository
DB_USERNAME=root
DB_PASSWORD=
```

If your MySQL uses another port or password, update these values before clearing configuration. phpMyAdmin is the database management interface; the application connects to MySQL/MariaDB.

## Contents

- Full schema, indexes, foreign keys, and migration history.
- All **7 existing student, adviser, and admin accounts**, including their original password hashes, IDs, roles, profile paths, and academic details.
- No subjects, classrooms, enrollments, activities, submissions, grades, notifications, chat history, repository records, sessions, or old settings.
- Remember-me tokens are reset; sign in again after importing.
- The original SQLite database is backed up under `storage/app/private/backups/before-mysql-cleanup-*.sqlite`. Its working copy was cleaned while retaining accounts. The app now uses MySQL.

Import **once into an empty database**. The file intentionally has no DROP statements, so importing cannot silently replace existing tables. If a database with this name already contains data, create a different empty database and update `DB_DATABASE` instead.

The SQL contains account data and password hashes. Keep it private. Existing profile photo files remain under `public/uploads/profile-photos`.

## Start using the clean system

1. Adviser: create a subject and classroom, then share its enrollment key.
2. Adviser: publish an activity in that classroom.
3. Student: join using the key. A confirmation dialog appears, and the student and adviser receive in-app notifications.
4. Student: choose **View Activities** on that classroom to see its subject's activities. Other subjects and sections remain inaccessible without enrollment.
5. Admin: create or edit accounts. Errors appear beside their fields. Names reject numbers; email requires a full address such as `student@gmail.com`.

Normal `php artisan db:seed` does not add demo subjects or reset account passwords. `DemoSeeder` exists only as an explicit test fixture. Old database settings are excluded. AI grading can use an existing Groq key in `.env`; otherwise configure it in **Admin > AI Settings**. Live AI responses depend on that external service.
