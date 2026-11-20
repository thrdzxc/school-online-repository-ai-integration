# School Online Repository - Feature Update

Updated September 24, 2026.

## Added features

### 1. Room / section enrollment
- New `rooms` table and `Room` model.
- Each room belongs to a subject and has a unique enrollment key.
- Existing subject enrollments are automatically assigned to a default room during migration.
- Students can join using a key from **Student > My Rooms**.
- Advisers can create rooms, view rosters, close/reopen rooms, copy keys, and regenerate keys.
- Admin enrollment now places a student into a selected room.

### 2. Adviser-student online chat
- New private chat conversations tied to a room.
- Students can start a chat with the adviser of an enrolled room.
- Advisers can start a chat from a room roster.
- Messages are stored in the database and the chat window polls for new messages every 4 seconds.
- A notification is created for the recipient whenever a message is sent.

### 3. Notifications
Notifications are generated for important events, including:
- room enrollment,
- new chat messages,
- student submissions,
- AI rubric score availability,
- returned submissions, and
- finalized grades.

### 4. AI score visibility
- Students can see AI rubric recommendations from **AI Scores & Grades** even before the adviser finalizes the academic grade.
- The submission detail page shows criterion-by-criterion AI feedback, strengths, improvements, and score.
- Advisers can see the AI rubric score in submission lists and review the full breakdown before approving/modifying the final grade.
- The adviser remains responsible for the final grade.

### 5. Interface refresh
- New sidebar/navigation styling.
- Modern cards, badges, room cards, chat bubbles, responsive layout, improved forms, and clearer score presentation.
- Added direct navigation for rooms and chat.

## Database update
New migrations:
- `2026_09_24_000010_create_rooms_and_add_room_to_enrollments.php`
- `2026_09_24_000011_create_chat_tables.php`

The included `database/database.sqlite` has already been updated with these tables and current room assignments.

If using another database or a fresh copy, run:

```bash
php artisan migrate
```

Then seed demo data if required:

```bash
php artisan db:seed
```

## AI key
No AI API key is included in `.env`. Configure your own key locally before testing new AI evaluations.
