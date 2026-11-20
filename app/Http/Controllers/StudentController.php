<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AppNotification;
use App\Models\Submission;
use App\Services\AiGradingService;
use App\Support\Audit;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StudentController extends Controller
{
    public function activities(Request $request)
    {
        $data = $request->validate(['subject_id' => ['nullable', 'integer']]);
        $subjects = auth()->user()->subjects()->orderBy('name')->get();
        $subjectId = $data['subject_id'] ?? null;
        if ($subjectId) abort_unless($subjects->contains('id', $subjectId), 403);
        $activities = Activity::with(['subject', 'room'])->forStudent(auth()->id())
            ->where('status', 'published')
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')->paginate(15)->withQueryString();
        return view('student.activities.index', compact('activities', 'subjects', 'subjectId'));
    }

    public function showActivity(Activity $activity)
    {
        $this->assertEnrolled($activity);
        abort_unless($activity->status === 'published', 404);
        $activity->load(['subject','room']);
        $submissions = Submission::with(['latestAiEvaluation','grade'])
            ->where('activity_id',$activity->id)
            ->where('student_id',auth()->id())
            ->latest('version')->get();
        return view('student.activities.show', compact('activity','submissions'));
    }

    public function submit(Request $request, Activity $activity, AiGradingService $ai)
    {
        $this->assertEnrolled($activity);
        $activity->loadMissing(['subject','room']);
        if ($activity->status !== 'published') abort(403, 'This activity is not accepting submissions.');
        $previous = Submission::where('activity_id', $activity->id)
            ->where('student_id', auth()->id())->orderByDesc('version')->first();
        if ($previous) {
            return back()->withErrors(['submission' => 'You have already submitted this activity. Resubmission is not allowed.']);
        }
        if ($activity->due_at && now()->greaterThan($activity->due_at) && !$activity->allow_late) {
            return back()->withErrors(['submission' => 'The deadline has passed and late submissions are not allowed.']);
        }

        $rules = ['text_content' => ['nullable','string','max:100000']];
        if ($activity->allow_file) $rules['file'] = ['nullable','file','max:15360','extensions:pdf,docx,txt,md,pptx','mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/markdown,application/zip,application/x-zip-compressed,application/octet-stream'];
        $data = $request->validate($rules);
        if (!$activity->allow_text && filled($data['text_content'] ?? null)) return back()->withErrors(['text_content'=>'Text submissions are disabled for this activity.']);
        if (!$activity->allow_file && $request->hasFile('file')) return back()->withErrors(['file'=>'Document uploads are disabled for this activity.']);
        if (!filled($data['text_content'] ?? null) && !$request->hasFile('file')) return back()->withErrors(['submission'=>'Enter text or upload a file.']);

        $version = 1;

        $submission = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $activity, $data, $version) {
            // Serialize submissions for this activity, including simultaneous requests.
            Activity::whereKey($activity->id)->lockForUpdate()->firstOrFail();
            if (Submission::where('activity_id', $activity->id)->where('student_id', auth()->id())->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['submission' => 'You have already submitted this activity. Resubmission is not allowed.']);
            }
            $filePath = $original = $mime = null; $size = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $original = $file->getClientOriginalName();
                $mime = $file->getMimeType();
                $size = $file->getSize();
                $filePath = $file->store('submissions/'.auth()->id().'/'.$activity->id, 'local');
            }

            $submission = Submission::create([
                'activity_id'=>$activity->id,'student_id'=>auth()->id(),'version'=>$version,
                'text_content'=>$data['text_content'] ?? null,'file_path'=>$filePath,'original_filename'=>$original,
                'mime_type'=>$mime,'file_size'=>$size,'status'=>'submitted',
                'is_late'=>$activity->due_at ? now()->greaterThan($activity->due_at) : false,'submitted_at'=>now(),
            ]);
            return $submission;
        });
        Audit::log(auth()->user(),'submission_created','submission',$submission->id,[
            'activity_id'=>$activity->id,'version'=>$version,'room_id'=>$activity->room_id
        ]);
        Notifier::send(
            $activity->adviser_id,
            'New student submission',
            auth()->user()->name.' submitted '.$activity->title.' in '.($activity->room?->name ?: $activity->subject->name).'.',
            route('adviser.submissions.show',$submission)
        );

        $message = 'Submission saved successfully.';
        if (config('services.ai.auto_evaluate')) {
            $aiStatus = $ai->configurationStatus();
            if ($aiStatus['configured']) {
                try {
                    $ai->evaluate($submission);
                    $message .= ' AI assessment completed and sent to your adviser for review. Scores appear after the adviser publishes a grade.';
                } catch (Throwable $e) {
                    $message .= ' AI evaluation could not complete; your adviser can review the error and retry.';
                }
            } else {
                $message .= ' AI checking is waiting for an administrator to configure the AI API key.';
            }
        }
        return redirect()->route('student.activities.show',$activity)->with('success',$message);
    }

    public function submissions()
    {
        $submissions = Submission::with(['activity.subject','activity.room','latestAiEvaluation','grade'])
            ->where('student_id',auth()->id())->latest('submitted_at')->paginate(20);
        return view('student.submissions', compact('submissions'));
    }

    public function showSubmission(Submission $submission)
    {
        abort_unless($submission->student_id === auth()->id(), 403);
        $submission->load(['activity.subject','activity.room','latestAiEvaluation','grade']);
        return view('student.submission-show', compact('submission'));
    }

    public function downloadSubmission(Submission $submission)
    {
        abort_unless($submission->student_id === auth()->id(), 403);
        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path), 404);
        return Storage::disk('local')->download($submission->file_path, $submission->original_filename);
    }

    public function downloadInstruction(Activity $activity)
    {
        $this->assertEnrolled($activity);
        abort_unless($activity->status === 'published', 404);
        abort_unless($activity->instruction_file_path && Storage::disk('local')->exists($activity->instruction_file_path), 404);
        return Storage::disk('local')->download($activity->instruction_file_path, $activity->instruction_original_filename ?: basename($activity->instruction_file_path));
    }

    public function downloadRubric(Activity $activity)
    {
        $this->assertEnrolled($activity);
        abort_unless($activity->status === 'published', 404);
        abort_unless($activity->rubric_file_path && Storage::disk('local')->exists($activity->rubric_file_path), 404);
        return Storage::disk('local')->download($activity->rubric_file_path, $activity->rubric_original_filename ?: basename($activity->rubric_file_path));
    }

    public function grades()
    {
        $submissions = Submission::with(['activity.subject','activity.room','latestAiEvaluation','grade'])
            ->where('student_id', auth()->id())
            ->where('status', '!=', 'superseded')
            ->latest('submitted_at')
            ->paginate(20);
        return view('student.grades', compact('submissions'));
    }

    public function notifications()
    {
        $notifications=AppNotification::where('user_id',auth()->id())->latest()->paginate(20);
        return view('notifications',compact('notifications'));
    }

    public function markNotification(AppNotification $notification)
    {
        abort_unless($notification->user_id===auth()->id(),403);
        $notification->update(['read_at'=>now()]);
        return $notification->url ? redirect($notification->url) : back();
    }

    private function assertEnrolled(Activity $activity): void
    {
        abort_unless(Activity::forStudent(auth()->id())->whereKey($activity->id)->exists(), 403);
    }
}
