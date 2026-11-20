# School Repository 2.0 — Improved Source Package

## What was changed

This package is a modification of the uploaded Laravel 12 system. It is **not** a replacement application.

- **Adviser subjects:** Added a visible two-step management screen to create a subject and then create classrooms. Subject details can be edited without changing enrollment records. Existing adviser subject creation/update routes are wired to the forms.
- **Classroom management:** Added editable classroom information (name, section, location, schedule and capacity) directly to the classroom details page. Unique enrollment keys, roster, removal, room activation and chat functions remain available.
- **Student courses:** Reorganized enrolled-room actions into a responsive dropdown. Students can open activities or message the assigned adviser from the class menu.
- **Administrator accounts:** Improved account creation and management forms, added search and role filtering, and surfaced the existing CSV bulk-import endpoint. Server-side email, name, password, uniqueness and role validation remain mandatory.
- **Security:** Prevented student access to unpublished activities and downloads through direct URLs. Updated chat authorization so neither advisers nor students can continue an old classroom conversation after enrollment is removed.
- **AI grading:** Kept Groq's rubric-based structured JSON workflow and adviser approval of grades. UI clarifies that an AI recommendation stays private until a final grade is published. Added additional consistency checks for rubric totals; errors are surfaced rather than silently saving invalid scores. Large combined evaluation prompts now fail explicitly rather than silently truncating student work or rubric text.
- **Submissions:** Returned submissions can be resubmitted after the original deadline by adviser's explicit revision request. Returning an already published grade for revision is blocked.
- **Validation:** Strong name/password/email rules were wired into administrator account creation and edits. Passwords require 8 or more characters, an uppercase letter and a special character; names allow Unicode letters, spaces, apostrophes and hyphens.
- **Regression coverage:** Added `tests/Feature/WorkflowHardeningTest.php` for adviser subject/class creation, authorization, draft visibility, private messages and account validation.

## Verification performed here

| Check | Result |
| --- | --- |
| PHP source syntax (`php -l`) across project controllers/models/routes and tests | **PASS** (no PHP syntax errors) |
| Changed Blade views: compile to PHP and PHP syntax check | **PASS** for five changed Blade views |
| `node --test tests/js/sidebar.test.cjs` | **PASS**: 4/4 |
| Original uploaded SQLite database integrity check | **PASS** (`PRAGMA integrity_check`: ok) |
| Laravel PHPUnit feature/integration suite | **BLOCKED**, not passed: this environment's PHP lacks `dom`, `mbstring`, `xmlwriter` and `pdo_sqlite` extensions |
| Vite build | **BLOCKED**: uploaded Windows `node_modules` lacks this Linux host's Rollup binary. The current UI uses the existing `public/css/app.css` and `public/js/app.js` directly. |
| Groq live API call, browser sign-in, DB writes, multi-user load, deployment | **NOT VERIFIED**: requires your real application configuration and a working PHP/database environment |

No claim is made that unrun tests passed or that the application is production-ready.

## Before updating your actual system

1. **Back up** the ORIGINAL project directory, database, `.env`, uploaded documents, photos and other user data.
2. Extract this ZIP into a **NEW folder**. Do not overwrite your live project automatically.
3. Copy your own private `.env` and `database/database.sqlite` from your ORIGINAL project into the new directory. They are deliberately **excluded from this shareable ZIP**, because they can contain API credentials, personally identifiable user data and encrypted settings. Keep the **same** `APP_KEY` for previously encrypted Groq settings.
4. Copy any private uploaded activity/submission files from your ORIGINAL `storage/app` directory, and profile photos from `public/uploads`. Do not put users' private data in a public repository.
5. Use a Windows PHP installation with PHP >= 8.2 and extensions: `pdo_sqlite` (if using SQLite), `mbstring`, `dom`, `xml`, `fileinfo`, `openssl`; enable `zip` or an appropriate alternative for document extraction.
6. Open a terminal from the **new project directory**:

```powershell
php -v
php -m
php artisan migrate --force
php artisan optimize:clear
php artisan test
php artisan serve
```

7. Open `http://127.0.0.1:8000` and test real admin/adviser/student accounts using **test users**, not real student data. The original project supplies Composer `vendor/` libraries; if they are not compatible with your local PHP or are incomplete, run `composer install`. For Vite development, run `npm ci` on your Windows PC with working Node.js rather than reusing the uploaded Windows `node_modules`.
8. Go to **Admin → AI Settings** and add your official Groq API key. Use **Test AI Connection** and run a test with a real rubric and document. AI scores remain adviser recommendations until published.
9. Check that subjects, existing enrollments, submissions, rubric downloads and message history still appear before replacing the live deployment.

**Do not use `php artisan migrate:fresh` on an existing school database.** It drops tables and deletes records. Do not call `db:seed` against production without reviewing the seeded demo accounts and passwords.

## Required final acceptance checks on the user's environment

- Admin: create an account, reject invalid name/email/password, search users, import a CSV, toggle accounts and configure Groq.
- Adviser: create and edit subject; create and edit classroom; generate/copy/reset key; add activity/rubric; chat with enrolled student; run AI checker; approve/modify/return grade.
- Student: login; join an active room with a key; reject a wrong key; access only enrolled class; download rubric; submit, revise returned work, and see only published grades; message adviser.
- Security: reject another student's submission URL; reject un-enrolled chat access for both participants; reject unpublished activities; prevent cross-adviser editing; verify no API key is visible in browser source.

## Compatibility and remaining limits

- Groq endpoint and the default `openai/gpt-oss-20b` model use the documented Groq Chat Completions and strict JSON Schema features, but API success depends on a valid key, account limits, network and selected model.
- Scanned/image-only PDFs require OCR or a suitable text-extraction service. The existing AI service rejects unreadable files rather than inventing scores. On Windows, ensure PDF text extraction is installed or use readable DOCX/TXT files.
- Existing designs and routes are retained; this is a focused UI/functionality pass, **not** a complete redesign, penetration test or production sign-off.
- Existing database records and secrets were not altered during the source-code work.
