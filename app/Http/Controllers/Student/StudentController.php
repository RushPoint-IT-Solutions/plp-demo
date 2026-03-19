<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Student;
use App\StudentProfile;
use App\Subject;
use App\Semester;
use App\Course;
use App\YearBlock;
use App\StudentSubjectGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class StudentController extends Controller
{
    private function currentStudent()
    {
        $user = Auth::user();

        if ($user && $user->student_id) {
            return Student::with('subjects')->find($user->student_id);
        }

        if ($user && !empty($user->username)) {
            return Student::with('subjects')->where('student_no', $user->username)->first();
        }

        return Student::with('subjects')->first();
    }

    /**
     * Show the Section Offering / COR page.
     */
    public function sectionOffering()
    {
        $student    = $this->currentStudent();
        $subjects   = $student ? $student->subjects : collect();
        $semesters  = Semester::all();
        $courses    = Course::all();
        $yearBlocks = YearBlock::all();

        return view('student.section-offering', compact(
            'student', 'subjects', 'semesters', 'courses', 'yearBlocks'
        ));
    }

    /**
     * Show the Grades page.
     */
    public function grades()
    {
        $student = $this->currentStudent();

        $gradeRows = collect();
        if ($student) {
            $gradeRows = StudentSubjectGrade::with('subject')
                ->where('student_id', $student->id)
                ->get();
        }

        $semesterOptions = $gradeRows
            ->map(function ($row) {
                return optional($row->subject)->school_year . '|' . optional($row->subject)->semester;
            })
            ->filter()
            ->unique()
            ->values();

        $selectedSemester = request('semester');

        if ($selectedSemester) {
            [$selectedSchoolYear, $selectedSem] = array_pad(explode('|', $selectedSemester), 2, null);
            $gradeRows = $gradeRows->filter(function ($row) use ($selectedSchoolYear, $selectedSem) {
                return optional($row->subject)->school_year === $selectedSchoolYear
                    && optional($row->subject)->semester === $selectedSem;
            })->values();
        }

        return view('student.grades', compact('student', 'gradeRows', 'semesterOptions', 'selectedSemester'));
    }

    /**
     * Show the Schedule page.
     */
    public function schedule()
    {
        $student  = $this->currentStudent();
        $subjects = $student ? $student->subjects : collect();

        $dayMap = [
            'Sun' => 'Sunday',
            'M'   => 'Monday',   'Mon' => 'Monday',
            'T'   => 'Tuesday',  'Tue' => 'Tuesday',
            'W'   => 'Wednesday','Wed' => 'Wednesday',
            'Th'  => 'Thursday', 'Thu' => 'Thursday',
            'F'   => 'Friday',   'Fri' => 'Friday',
            'Sat' => 'Saturday',
        ];

        $days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $weekly = array_fill_keys($days, []);

        foreach ($subjects as $subject) {
            foreach (array_map('trim', explode(',', $subject->days ?? '')) as $abbr) {
                if (isset($dayMap[$abbr])) {
                    $weekly[$dayMap[$abbr]][] = $subject;
                }
            }
        }

        return view('student.schedule', compact('student', 'subjects', 'weekly', 'days'));
    }

    /**
     * Show the Events page.
     */
    public function events()
    {
        return view('student.events');
    }

    /**
     * Show a forms sub-module page by category.
     */
    public function forms(string $category)
    {
        $viewByCategory = [
            'add-change-delete' => 'student.forms.add-change-delete',
            'late-leave-appeal' => 'student.forms.late-leave-appeal',
            'change-grade'      => 'student.forms.change-grade',
            'cross-enroll'      => 'student.forms.cross-enroll',
        ];

        if (!array_key_exists($category, $viewByCategory)) {
            abort(404);
        }

        $student = $this->currentStudent();
        $profile = $student
            ? StudentProfile::where('student_no', $student->student_no)->first()
            : null;

        return view($viewByCategory[$category], compact('student', 'profile'));
    }

    /**
     * Show the Profile view page (read-only if complete, else redirect to edit).
     */
    public function profile()
    {
        $student = $this->currentStudent();
        $profile = $student
            ? StudentProfile::where('student_no', $student->student_no)->first()
            : null;

        if (!$profile || !$profile->profile_complete) {
            return redirect()->route('student.profile.edit');
        }

        return view('student.profile-view', compact('profile', 'student'));
    }

    /**
     * Show the Profile edit form (step wizard).
     */
    public function editProfile()
    {
        $student = $this->currentStudent();
        $profile = $student
            ? StudentProfile::where('student_no', $student->student_no)->first()
            : null;

        return view('student.profile', compact('profile'));
    }

    /**
     * Handle profile updates.
     */
    public function updateProfile(Request $request)
    {
        // Normalize all boolean checkboxes: a checked checkbox sends 'on', an unchecked one sends nothing.
        // Laravel 5 does not have $request->boolean(), so we convert them to true/false here.
        $request->merge([
            'same_as_present'  => $request->has('same_as_present'),
            'is_orphan'        => $request->has('is_orphan'),
            'is_first_gen'     => $request->has('is_first_gen'),
            'is_4ps'           => $request->has('is_4ps'),
            'has_disability'   => $request->has('has_disability'),
            'is_foreign'       => $request->has('is_foreign'),
            'mother_pensioner' => $request->has('mother_pensioner'),
            'father_pensioner' => $request->has('father_pensioner'),
            'no_k12'           => $request->has('no_k12'),
        ]);

        // When same as present is checked, disabled fields won't submit — copy them server-side
        if ($request->input('same_as_present')) {
            $request->merge([
                'permanent_street'       => $request->input('present_street'),
                'permanent_barangay'     => $request->input('present_barangay'),
                'permanent_zipcode'      => $request->input('present_zipcode'),
                'permanent_municipality' => $request->input('present_municipality'),
                'permanent_province'     => $request->input('present_province'),
                'permanent_region'       => $request->input('present_region'),
            ]);
        }

        $data = $request->validate([
            // Step 1 — Personal
            'student_number'     => 'required|string|max:50',
            'last_name'          => 'required|string|max:255',
            'first_name'         => 'required|string|max:255',
            'middle_name'        => 'required|string|max:255',
            'suffix'             => 'nullable|string|max:50',
            'nickname'           => 'required|string|max:255',
            'gender'             => 'required|in:Male,Female',
            'nationality'        => 'required|string|max:255',
            'nationality_other'  => 'nullable|string|max:255',
            'religion'           => 'required|string|max:255',
            'religion_other'     => 'nullable|string|max:255',
            'date_of_birth'      => 'required|date',
            'place_of_birth'     => 'required|string|max:255',
            'civil_status'       => 'required|string|max:100',
            'mobile_number'      => 'required|string|max:15',
            'student_email'      => 'required|email|max:255',
            'profile_photo'      => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',

            // Step 1 — Residence
            'present_street'        => 'required|string|max:255',
            'present_barangay'      => 'required|string|max:255',
            'present_zipcode'       => 'required|string|max:10',
            'present_municipality'  => 'required|string|max:255',
            'present_province'      => 'required|string|max:255',
            'present_region'        => 'required|string|max:255',
            'permanent_street'      => 'required|string|max:255',
            'permanent_barangay'    => 'required|string|max:255',
            'permanent_zipcode'     => 'required|string|max:10',
            'permanent_municipality'=> 'required|string|max:255',
            'permanent_province'    => 'required|string|max:255',
            'permanent_region'      => 'required|string|max:255',
            'same_as_present'       => 'sometimes|boolean',
            'is_orphan'             => 'sometimes|boolean',
            'is_first_gen'          => 'sometimes|boolean',
            'is_4ps'                => 'sometimes|boolean',
            'has_disability'        => 'sometimes|boolean',
            'is_foreign'            => 'sometimes|boolean',

            // Step 2 — Family / Guardian
            'mother_firstname'      => 'required|string|max:255',
            'mother_middlename'     => 'required|string|max:255',
            'mother_lastname'       => 'required|string|max:255',
            'mother_contact'        => 'nullable|string|max:15',
            'mother_occupation'     => 'required|string|max:255',
            'mother_pensioner'      => 'sometimes|boolean',
            'father_firstname'      => 'required|string|max:255',
            'father_middlename'     => 'required|string|max:255',
            'father_lastname'       => 'required|string|max:255',
            'father_contact'        => 'nullable|string|max:15',
            'father_occupation'     => 'required|string|max:255',
            'father_pensioner'      => 'sometimes|boolean',
            'guardian_firstname'    => 'required|string|max:255',
            'guardian_middlename'   => 'required|string|max:255',
            'guardian_lastname'     => 'required|string|max:255',
            'guardian_contact'      => 'required|string|max:15',
            'guardian_occupation'   => 'required|string|max:255',
            'guardian_address'      => 'required|string|max:500',
            'parent_marital_status' => 'required|string|max:255',
            'monthly_family_income' => 'required|string|max:255',
            'number_of_siblings'    => 'required|integer|min:0',
            'household_members'     => 'required|integer|min:1',
            'dependents'            => 'required|integer|min:0',

            // Step 3 — Educational
            'junior_school'     => 'required|string|max:255',
            'senior_school'     => 'required|string|max:255',
            'shs_track_strand'  => 'required|string|max:255',
            'no_k12'            => 'sometimes|boolean',
            'lrn'               => 'nullable|string|max:12',

            // Step 4 — Other
            'family_income_source'       => 'required|string|max:255',
            'family_income_source_other' => 'nullable|string|max:255',
            'living_situation'           => 'required|string|max:255',
            'living_situation_other'     => 'nullable|string|max:255',
            'working_student'            => 'required|string|max:255',
            'has_scholarship'            => 'required|string|max:255',
            'first_in_family_college'    => 'required|string|max:255',
            'internet_access'            => 'required|string|max:255',
            'it_tools_access'            => 'required|string|max:255',
            'devices'                    => 'nullable|array',
            'devices.*'                  => 'string|max:100',
            'devices_other'              => 'nullable|string|max:255',
            'lms_used'                   => 'required|string|max:255',
            'lms_used_other'             => 'nullable|string|max:255',
            'lms_preferred'              => 'required|string|max:255',
            'lms_preferred_other'        => 'nullable|string|max:255',
            'lms_reasons'               => 'nullable|array',
            'lms_reasons.*'             => 'string|max:100',
            'lms_reasons_other'          => 'nullable|string|max:255',
            'preferred_class_time'       => 'required|string|max:255',
            'evening_classes'            => 'required|string|max:255',
        ]);

        $student = $this->currentStudent();

        if (!$student) {
            return redirect()->route('module.login', ['module' => 'student'])
                ->withErrors(['username' => 'Student record not found for this account.']);
        }

        $profile = StudentProfile::where('student_no', $student->student_no)->first() ?? new StudentProfile();

        // Map form field names to DB column names
        $profile->student_no    = $student->student_no;
        $profile->first_name    = $data['first_name'];
        $profile->last_name     = $data['last_name'];
        $profile->middle_name   = $data['middle_name'];
        $profile->suffix        = $data['suffix'] ?? null;
        $profile->nickname      = $data['nickname'];
        $profile->gender        = $data['gender'];

        $profile->nationality       = $data['nationality'];
        $profile->nationality_other = $data['nationality_other'] ?? null;
        $profile->religion          = $data['religion'];
        $profile->religion_other    = $data['religion_other'] ?? null;
        $profile->date_of_birth     = $data['date_of_birth'];
        $profile->place_of_birth    = $data['place_of_birth'];
        $profile->civil_status      = $data['civil_status'];
        $profile->mobile_number     = $data['mobile_number'];
        $profile->student_email     = $data['student_email'];

        // Profile photo
        if ($request->hasFile('profile_photo')) {
            $profile->profile_photo_path = $request->file('profile_photo')
                ->store('students/photos', 'public');
        }

        // Residence — present
        $profile->present_street       = $data['present_street'];
        $profile->present_barangay     = $data['present_barangay'];
        $profile->present_zipcode      = $data['present_zipcode'];
        $profile->present_municipality = $data['present_municipality'];
        $profile->present_province     = $data['present_province'];
        $profile->present_region       = $data['present_region'];

        // Residence — permanent
        $profile->permanent_street       = $data['permanent_street'];
        $profile->permanent_barangay     = $data['permanent_barangay'];
        $profile->permanent_zipcode      = $data['permanent_zipcode'];
        $profile->permanent_municipality = $data['permanent_municipality'];
        $profile->permanent_province     = $data['permanent_province'];
        $profile->permanent_region       = $data['permanent_region'];

        // Residence — toggles
        $profile->same_as_present = (bool) $request->input('same_as_present', false);
        $profile->is_orphan       = (bool) $request->input('is_orphan', false);
        $profile->is_first_gen    = (bool) $request->input('is_first_gen', false);
        $profile->is_4ps          = (bool) $request->input('is_4ps', false);
        $profile->has_disability  = (bool) $request->input('has_disability', false);
        $profile->is_foreign      = (bool) $request->input('is_foreign', false);

        // Family
        $profile->mother_firstname  = $data['mother_firstname'];
        $profile->mother_middlename = $data['mother_middlename'];
        $profile->mother_lastname   = $data['mother_lastname'];
        $profile->mother_contact    = $data['mother_contact'] ?? null;
        $profile->mother_occupation = $data['mother_occupation'];
        $profile->mother_pensioner  = (bool) $request->input('mother_pensioner', false);

        $profile->father_firstname  = $data['father_firstname'];
        $profile->father_middlename = $data['father_middlename'];
        $profile->father_lastname   = $data['father_lastname'];
        $profile->father_contact    = $data['father_contact'] ?? null;
        $profile->father_occupation = $data['father_occupation'];
        $profile->father_pensioner  = (bool) $request->input('father_pensioner', false);

        $profile->guardian_firstname  = $data['guardian_firstname'];
        $profile->guardian_middlename = $data['guardian_middlename'];
        $profile->guardian_lastname   = $data['guardian_lastname'];
        $profile->guardian_contact    = $data['guardian_contact'];
        $profile->guardian_occupation = $data['guardian_occupation'];
        $profile->guardian_address    = $data['guardian_address'];

        $profile->parent_marital_status = $data['parent_marital_status'];
        $profile->monthly_family_income = $data['monthly_family_income'];
        $profile->number_of_siblings    = $data['number_of_siblings'];
        $profile->household_members     = $data['household_members'];
        $profile->dependents            = $data['dependents'];

        // Education
        $profile->junior_school    = $data['junior_school'];
        $profile->senior_school    = $data['senior_school'];
        $profile->shs_track_strand = $data['shs_track_strand'];
        $profile->no_k12           = (bool) $request->input('no_k12', false);
        $profile->lrn              = $data['lrn'] ?? null;

        // Other
        $profile->family_income_source       = $data['family_income_source'];
        $profile->family_income_source_other = $data['family_income_source_other'] ?? null;
        $profile->living_situation           = $data['living_situation'];
        $profile->living_situation_other     = $data['living_situation_other'] ?? null;
        $profile->working_student            = $data['working_student'];
        $profile->has_scholarship            = $data['has_scholarship'];
        $profile->first_in_family_college    = $data['first_in_family_college'];
        $profile->internet_access            = $data['internet_access'];
        $profile->it_tools_access            = $data['it_tools_access'];
        $profile->devices                    = json_encode($data['devices'] ?? []);
        $profile->devices_other              = $data['devices_other'] ?? null;
        $profile->lms_used                   = $data['lms_used'];
        $profile->lms_used_other             = $data['lms_used_other'] ?? null;
        $profile->lms_preferred              = $data['lms_preferred'];
        $profile->lms_preferred_other        = $data['lms_preferred_other'] ?? null;
        $profile->lms_reasons                = json_encode($data['lms_reasons'] ?? []);
        $profile->lms_reasons_other          = $data['lms_reasons_other'] ?? null;
        $profile->preferred_class_time       = $data['preferred_class_time'];
        $profile->evening_classes            = $data['evening_classes'];
        $profile->profile_complete           = true;

        $profile->save();

        // Keep legacy student fields aligned with profile edits so pages/forms
        // still reading App\Student values won't show stale seeded data.
        $student->name = trim($profile->first_name . ' ' . $profile->last_name);
        $student->sex = $profile->gender;
        $student->save();

        return redirect()->route('student.profile')->with('success', 'Profile saved successfully!');
    }
}
