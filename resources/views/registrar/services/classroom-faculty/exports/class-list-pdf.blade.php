<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Class List Report</title>
</head>
<body>
    <h2>Class List Report</h2>
    <p>Generated at: {{ now()->format('Y-m-d H:i:s') }}</p>
    <p>School Year: {{ $state['selected_school_year'] !== '' ? $state['selected_school_year'] : 'All' }}</p>
    <p>Semester: {{ $state['selected_semester'] !== '' ? $state['selected_semester'] : 'All' }}</p>

    @if($selectedSubject)
        <h3>Section Details</h3>
        <p>Section: {{ $controller->sectionLabel($selectedSubject) }}</p>
        <p>Subject: {{ $selectedSubject->code }} - {{ $selectedSubject->name }}</p>
        <p>Schedule: {{ $controller->scheduleLabel($selectedSubject) }}</p>
        <p>Professor: {{ optional($selectedSubject->facultyModel)->name ?: 'TBA' }}</p>

        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student No.</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sectionStudents as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $student->student_no }}</td>
                        <td>{{ $student->name }}</td>
                        <td>{{ optional($student->canonicalCourse)->name }}</td>
                        <td>{{ optional($student->yearBlock)->label }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No enrolled students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p>Total Students: {{ $sectionStudents->count() }}</p>
    @else
        <h3>Subject Summary</h3>

        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Section</th>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th>Schedule</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subjects as $index => $subject)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $controller->sectionLabel($subject) }}</td>
                        <td>{{ $subject->code }}</td>
                        <td>{{ $subject->name }}</td>
                        <td>{{ $controller->scheduleLabel($subject) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No subjects found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p>Total Subjects: {{ $subjects->count() }}</p>
    @endif
</body>
</html>
