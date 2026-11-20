<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::setValue('groq_model', 'openai/gpt-oss-20b');

        User::updateOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User','password' => Hash::make('password'),'role' => 'student','active' => true,
        ]);

        User::updateOrCreate(['email' => 'admin@school.test'], [
            'name' => 'Admin','password' => Hash::make('password'),'role' => 'admin',
            'school_id' => 'ADMIN-001','department' => 'IT Office','active' => true,
        ]);

        $adviser = User::updateOrCreate(['email' => 'adviser@school.test'], [
            'name' => 'Adviser','password' => Hash::make('password'),'role' => 'adviser',
            'school_id' => 'FAC-001','department' => 'College of Information Technology','active' => true,
        ]);

        $student = User::updateOrCreate(['email' => 'student@school.test'], [
            'name' => 'Student','password' => Hash::make('password'),'role' => 'student','school_id' => '2026-0001',
            'course' => 'BSIT','year_level' => '3','section' => 'B','active' => true,
        ]);
        $student2 = User::updateOrCreate(['email' => 'student2@school.test'], [
            'name' => 'Student 2','password' => Hash::make('password'),'role' => 'student','school_id' => '2026-0002',
            'course' => 'BSIT','year_level' => '3','section' => 'B','active' => true,
        ]);

        $rows = [
            ['F85','Networking 1',1.0,'8-9:30 am MW','COMP','R. Dalupang','BSIT 3B'],
            ['F88','Systems Integration and Architecture 1',2.0,'9:30-10:30 am MW','EM319','K. Antonio','BSIT 3B'],
            ['F90','Event Driven Programming',1.0,'11:30 am-12:30 pm MWF','COMP','K. Antonio','BSIT 3B'],
            ['F92','Application Development and Emerging Technologies',1.0,'1:30-3 pm MW','COMP','MJ. Baltazar','BSIT 3B'],
            ['F94','IT Elective 2',1.0,'3-4:30 pm MW','COMP','MJ. Baltazar','BSIT 3B'],
            ['F95','IT Elective 2',2.0,'4:30-5:30 pm MW','EM319','MJ. Baltazar','BSIT 3B'],
            ['F89','Social and Professional Issues 1',3.0,'5:30-7 pm MW','OS303','B. Abad','BSIT 3B'],
            ['F91','Event Driven Programming',2.0,'8-9 am TTh','OS303','K. Antonio','BSIT 3B'],
            ['F86','Networking 1',2.0,'9-10 am TTh','OS304','R. Dalupang','BSIT 3B'],
            ['F87','Systems Integration and Architecture 1',1.0,'10:30 am-12 pm TTh','COMP','K. Antonio','BSIT 3B'],
            ['F93','Application Development and Emerging Technologies',2.0,'3-4 pm TTh','EM320','MJ. Baltazar','BSIT 3B'],
            ['F96','IT Free Elective 2',1.0,'4:30-6 pm TTh','COMP','J. Nacorda','BSIT 3B'],
            ['F98','Foreign Language 1',3.0,'6-7:30 pm TTh','OS302','G. Sedano','BSIT 3B'],
            ['F97','IT Free Elective 2',2.0,'9:30-11:30 am F','EM319','J. Nacorda','BSIT 3B'],
        ];

        $subjects = collect();
        foreach ($rows as [$class,$name,$units,$schedule,$room,$instructor,$section]) {
            $subject = Subject::updateOrCreate(['class_code' => $class, 'name' => $name, 'adviser_id' => $adviser->id, 'school_year' => '2026-2027', 'semester' => '1st Semester'], [
                'class_code' => $class,
                'name' => $name,
                'description' => 'Subject based on the provided 1st Semester 2026-2027 enrollment list.',
                'units' => $units,
                'schedule' => $schedule,
                'room' => $room,
                'instructor_name' => $instructor,
                'section' => $section,
                'adviser_id' => $adviser->id,
                'school_year' => '2026-2027',
                'semester' => '1st Semester',
                'active' => true,
            ]);
            $roomKey = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $class)).'-'.strtoupper(preg_replace('/[^A-Z0-9]/i', '', $section)).'-'.str_pad((string) $subject->id, 3, '0', STR_PAD_LEFT);
            $classRoom = Room::firstOrCreate(['subject_id' => $subject->id], [
                'name' => $name.' · '.$section,
                'section' => $section,
                'enrollment_key' => substr($roomKey, 0, 40),
                'physical_room' => $room,
                'schedule' => $schedule,
                'active' => true,
            ]);
            Enrollment::updateOrCreate(['subject_id' => $subject->id, 'student_id' => $student->id], ['room_id' => $classRoom->id]);
            Enrollment::updateOrCreate(['subject_id' => $subject->id, 'student_id' => $student2->id], ['room_id' => $classRoom->id]);
            $subjects->push($subject);
        }

        $appDev = $subjects->firstWhere('class_code', 'F92') ?? $subjects->first();
        $appDevRoom = Room::where('subject_id', $appDev->id)->orderBy('id')->first();
        $activity = Activity::updateOrCreate([
            'subject_id' => $appDev->id,
            'title' => 'Student Registration Application',
        ], [
            'adviser_id' => $adviser->id,
            'room_id' => $appDevRoom?->id,
            'instructions' => "Create a simple Student Registration Application.\n\nRequirements:\n1. Allow the user to enter student name, student ID, course, and year level.\n2. Include a Save button.\n3. Validate required fields before saving.\n4. Display submitted information clearly.\n5. Keep the interface simple and organized.\n6. Submit a short explanation of how the application works.",
            'rubric_text' => "Functionality - 30 points\nThe application performs the required functions correctly, including data entry, saving, and displaying student information.\n\nRequirements Completion - 25 points\nThe student completed all requirements stated in the activity instructions.\n\nValidation - 15 points\nThe system properly checks required fields and prevents incomplete information from being submitted.\n\nUser Interface - 15 points\nThe application has a clean, organized, and easy-to-understand interface.\n\nCode / Explanation - 15 points\nThe submitted work or explanation clearly shows how the application works and demonstrates understanding of the task.",
            'due_at' => now()->addDays(14),
            'max_score' => 100,
            'status' => 'published',
            'allow_text' => true,'allow_file' => true,'allow_late' => true,
            'accepted_types' => 'pdf,docx,txt,md,pptx',
        ]);
        // This version uses rubric_text / rubric_file, not the old structured rubric builder.
        $activity->rubricCriteria()->delete();
    }
}
