<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Grade;
use App\Models\Room;
use App\Models\Submission;
use App\Services\AiGradingService;
use App\Support\Audit;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdviserController extends Controller
{
    public function activities()
    {
        $activities = Activity::with(['subject','room'])->withCount('submissions')
            ->where('adviser_id', auth()->id())->latest()->paginate(20);
        return view('adviser.activities.index', compact('activities'));
    }

    public function createActivity()
    {
        $rooms = Room::with('subject')
            ->whereHas('subject', fn ($q) => $q->where('adviser_id', auth()->id())->where('active', true))
            ->where('active', true)
            ->orderBy('name')
            ->get();
        return view('adviser.activities.create', compact('rooms'));
    }

    public function storeActivity(Request $request)
    {
        $data = $this->validateActivity($request);
        $room = $this->ownedRoom((int) $data['room_id']);
        $this->validateSubmissionOptions($request);
        $this->validateInstructionSource($request, $data);
        $this->validateRubricSource($request, $data);

        $activity = Activity::create([
            'subject_id' => $room->subject_id,
            'room_id' => $room->id,
            'adviser_id' => auth()->id(),
            'title' => $data['title'],
            'instructions' => filled($data['instructions'] ?? null) ? trim($data['instructions']) : null,
            'rubric_text' => filled($data['rubric_text'] ?? null) ? trim($data['rubric_text']) : null,
            'due_at' => $data['due_at'] ?? null,
            'max_score' => $data['max_score'],
            'status' => $data['status'],
            'allow_text' => $request->boolean('allow_text'),
            'allow_file' => $request->boolean('allow_file'),
            'allow_late' => $request->boolean('allow_late'),
            'accepted_types' => 'pdf,docx,txt,md,pptx',
        ]);

        $this->saveActivityFiles($request, $activity);
        Audit::log(auth()->user(), 'activity_created', 'activity', $activity->id, [
            'status' => $activity->status,
            'room_id' => $room->id,
        ]);
        $this->notifyStudentsIfPublished($activity);

        return redirect()->route('adviser.activities.show', $activity)
            ->with('success', 'Activity and rubric saved for '.$room->name.'. Students in this room can now access it when published.');
    }

    public function editActivity(Activity $activity)
    {
        $this->assertOwned($activity);
        if ($activity->submissions()->exists()) {
            return redirect()->route('adviser.activities.show', $activity)
                ->withErrors(['activity' => 'This activity is locked because submissions already exist. Create a new activity if the instructions or rubric must change.']);
        }
        $rooms = Room::with('subject')
            ->whereHas('subject', fn ($q) => $q->where('adviser_id', auth()->id())->where('active', true))
            ->orderBy('name')
            ->get();
        return view('adviser.activities.edit', compact('activity', 'rooms'));
    }

    public function updateActivity(Request $request, Activity $activity)
    {
        $this->assertOwned($activity);
        if ($activity->submissions()->exists()) {
            return back()->withErrors(['activity' => 'Activity instructions and rubric are locked after the first submission.']);
        }

        $data = $this->validateActivity($request);
        $room = $this->ownedRoom((int) $data['room_id']);
        $this->validateSubmissionOptions($request);
        $this->validateInstructionSource($request, $data, $activity);
        $this->validateRubricSource($request, $data, $activity);

        $activity->update([
            'subject_id' => $room->subject_id,
            'room_id' => $room->id,
            'title' => $data['title'],
            'instructions' => filled($data['instructions'] ?? null) ? trim($data['instructions']) : null,
            'rubric_text' => filled($data['rubric_text'] ?? null) ? trim($data['rubric_text']) : null,
            'due_at' => $data['due_at'] ?? null,
            'max_score' => $data['max_score'],
            'status' => $data['status'],
            'allow_text' => $request->boolean('allow_text'),
            'allow_file' => $request->boolean('allow_file'),
            'allow_late' => $request->boolean('allow_late'),
            'accepted_types' => 'pdf,docx,txt,md,pptx',
        ]);

        $activity->rubricCriteria()->delete();
        $this->removeActivityFileIfRequested($request, $activity, 'instruction');
        $this->removeActivityFileIfRequested($request, $activity, 'rubric');
        $this->saveActivityFiles($request, $activity);

        Audit::log(auth()->user(), 'activity_updated', 'activity', $activity->id, ['room_id' => $room->id]);
        return redirect()->route('adviser.activities.show', $activity)->with('success', 'Activity, room assignment, and rubric updated.');
    }

    public function showActivity(Activity $activity)
    {
        $this->assertOwned($activity);
        $activity->load(['subject','room'])->loadCount('submissions');
        return view('adviser.activities.show', compact('activity'));
    }

    public function togglePublish(Activity $activity)
    {
        $this->assertOwned($activity);
        $oldStatus = $activity->status;
        $activity->update(['status' => $activity->status === 'published' ? 'draft' : 'published']);
        Audit::log(auth()->user(), 'activity_status_changed', 'activity', $activity->id, ['status' => $activity->status]);
        if ($oldStatus !== 'published' && $activity->status === 'published') $this->notifyStudentsIfPublished($activity);
        return back()->with('success', 'Activity status changed to '.$activity->status.'.');
    }

    public function downloadInstruction(Activity $activity)
    {
        $this->assertOwned($activity);
        return $this->downloadActivityFile($activity->instruction_file_path, $activity->instruction_original_filename);
    }

    public function downloadRubric(Activity $activity)
    {
        $this->assertOwned($activity);
        return $this->downloadActivityFile($activity->rubric_file_path, $activity->rubric_original_filename);
    }

    public function submissions(Activity $activity)
    {
        $this->assertOwned($activity);
        $activity->load(['subject','room']);
        $submissions = Submission::with(['student', 'latestAiEvaluation', 'grade'])
            ->where('activity_id', $activity->id)->latest('submitted_at')->paginate(30);
        return view('adviser.submissions.index', compact('activity', 'submissions'));
    }

    public function scores(Request $request)
    {
        $request->validate(['subject_id' => ['nullable', 'integer'], 'room_id' => ['nullable', 'integer']]);
        $subjects = \App\Models\Subject::where('adviser_id', auth()->id())->orderBy('name')->get();
        $subjectId = $request->input('subject_id');
        if ($subjectId) abort_unless($subjects->contains('id', $subjectId), 403);
        $activityIds = Activity::where('adviser_id', auth()->id())->pluck('id');
        $submissions = Submission::with(['student','activity.subject','activity.room','latestAiEvaluation','grade'])
            ->whereIn('activity_id', $activityIds)
            ->when($subjectId, fn ($q) => $q->whereHas('activity', fn ($a) => $a->where('subject_id', $subjectId)))
            ->where('status', '!=', 'superseded');

        if ($request->filled('room_id')) {
            $roomId = (int) $request->input('room_id');
            $this->ownedRoom($roomId);
            $submissions->whereHas('activity', fn ($q) => $q->where('room_id', $roomId));
        }

        $submissions = $submissions->orderBy(Activity::select('subject_id')->whereColumn('activities.id', 'submissions.activity_id'))->latest('submitted_at')->paginate(30)->withQueryString();
        $rooms = Room::with('subject')
            ->whereHas('subject', fn ($q) => $q->where('adviser_id', auth()->id()))
            ->orderBy('name')->get();

        return view('adviser.scores', compact('submissions','rooms','subjects'));
    }

    public function showSubmission(Submission $submission, AiGradingService $ai)
    {
        $submission->load(['activity.subject', 'activity.room', 'student', 'aiEvaluations', 'grade.finalizedBy']);
        $this->assertOwned($submission->activity);
        $aiStatus = $ai->configurationStatus();
        return view('adviser.submissions.show', compact('submission', 'aiStatus'));
    }

    public function downloadSubmission(Submission $submission)
    {
        $submission->load('activity');
        $this->assertOwned($submission->activity);
        return $this->downloadActivityFile($submission->file_path, $submission->original_filename);
    }

    public function runAi(Submission $submission, AiGradingService $ai)
    {
        $submission->load('activity');
        $this->assertOwned($submission->activity);
        try {
            $evaluation = $ai->evaluate($submission);
            return back()->with('success', $evaluation->is_demo
                ? 'Demo workflow generated. Configure the AI provider first, then run the assessment again for a real rubric score.'
                : 'AI rubric assessment completed. Review the recommendation and approve or change the final grade before students can see it.');
        } catch (Throwable $e) {
            return back()->withErrors(['ai' => 'AI evaluation failed: '.$e->getMessage()]);
        }
    }

    public function finalizeGrade(Request $request, Submission $submission)
    {
        $submission->load(['activity', 'latestAiEvaluation']);
        $this->assertOwned($submission->activity);
        abort_if($submission->status === 'superseded', 422, 'This version was replaced by a newer submission.');
        $data = $request->validate([
            'final_score' => ['required', 'numeric', 'min:0', 'max:'.$submission->activity->max_score],
            'decision' => ['required', 'in:approved,modified'],
            'adviser_comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $latestAi = $submission->latestAiEvaluation;
        // Human evaluation remains possible when Groq is unavailable. A demo
        // or failed AI response is never treated as an actual recommendation.
        if ($latestAi && $latestAi->is_demo) $latestAi = null;

        $grade = Grade::updateOrCreate(['submission_id' => $submission->id], [
            'ai_score' => $latestAi?->recommended_score,
            'adviser_score' => $data['final_score'],
            'final_score' => $data['final_score'],
            'decision' => $data['decision'],
            'admin_override' => false,
            'adviser_comment' => $data['adviser_comment'] ?? null,
            'finalized_by' => auth()->id(),
            'finalized_at' => now(),
        ]);
        $submission->update(['status' => 'graded']);
        Notifier::send(
            $submission->student_id,
            'Grade finalized',
            $submission->activity->title.' final score: '.$grade->final_score.'/'.$submission->activity->max_score,
            route('student.submissions.show', $submission)
        );
        Audit::log(auth()->user(), 'grade_finalized', 'grade', $grade->id, [
            'submission_id' => $submission->id,
            'ai_score' => $latestAi?->recommended_score,
            'final_score' => $grade->final_score,
            'decision' => $grade->decision,
        ]);
        return back()->with('success', 'Final grade saved and released to the student.');
    }

    public function reports()
    {
        $activities = Activity::with(['subject','room'])
            ->withCount(['submissions' => fn($q) => $q->where('status', '!=', 'superseded'), 'submissions as graded_count' => fn($q) => $q->where('status', 'graded')])
            ->where('adviser_id', auth()->id())->latest()->get();
        return view('adviser.reports', compact('activities'));
    }

    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:180'],
            'instructions' => ['nullable', 'string', 'max:30000'],
            'rubric_text' => ['nullable', 'string', 'max:50000'],
            'instruction_file' => ['nullable', 'file', 'max:15360', 'extensions:pdf,docx,txt,md,pptx', 'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/markdown,application/zip,application/x-zip-compressed,application/octet-stream'],
            'rubric_file' => ['nullable', 'file', 'max:15360', 'extensions:pdf,docx,txt,md,pptx', 'mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/markdown,application/zip,application/x-zip-compressed,application/octet-stream'],
            'due_at' => ['nullable', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'status' => ['required', 'in:draft,published'],
            'allow_text' => ['nullable', 'boolean'],
            'allow_file' => ['nullable', 'boolean'],
            'allow_late' => ['nullable', 'boolean'],
            'remove_instruction_file' => ['nullable', 'boolean'],
            'remove_rubric_file' => ['nullable', 'boolean'],
        ]);
    }

    private function validateSubmissionOptions(Request $request): void
    {
        if (!$request->boolean('allow_text') && !$request->boolean('allow_file')) {
            throw ValidationException::withMessages(['allow_text' => 'Allow at least text or file submission.']);
        }
    }

    private function validateInstructionSource(Request $request, array $data, ?Activity $activity = null): void
    {
        $hasText = filled($data['instructions'] ?? null);
        $hasUpload = $request->hasFile('instruction_file');
        $hasExistingFile = $activity?->instruction_file_path && !$request->boolean('remove_instruction_file');
        if (!$hasText && !$hasUpload && !$hasExistingFile) {
            throw ValidationException::withMessages([
                'instructions' => 'Add instructions in the text box or upload an instruction/reference file before saving the activity.'
            ]);
        }
    }

    private function validateRubricSource(Request $request, array $data, ?Activity $activity = null): void
    {
        $hasText = filled($data['rubric_text'] ?? null);
        $hasUpload = $request->hasFile('rubric_file');
        $hasExistingFile = $activity?->rubric_file_path && !$request->boolean('remove_rubric_file');
        if (!$hasText && !$hasUpload && !$hasExistingFile) {
            throw ValidationException::withMessages([
                'rubric_text' => 'Add the adviser rubric in the rubric text box or upload a rubric file before saving the activity.'
            ]);
        }
    }

    private function saveActivityFiles(Request $request, Activity $activity): void
    {
        $changes = [];
        foreach (['instruction', 'rubric'] as $kind) {
            $field = $kind.'_file';
            if (!$request->hasFile($field)) continue;

            $oldPath = $activity->{$kind.'_file_path'};
            if ($oldPath) Storage::disk('local')->delete($oldPath);

            $file = $request->file($field);
            $path = $file->store('activity-materials/'.auth()->id().'/'.$activity->id, 'local');
            $changes[$kind.'_file_path'] = $path;
            $changes[$kind.'_original_filename'] = $file->getClientOriginalName();
            $changes[$kind.'_mime_type'] = $file->getMimeType();
            $changes[$kind.'_file_size'] = $file->getSize();
        }
        if ($changes) $activity->update($changes);
    }

    private function removeActivityFileIfRequested(Request $request, Activity $activity, string $kind): void
    {
        if (!$request->boolean('remove_'.$kind.'_file')) return;
        $path = $activity->{$kind.'_file_path'};
        if ($path) Storage::disk('local')->delete($path);
        $activity->update([
            $kind.'_file_path' => null,
            $kind.'_original_filename' => null,
            $kind.'_mime_type' => null,
            $kind.'_file_size' => null,
        ]);
    }

    private function notifyStudentsIfPublished(Activity $activity): void
    {
        if ($activity->status !== 'published') return;
        $activity->loadMissing(['subject','room.students']);
        $students = $activity->room ? $activity->room->students : $activity->subject->students;
        foreach ($students as $student) {
            Notifier::send(
                $student->id,
                'New activity posted',
                $activity->title.' was posted in '.($activity->room?->name ?: $activity->subject->name).'.',
                route('student.activities.show', $activity)
            );
        }
    }

    private function downloadActivityFile(?string $path, ?string $filename)
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        return Storage::disk('local')->download($path, $filename ?: basename($path));
    }

    private function assertOwned(Activity $activity): void
    {
        abort_unless($activity->adviser_id === auth()->id(), 403);
    }

    private function ownedRoom(int $roomId): Room
    {
        return Room::with('subject')
            ->whereKey($roomId)
            ->whereHas('subject', fn ($q) => $q->where('adviser_id', auth()->id()))
            ->firstOrFail();
    }
}
