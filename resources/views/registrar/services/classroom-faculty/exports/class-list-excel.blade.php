@if($selectedSubject)
<table border="1">
    <thead>
        <tr>
            <th colspan="6" style="font-size: 14pt; font-weight: bold; text-align: center;">PAMANTASAN NG LUNGSOD NG PASIG</th>
        </tr>
        <tr>
            <th colspan="6" style="font-size: 10pt; font-weight: bold; text-align: center;">Alcalde Jose Street, Kapasigan, Pasig City</th>
        </tr>
        <tr>
            <th colspan="6" style="font-size: 16pt; font-weight: bold; text-align: center;">CLASS LIST</th>
        </tr>
        <tr>
            <th>Professor:</th>
            <td colspan="2">{{ $controller->professorLabel($selectedSubject) }}</td>
            <th>Lab Professor:</th>
            <td colspan="2"></td>
        </tr>
        <tr>
            <th>Subject:</th>
            <td colspan="2">{{ $controller->subjectLine($selectedSubject) }}</td>
            <th>Department:</th>
            <td colspan="2">{{ strtoupper($controller->departmentLabel($selectedSubject) ?: 'N/A') }}</td>
        </tr>
        <tr>
            <th>SY Year/Sem:</th>
            <td colspan="2">{{ $controller->schoolYearSemesterLabel($selectedSubject) ?: 'N/A' }}</td>
            <th>Total Students:</th>
            <td colspan="2">{{ $sectionStudents->count() }}</td>
        </tr>
        <tr>
            <th>Schedule ID:</th>
            <td colspan="2">{{ $selectedSubject->id }}</td>
            <th>Block Section:</th>
            <td colspan="2">{{ $controller->blockSectionLabel($selectedSubject) ?: 'N/A' }}</td>
        </tr>
        <tr>
            <th>Day &amp; Time:</th>
            <td colspan="2">{{ $controller->dayTimeLabel($selectedSubject) ?: 'TBA' }}</td>
            <th>Lab Day &amp; Time:</th>
            <td colspan="2">{{ (int) $selectedSubject->lab > 0 ? ($controller->dayTimeLabel($selectedSubject) ?: 'TBA') : '' }}</td>
        </tr>
        <tr>
            <th>Room:</th>
            <td colspan="2">{{ $controller->roomLabel($selectedSubject) ?: 'TBA' }}</td>
            <th>Lab Room:</th>
            <td colspan="2">{{ (int) $selectedSubject->lab > 0 ? ($controller->roomLabel($selectedSubject) ?: 'TBA') : '' }}</td>
        </tr>
        <tr>
            <th style="font-weight: bold;">No.</th>
            <th style="font-weight: bold;">Student No.</th>
            <th style="font-weight: bold;">Student Name</th>
            <th style="font-weight: bold;">Course</th>
            <th style="font-weight: bold;">Year Level</th>
            <th style="font-weight: bold;">Sex</th>
        </tr>
    </thead>
    <tbody>
        @forelse($sectionStudents->values() as $index => $student)
            <tr>
                <td>{{ $index + 1 }})</td>
                <td>{{ $student->student_no }}</td>
                <td>{{ strtoupper($student->name) }}</td>
                <td>{{ $controller->studentCourseLabel($student) ?: 'N/A' }}</td>
                <td>{{ $controller->studentYearLevelLabel($student) ?: 'N/A' }}</td>
                <td>{{ $controller->studentSexLabel($student) ?: 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align:center;">No students found for this section.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@else
<table border="1">
    <thead>
        <tr>
            <th colspan="5" style="font-size: 14pt; font-weight: bold; text-align: center;">CLASS LIST SUMMARY</th>
        </tr>
        <tr>
            <th>#</th>
            <th>Section</th>
            <th>Subject Code</th>
            <th>Description</th>
            <th>Schedule</th>
        </tr>
    </thead>
    <tbody>
        @forelse($subjects->values() as $index => $subject)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $controller->sectionLabel($subject) }}</td>
                <td>{{ $subject->code }}</td>
                <td>{{ strtoupper($subject->name) }}</td>
                <td>{{ $controller->scheduleLabel($subject) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align:center;">No class list data found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endif
