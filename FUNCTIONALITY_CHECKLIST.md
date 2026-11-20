# Final Functionality Checklist

## Authentication and roles
- [x] Student, Adviser, and Admin login portals
- [x] Role middleware and active-account checking
- [x] Session regeneration on successful login
- [x] Logout/session invalidation

## Rooms and enrollment
- [x] Adviser room/section management
- [x] Unique enrollment keys
- [x] Student room join/leave
- [x] Adviser room roster and student removal
- [x] Enrollment notifications
- [x] Activities linked to rooms/sections

## Online chat
- [x] Student → Adviser private room chat
- [x] Adviser → Student reply
- [x] Unread tracking
- [x] Chat notifications
- [x] Automatic message refresh

## Activities and submissions
- [x] Adviser creates instructions/rubric as text and/or file
- [x] Activity publishing
- [x] Student file/text submission
- [x] Submission versioning
- [x] Late-submission logic
- [x] Download of instruction/rubric/student files

## Groq AI checker
- [x] Groq API configuration through Admin AI Settings
- [x] Encrypted database key storage
- [x] Terminal configure/status/test commands
- [x] AI compares adviser instructions, adviser rubric, and student answer
- [x] Structured rubric score/feedback storage
- [x] Student can view own AI score
- [x] Adviser can view AI score and finalize/modify/return
- [x] Admin AI monitor
- [x] DOCX/PPTX extraction with ZipArchive
- [x] Windows PowerShell fallback when ZipArchive is unavailable

## Notifications
- [x] New activity
- [x] New submission
- [x] AI result
- [x] Returned submission
- [x] Finalized grade
- [x] Chat
- [x] Room enrollment
- [x] Red notification styling in final UI

## Profile images
- [x] Student profile-photo upload/remove
- [x] Adviser profile-photo upload/remove
- [x] Admin profile-photo upload/remove
- [x] Sidebar image rendering

## Visual update
- [x] User-provided School Repository logo
- [x] User-provided ULS background
- [x] Transparent/glass cards
- [x] Updated login/front page
- [x] Removed V2 COMPLETE sidebar badge
- [x] Adviser demo display name changed to Adviser
