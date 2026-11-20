# Functional requirements audit

> Historical audit: the failures below were subsequently fixed. The final regression run passed **89 PHP tests (912 assertions)** and **21 JavaScript tests**. A fresh live Groq synthetic connection test also passed. Revision requests now tolerate omitted comments and roll back on notification failure; reports exclude superseded versions; Admin can edit subject metadata; Repository Files is reachable from All Submissions. The removed sidebar entry and admin grade override remain removed. See `FUNCTIONAL_REQUIREMENTS_VERIFICATION_FINAL.md` for current verification scope.

Date: November 17, 2026 (Asia/Manila).

## Result

**Not ready for an unconditional “all functions working” sign-off.**

- Existing PHP suite: 79 passed, 747 assertions.
- Added workflow audit: 6 passed, 130 assertions.
- Added gap checks: 2 failed, 3 assertions. These are intentionally retained as acceptance tests for the unresolved findings.
- JavaScript suite: 21 passed. These use simulated DOM/browser APIs rather than a live Chrome session.
- Live `php artisan groq:test`: passed using `openai/gpt-oss-20b`. The request used only a synthetic 2 + 2 question, not student work. The first sandboxed attempt could not connect through its proxy; the authorized external retry succeeded.

Application code was not changed during this audit. Added files are tests and this report. Database feature tests use isolated SQLite data and test storage. Profile-photo tests create temporary photos and remove them afterward.

## Confirmed failures

### F1 — Requirement 40: optional revision comment can trigger HTTP 500

Reproduction: sign in as the owning adviser and POST a grade decision with `decision=returned` and no `adviser_comment` field.

Expected: return the work with the default revision message because the comment is optional.

Actual: `Undefined array key "adviser_comment"` in `app/Http/Controllers/AdviserController.php:228`. The submission status is updated before the failure, leaving a returned submission without the corresponding notification/audit completion.

Scope: the normal HTML form includes the comment field, even when blank, and the normal commented return flow passed. The failure concerns a valid request omitting the optional field.

Acceptance test: `FunctionalRequirementsGapsTest::test_requirement_40_returning_without_optional_comment_must_not_error`.

### F2 — Requirement 41: completion percentage includes obsolete versions

Reproduction: one activity has one student's version 1 marked `superseded` and version 2 marked `graded`.

Actual: the report counts two submissions and one graded submission, producing 50% completion. The student's current work is fully graded. `AdviserController::reports` counts all versions in its denominator.

Expected for current-work completion: exclude superseded versions and show 100%. If historical-version completion was intended instead, that definition should be stated explicitly in the requirement and UI.

Acceptance test: `FunctionalRequirementsGapsTest::test_requirement_41_completion_counts_current_work_not_superseded_versions`.

## Scope differences and limitations

- **45 — Administrator subject management:** creation, directory filtering, enrollment management, and classroom editing exist and pass. Admin has no endpoint/form for editing an existing subject's name, adviser assignment, school year, semester, or description. Advisers can edit their own subject details. Full admin subject editing/deletion is therefore not verified or implemented.
- **47 — Repository monitoring:** admin list/inspect/download endpoints work, but the Repository Monitor sidebar link was removed at the user's request. This requirement is not reachable through that sidebar entry.
- **49 — Grade oversight:** admin can view final grades in All Submissions and inspect AI evaluations. Admin grade override was intentionally removed, including its endpoint. The inspection page no longer shows the old Administrator Grade Decision panel. Also, the adviser error message about returning a published grade still tells the user to contact the administrator to correct it, despite the removed override feature.
- **35 — Activity editing:** deliberately blocked after the first student submission; it is not unrestricted editing of published activities.
- **22 — Student AI results:** AI recommendations/feedback are shown only after the adviser finalizes a grade. This is intentional in the current implementation.
- **38/39/48 — AI:** mocked-provider grading/recovery tests and a live synthetic connection passed. This does not certify grading accuracy, every document extraction format, scanned-document OCR, or continuous provider availability.
- No new live Chrome walkthrough, real-camera capture, browser password-manager verification, or mobile/accessibility visual audit was performed. PHP feature tests render pages and exercise requests; JS tests simulate browser APIs. Database tests used SQLite, not the live deployment database engine.

## Requirement-by-requirement results

“Pass” means the implemented workflow passed the automated checks described here; it is not a guarantee of all devices, concurrency cases, or future external-service availability.

