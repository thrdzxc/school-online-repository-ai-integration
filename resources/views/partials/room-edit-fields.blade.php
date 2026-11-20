<div class="grid grid-2">
    <div class="field"><label for="edit-room-name">Classroom Name</label><input id="edit-room-name" name="name" pattern="\p{L}[\p{L}\p{M} ]*" title="Use letters and spaces only." value="{{ old('name', $room->name) }}" placeholder="COMPLAB" maxlength="120" required></div>
    <div class="field"><label for="edit-room-section">Section</label><input id="edit-room-section" name="section" value="{{ old('section', $room->section) }}" maxlength="80"></div>
</div>
<div class="grid grid-2">
    <div class="field"><label for="edit-physical-room">Physical Room</label><input id="edit-physical-room" name="physical_room" pattern="[\p{L}0-9][\p{L}\p{M}0-9 ]*" title="Use letters, numbers, and spaces only." value="{{ old('physical_room', $room->physical_room) }}" placeholder="OS304" maxlength="80"></div>
    <div class="field"><label for="edit-room-capacity">Capacity (optional)</label><input id="edit-room-capacity" type="number" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" max="500" placeholder="No limit"></div>
</div>
<div class="field"><label for="edit-room-schedule">Schedule</label><input id="edit-room-schedule" name="schedule" value="{{ old('schedule', $room->schedule) }}" maxlength="120" placeholder="MW 8:00–9:30 AM"></div>
