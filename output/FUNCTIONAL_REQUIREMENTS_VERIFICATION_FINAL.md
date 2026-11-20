# Final functional verification

Verified November 17, 2026, after correcting the audit findings.

- PHP: **89 tests passed, 912 assertions, zero test failures**.
- JavaScript: **21 tests passed, zero test failures**.
- Blade compilation: passed.
- Changed PHP controller syntax: passed.
- Live Groq synthetic connection and structured assessment: passed, model `openai/gpt-oss-20b`.

## Corrections verified

1. Returning work without an optional comment succeeds and sends the default revision notice.
2. A failed revision notification rolls back the status change instead of leaving partial state.
3. Report completion excludes superseded submission versions.
4. Admin can edit subject name, description, course number, school year, semester, section, units, schedule, subject room, and instructor display name. Assigned adviser, classrooms, enrollments, and activities are preserved. No subject deletion or adviser reassignment is claimed.
5. All Submissions has a Repository Files action. The previously removed sidebar entry remains absent.
6. The adviser error text no longer directs users to the removed admin grade-override function.

## Demonstration scope

- Sign in once for each role using the separate workspace login pages when demonstrating multiple tabs.
- Use an enrolled student and the owning adviser for room/activity/chat demonstrations.
- Edit activity instructions and rubrics before the first submission; the application deliberately locks those changes afterward.
- Finalize an adviser grade before demonstrating student AI-score visibility.
- Admin grade oversight is read-only. It does not include an admin grading override.
- For repository supervision, open Admin > All Submissions > Repository Files.
- For subject editing, expand the subject in Admin > Subjects & Classrooms and click Edit Subject.
- Live AI requires the configured Groq key, internet access, and provider availability. The live check used synthetic data; grading correctness for arbitrary student content is not certified.

These PASS results describe the executed automated acceptance checks and live synthetic API check. No new live Chrome walkthrough, camera-device capture, or every-device browser test was performed. They are not a promise of no possible future errors.

Evidence: `functional-requirements-final-php.txt`, `functional-requirements-final-js.txt`, and the tests under `tests/Feature` and `tests/js`.
