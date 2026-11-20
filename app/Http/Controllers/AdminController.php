<?php
namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AiEvaluation;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\RepositoryItem;
use App\Models\Room;
use App\Models\Submission;
use App\Models\SystemSetting;
use App\Models\Subject;
use App\Models\User;
use App\Services\AiGradingService;
use App\Services\SubjectClassroomCreator;
use App\Support\Audit;
use App\Support\AccountValidation;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdminController extends Controller
{
    public function users()
    {
        $query = User::query();
        if (request()->filled('q')) {
            $term = trim((string) request()->query('q'));
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('email', 'like', '%'.$term.'%')
                ->orWhere('school_id', 'like', '%'.$term.'%'));
        }
        if (in_array(request()->query('role'), ['student', 'adviser', 'admin'], true)) {
            $query->where('role', request()->query('role'));
        }
        $users = $query->orderBy('role')->orderBy('name')->paginate(30)->withQueryString();
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        AccountValidation::normalizeNameAndEmail($request);
        $data = $request->validate([
            'name' => AccountValidation::name(),
            'email' => ['required','email',new \App\Rules\EmailAddress(),'max:255', Rule::unique('users', 'email')],
            'password' => AccountValidation::password(),
            'role' => ['required','in:student,adviser,admin'],
            'school_id' => ['nullable','string','max:60','unique:users,school_id'],
            'course' => ['nullable','string','max:100'],
            'year_level' => ['nullable','string','max:30'],
            'section' => ['nullable','string','max:30'],
            'department' => ['nullable','string','max:100'],
        ]);
        $user = User::create(array_merge($data, ['password' => Hash::make($data['password']), 'active' => true]));
        Audit::log(Auth::user(), 'user_created', 'user', $user->id, ['role' => $user->role]);
        return back()->with('success', 'User account created.');
    }

    public function importUsers(Request $request)
    {
        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $handle = fopen($request->file('csv_file')->getRealPath(), 'rb');
        if (!$handle) {
            throw ValidationException::withMessages(['csv_file' => 'The CSV file could not be read.']);
        }

        try {
            $headers = fgetcsv($handle);
            if (!$headers) {
                throw ValidationException::withMessages(['csv_file' => 'The CSV file is empty.']);
            }
            $headers = array_map(fn ($v) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $v))), $headers);
            $requiredHeaders = ['name', 'email', 'password', 'role'];
            if (array_diff($requiredHeaders, $headers) || count($headers) !== count(array_unique($headers))) {
                throw ValidationException::withMessages(['csv_file' => 'CSV headers must include unique name,email,password,role columns.']);
            }
            $allowed = array_merge($requiredHeaders, ['school_id','course','year_level','section','department']);
            $created = DB::transaction(function () use ($handle, $headers, $allowed) {
                $created = 0;
                $rowNumber = 1;
                while (($values = fgetcsv($handle)) !== false) {
                    $rowNumber++;
                    if ($values === [null]) continue;
                    if ($rowNumber > 1001) {
                        throw ValidationException::withMessages(['csv_file' => 'Maximum 1,000 accounts per import.']);
                    }
                    if (count($values) !== count($headers)) {
                        throw ValidationException::withMessages(['csv_file' => 'Row '.$rowNumber.' has an incorrect number of columns.']);
                    }
                    $row = array_intersect_key(array_combine($headers, array_map('trim', $values)), array_flip($allowed));
                    $row['email'] = strtolower((string) ($row['email'] ?? ''));
                    foreach (['school_id', 'course', 'year_level', 'section', 'department'] as $optional) {
                        if (array_key_exists($optional, $row) && $row[$optional] === '') {
                            $row[$optional] = null;
                        }
                    }
                    $rules = [
                        'name' => AccountValidation::name(),
                        'email' => ['required','email',new \App\Rules\EmailAddress(),'max:255',Rule::unique('users', 'email')],
                        'password' => AccountValidation::password(),
                        'role' => ['required','in:student,adviser,admin'],
                        'school_id' => ['nullable','string','max:60',Rule::unique('users', 'school_id')],
                        'course' => ['nullable','string','max:100'],
                        'year_level' => ['nullable','string','max:30'],
                        'section' => ['nullable','string','max:30'],
                        'department' => ['nullable','string','max:100'],
                    ];
                    $validator = Validator::make($row, $rules);
                    if ($validator->fails()) {
                        throw ValidationException::withMessages(['csv_file' => 'Row '.$rowNumber.': '.$validator->errors()->first()]);
                    }
                    $data = $validator->validated();
                    $data['password'] = Hash::make($data['password']);
                    $data['active'] = true;
                    User::create($data);
                    $created++;
                }
                return $created;
            });
            Audit::log(Auth::user(), 'users_bulk_imported', 'user', null, ['count' => $created]);
            return back()->with('success', $created.' accounts imported successfully.');
        } finally {
            fclose($handle);
        }
    }

    public function updateUser(Request $request, User $user)
    {
        AccountValidation::normalizeNameAndEmail($request);
        $data = $request->validate([
            'name' => AccountValidation::name(),
            'email' => ['required','email',new \App\Rules\EmailAddress(),'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required','in:student,adviser,admin'],
            'school_id' => ['nullable','string','max:60','unique:users,school_id,'.$user->id],
            'course' => ['nullable','string','max:100'],
            'year_level' => ['nullable','string','max:30'],
            'section' => ['nullable','string','max:30'],
            'department' => ['nullable','string','max:100'],
        ]);
        if ($user->id === Auth::id() && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own administrator role.']);
        }
        $user->update($data);
        Audit::log(Auth::user(), 'user_updated', 'user', $user->id, ['role' => $user->role]);
        return back()->with('success', 'User account updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate(['password' => AccountValidation::password(true)]);
        $user->update(['password' => Hash::make($data['password'])]);
        Audit::log(Auth::user(), 'user_password_reset', 'user', $user->id);
        return back()->with('success', 'Password reset successfully for '.$user->name.'.');
    }

    public function toggleUser(User $user)
    {
        abort_if($user->id === Auth::id(), 422, 'You cannot disable your own account.');
        $user->update(['active' => !$user->active]);
        Audit::log(Auth::user(), 'user_status_changed', 'user', $user->id, ['active' => $user->active]);
        return back()->with('success', 'User status updated.');
    }

    public function subjects(Request $request)
    {
        $query = Subject::with(['adviser','students','rooms','enrollments.room'])->withCount(['students','activities']);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('class_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhere('instructor_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_code')) $query->where('class_code', $request->query('class_code'));
        if ($request->filled('school_year')) $query->where('school_year', $request->query('school_year'));
        if ($request->filled('semester')) $query->where('semester', $request->query('semester'));
        if ($request->filled('adviser_id')) $query->where('adviser_id', (int) $request->query('adviser_id'));

        $subjects = $query->orderBy('name')->orderBy('class_code')->paginate(20)->withQueryString();
        $advisers = User::where('role','adviser')->where('active',true)->orderBy('name')->get();
        $students = User::where('role','student')->where('active',true)->orderBy('name')->get();
        $classCodes = Subject::whereNotNull('class_code')->where('class_code','!=','')->distinct()->orderBy('class_code')->pluck('class_code');
        $schoolYears = Subject::whereNotNull('school_year')->distinct()->orderByDesc('school_year')->pluck('school_year');
        $semesters = Subject::whereNotNull('semester')->distinct()->orderBy('semester')->pluck('semester');

        return view('admin.subjects', compact('subjects','advisers','students','classCodes','schoolYears','semesters'));
    }

    public function storeSubject(Request $request)
    {
        $request->merge([
            'class_code' => $request->filled('class_code') ? strtoupper(trim((string) $request->input('class_code'))) : null,
            'section' => $request->filled('section') ? strtoupper(trim((string) $request->input('section'))) : null,
        ]);

        $data = $request->validate([
            'class_code' => ['nullable','string','max:30'],
            'room_name' => ['required','string','max:120', new \App\Rules\AcademicName('Classroom name')],
            'capacity' => ['nullable','integer','min:1','max:500'],
            'name' => ['required','string','max:180', new \App\Rules\AcademicName('Subject name')],
            'description' => ['nullable','string','max:2000'],
            'units' => ['nullable','numeric','min:0','max:20'],
            'schedule' => ['nullable','string','max:120'],
            'room' => ['nullable','string','max:80', new \App\Rules\AcademicName('Physical room', true)],
            'instructor_name' => ['nullable','string','max:120'],
            'section' => ['nullable','string','max:80'],
            'adviser_id' => ['required','exists:users,id'],
            'school_year' => ['required','string','max:30'],
            'semester' => ['required','string','max:30'],
        ]);
        abort_unless(User::whereKey($data['adviser_id'])->where('role','adviser')->where('active', true)->exists(), 422);
        $room = app(SubjectClassroomCreator::class)->create($data);
        $subject = $room->subject;

        Audit::log(Auth::user(), 'subject_created', 'subject', $subject->id);
        return back()->with('success', $subject->name.' and classroom '.$room->name.' created. Enrollment key: '.$room->enrollment_key);
    }

    public function editSubject(Subject $subject)
    {
        $subject->load('adviser');
        return view('admin.subject-edit', compact('subject'));
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180', new \App\Rules\AcademicName('Subject name')],
            'class_code' => ['nullable', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'school_year' => ['required', 'string', 'max:30'],
            'semester' => ['required', 'string', 'max:30'],
            'section' => ['nullable', 'string', 'max:80'],
            'units' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'schedule' => ['nullable', 'string', 'max:120'],
            'room' => ['nullable', 'string', 'max:80', new \App\Rules\AcademicName('Physical room', true)],
            'instructor_name' => ['nullable', 'string', 'max:120'],
        ]);
        $subject->update($data);
        Audit::log(Auth::user(), 'admin_subject_updated', 'subject', $subject->id);
        return redirect()->route('admin.subjects.edit', $subject)->with('success', 'Subject details updated. Classrooms, enrollments, and activities are preserved.');
    }

    public function enroll(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'student_id' => ['required','exists:users,id'],
            'room_id' => ['required','exists:rooms,id'],
        ]);
        abort_unless(User::whereKey($data['student_id'])->where('role','student')->exists(), 422);
        $room = Room::whereKey($data['room_id'])->where('subject_id', $subject->id)->firstOrFail();
        $student = User::findOrFail($data['student_id']);
        $changed = app(\App\Services\EnrollmentService::class)->enroll($room, $student, $request->user());
        $message = $changed ? $student->name.' enrolled in '.$room->name.'.' : $student->name.' is already enrolled in '.$room->name.'.';
        return back()->with('success', $message)->with('enrollment_alert', $message);
    }

    public function unenroll(Subject $subject, User $student)
    {
        abort_unless($student->role === 'student', 422);
        Enrollment::where('subject_id', $subject->id)->where('student_id', $student->id)->delete();
        Audit::log(Auth::user(), 'student_unenrolled', 'subject', $subject->id, ['student_id' => $student->id]);
        return back()->with('success', 'Student removed from '.$subject->name.'.');
    }

    public function activities()
    {
        $activities = Activity::with(['subject','adviser'])->withCount('submissions')->latest()->paginate(30);
        return view('admin.activities', compact('activities'));
    }

    public function showActivity(Activity $activity)
    {
        $activity->load(['subject','adviser'])->loadCount('submissions');
        return view('admin.activity-show', compact('activity'));
    }

    public function downloadInstruction(Activity $activity)
    {
        return $this->downloadLocal($activity->instruction_file_path, $activity->instruction_original_filename);
    }

    public function downloadRubric(Activity $activity)
    {
        return $this->downloadLocal($activity->rubric_file_path, $activity->rubric_original_filename);
    }

    public function submissions(Request $request)
    {
        $request->validate(['subject_id' => ['nullable', 'integer', 'exists:subjects,id']]);
        $subjects = Subject::orderBy('name')->get();
        $subjectId = $request->input('subject_id');
        $submissions = Submission::with(['student','activity.subject','activity.room','activity.adviser','latestAiEvaluation','grade'])
            ->when($subjectId, fn ($q) => $q->whereHas('activity', fn ($a) => $a->where('subject_id', $subjectId)))
            ->orderBy(Activity::select('subject_id')->whereColumn('activities.id', 'submissions.activity_id'))
            ->latest('submitted_at')->paginate(30)->withQueryString();
        return view('admin.submissions', compact('submissions', 'subjects'));
    }

    public function showSubmission(Submission $submission)
    {
        $submission->load(['student','activity.subject','activity.adviser','aiEvaluations','grade.finalizedBy']);
        return view('admin.submission-show', compact('submission'));
    }

    public function downloadSubmission(Submission $submission)
    {
        return $this->downloadLocal($submission->file_path, $submission->original_filename);
    }

    public function runAi(Submission $submission, AiGradingService $ai)
    {
        try {
            $evaluation = $ai->evaluate($submission);
            return back()->with('success', $evaluation->is_demo
                ? 'Demo workflow generated. Configure the AI API key for real grading.'
                : 'AI rubric assessment completed successfully.');
        } catch (Throwable $e) {
            return back()->withErrors(['ai' => 'AI evaluation failed: '.$e->getMessage()]);
        }
    }

    public function repository()
    {
        $items = RepositoryItem::with(['user','subject'])->latest()->paginate(30);
        return view('admin.repository', compact('items'));
    }

    public function showRepository(RepositoryItem $item)
    {
        $item->load(['user','subject']);
        return view('admin.repository-show', compact('item'));
    }

    public function downloadRepository(RepositoryItem $item)
    {
        return $this->downloadLocal($item->file_path, $item->original_filename);
    }

    public function monitor()
    {
        $activities = Activity::with(['subject','adviser'])->withCount('submissions')->latest()->paginate(20);
        $submissionStats = [
            'total' => Submission::count(),
            'submitted' => Submission::where('status','submitted')->count(),
            'ai_reviewed' => Submission::where('status','ai_reviewed')->count(),
            'ai_failed' => Submission::where('status','ai_failed')->count(),
            'graded' => Submission::where('status','graded')->count(),
        ];
        return view('admin.monitor', compact('activities','submissionStats'));
    }

    public function aiSettings(AiGradingService $ai)
    {
        $status = $ai->configurationStatus();
        $storedModel = SystemSetting::getValue('groq_model');
        return view('admin.ai-settings', compact('status', 'storedModel'));
    }

    public function saveAiSettings(Request $request)
    {
        $data = $request->validate([
            'groq_api_key' => ['nullable','string','max:500'],
            'groq_model' => ['required','string','max:100'],
            'clear_groq_key' => ['nullable','boolean'],
        ]);

        if ($request->boolean('clear_groq_key')) {
            SystemSetting::setSecret('groq_api_key', null);
        } elseif (filled($data['groq_api_key'] ?? null)) {
            SystemSetting::setSecret('groq_api_key', trim($data['groq_api_key']));
        }
        SystemSetting::setValue('groq_model', trim($data['groq_model']));

        Audit::log(Auth::user(), 'groq_settings_updated', 'system_setting', null, [
            'model' => trim($data['groq_model']),
            'key_changed' => filled($data['groq_api_key'] ?? null) || $request->boolean('clear_groq_key'),
        ]);

        return back()->with('success', 'Groq AI settings saved securely. Use Test AI Connection to verify the API.');
    }

    public function aiMonitor(AiGradingService $ai)
    {
        $evaluations = AiEvaluation::with(['submission.student','submission.activity.subject'])->latest('evaluated_at')->paginate(25);
        $aiStatus = $ai->configurationStatus();
        $aiConfigured = $aiStatus['configured'];
        $aiModel = $aiStatus['model'];
        $aiProvider = $aiStatus['provider'];
        return view('admin.ai-monitor', compact('evaluations','aiConfigured','aiModel','aiProvider','aiStatus'));
    }

    public function testAiConnection(AiGradingService $ai)
    {
        try {
            $result = $ai->testConnection();
            return back()->with('success', $result['message'].' Provider: '.strtoupper($result['provider']).' · Model: '.$result['model']);
        } catch (Throwable $e) {
            return back()->withErrors(['ai' => 'AI connection test failed: '.$e->getMessage()]);
        }
    }

    public function auditLogs(Request $request)
    {
        $data = $request->validate(['role' => ['nullable', 'in:student,adviser,admin,system']]);
        $role = $data['role'] ?? null;
        $logs = AuditLog::with('user')
            ->when($role === 'system', fn ($q) => $q->whereDoesntHave('user'))
            ->when($role && $role !== 'system', fn ($q) => $q->whereHas('user', fn ($u) => $u->where('role', $role)))
            ->latest('created_at')->orderByDesc('id')->paginate(40)->withQueryString();
        return view('admin.audit', compact('logs'));
    }

    private function downloadLocal(?string $path, ?string $filename)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($path && $disk->exists($path), 404);
        return $disk->download($path, $filename ?: basename($path));
    }
}