| # | Requirement | Result | Evidence / qualification |
|---|---|---|---|
| 1 | User Login Page | Pass | Guest redirect and rendered login pages; validation markup. |
| 2 | Student Login | Pass | Credential login and independent workspace session. |
| 3 | Adviser Login | Pass | Credential login and independent workspace session. |
| 4 | Administrator Login | Pass | Credential login and independent workspace session. |
| 5 | Invalid Login Validation | Pass | Wrong credentials, invalid fields, wrong portal, inactive account. |
| 6 | Role-Based Route Protection | Pass | Wrong-role access, ownership, enrollment, and workspace checks. |
| 7 | Logout and Session Invalidation | Pass | Guest state after logout; one workspace logout preserves other roles. |
| 8 | Profile Photo Upload or Replace | Pass | All roles upload and replace; old photo removed; invalid file rejected. |
| 9 | Profile Photo Removal | Pass | All roles: DB path cleared and stored photo deleted. |
| 10 | Notifications and Read Status | Pass | Selected notification/read-all; ownership; workspace-aware redirect. |
| 11 | Student Dashboard | Pass | Authenticated dashboard renders successfully. |
| 12 | Student My Rooms | Pass | Enrolled rooms render; edited details propagate; live-update endpoint scoped. |
| 13 | Join Room Using Enrollment Key | Pass | Preview, confirmation, matching-room enrollment and notifications. |
| 14 | Room Enrollment Validation | Pass | Missing/wrong/old keys, full/closed/inactive rooms, duplicate enrollment. |
| 15 | Leave Room | Pass | Enrollment deleted and access revoked; student can rejoin with valid key. |
| 16 | Student Activities List | Pass | Enrollment, section and subject filtering; unpublished work excluded. |
| 17 | View Activity Details | Pass | Correct student access; draft/unrelated activity blocked. |
| 18 | Download Instruction and Rubric Files | Pass | Successful downloads for authorized roles; enrollment/publication restrictions. |
| 19 | Submit Activity as Text or File | Pass | Text/file uploads; disabled options enforced; empty/invalid work validation. |
| 20 | Submission Versioning and Late Status | Pass | Versions increment, old work superseded, late flag and deadline rules. |
| 21 | Student Submission History and Download | Pass | Versions displayed; file download; another student's download blocked. |
| 22 | Student Grades and AI Result | Pass with condition | Final grade and AI feedback visible after grade release; private before it. |
| 23 | Create Repository Item | Pass | Text and file repository creation. |
| 24 | Repository Download and Delete | Pass | Owner download/delete, disk cleanup, unrelated user denied. |
| 25 | Open Student-Adviser Room Chat | Pass | Enrolled student and owning adviser; former/unrelated users denied. |
| 26 | Send Chat Message | Pass | Both directions, validation, safe text output, atomic notification failure test. |
| 27 | Chat Auto-Refresh and Read Tracking | Pass (automated) | Polling batches/cursor/read tracking, multiple sessions, Enter behavior. No new live-browser walkthrough. |
| 28 | Adviser Dashboard | Pass | Authenticated dashboard renders successfully. |
| 29 | Adviser Rooms List and Create Room | Pass | Own rooms, combined subject/classroom and additional room creation. |
| 30 | Generate or Regenerate Enrollment Key | Pass | New unique key; replaced key rejected; current key accepted. |
| 31 | Open or Close Room Enrollment | Pass | Toggle persists; closed room prevents joining. |
| 32 | Adviser Room Roster and Remove Student | Pass | Roster visible; removal deletes enrollment and chat access. |
| 33 | Create Adviser Activity | Pass | Room-bound activity with text or uploaded instructions/rubric. |
| 34 | Instruction and Rubric Validation | Pass | Required content source, file validation, at least one submission mode. |
| 35 | Edit Adviser Activity | Pass with condition | Authorized edits before first submission; locked afterward. |
| 36 | Publish or Unpublish Activity | Pass | Status toggles; student access follows publication state. |
| 37 | Adviser View and Download Submissions | Pass | Owning adviser renders student work and downloads files. |
| 38 | Run Groq AI Assessment as Adviser | Pass with limits | Mocked endpoint grading/recovery plus successful live synthetic Groq connection. |
| 39 | Adviser AI Score and Criterion Feedback | Pass with limits | Validated assessment storage and rendered criteria; not grading-quality certification. |
| 40 | Finalize Grade or Return for Revision | **Partial — F1** | Finalization and commented revision pass; omitted optional comment causes 500. |
| 41 | Adviser Scores and Reports | **Partial — F2** | Scores/reports render; completion count includes superseded versions. |
| 42 | Administrator Dashboard | Pass | Authenticated dashboard renders successfully. |
| 43 | Administrator Create and Update Users | Pass | Creation/update persist; invalid names/emails/passwords rejected. |
| 44 | Administrator Reset Password and Account Status | Pass | New password accepted, old rejected, disabled login denied, re-enable works. |
| 45 | Administrator Subject Management | **Partial scope** | Create/filter and room editing work; existing subject editing/deletion absent for admin. |
| 46 | Administrator Enroll or Unenroll Student | Pass | Enrollment persists, capacity enforced, unenrollment revokes access. |
| 47 | Administrator Academic and Repository Monitoring | **Partial navigation** | Academic pages and repository routes pass; repository sidebar entry intentionally absent. |
| 48 | Administrator Groq AI Settings and Connection Test | Pass with limits | Encrypted key settings tests; live synthetic Groq connection passed. |
| 49 | Administrator AI Monitor and Grade Oversight | Pass for read-only scope | Monitor and final-grade list pass; override intentionally unavailable. |
| 50 | System Monitor and Audit Logs | Pass | Monitor/audit pages render; important actions produce persisted audit records. |

## Reproduction commands

```text
php vendor/phpunit/phpunit/phpunit --no-progress
php vendor/phpunit/phpunit/phpunit --filter FunctionalRequirementsAuditTest --no-progress
php vendor/phpunit/phpunit/phpunit --filter FunctionalRequirementsGapsTest --no-progress
node --test --test-isolation=none tests/js/*.test.cjs
php artisan groq:test
```

The complete PHP suite now includes the two failing acceptance checks. A green run should not be claimed until the findings are corrected or the report-completion definition is explicitly changed.
