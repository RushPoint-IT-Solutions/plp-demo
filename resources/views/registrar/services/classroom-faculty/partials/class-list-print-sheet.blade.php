<div class="cl-print-sheet">
    <div class="cl-print-school">PAMANTASAN NG LUNGSOD NG PASIG</div>
    <div class="cl-print-address">Alcalde Jose Street, Kapasigan, Pasig City</div>
    <div class="cl-print-title">CLASS LIST</div>

    <table class="cl-print-meta">
        <tr>
            <td class="label">Professor:</td>
            <td class="value">{{ $controller->professorLabel($selectedSubject) }}</td>
            <td class="label">Lab Professor:</td>
            <td class="value">&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Subject:</td>
            <td class="value">{{ $controller->subjectLine($selectedSubject) }}</td>
            <td class="label">Department:</td>
            <td class="value">{{ strtoupper($controller->departmentLabel($selectedSubject) ?: 'N/A') }}</td>
        </tr>
        <tr><td colspan="4" style="height:8px;padding:0;"></td></tr>
        <tr>
            <td class="label">SY Year/Sem:</td>
            <td class="value">{{ $controller->schoolYearSemesterLabel($selectedSubject) ?: 'N/A' }}</td>
            <td class="label">Total Students:</td>
            <td class="value">{{ $sectionStudents ? $sectionStudents->count() : 0 }}</td>
        </tr>
        <tr>
            <td class="label">Schedule ID:</td>
            <td class="value">{{ $selectedSubject->id }}</td>
            <td class="label">Block Section:</td>
            <td class="value">{{ $controller->blockSectionLabel($selectedSubject) ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Day &amp; Time:</td>
            <td class="value">{{ $controller->dayTimeLabel($selectedSubject) ?: 'TBA' }}</td>
            <td class="label">Lab Day &amp; Time:</td>
            <td class="value">{{ (int) $selectedSubject->lab > 0 ? ($controller->dayTimeLabel($selectedSubject) ?: 'TBA') : '' }}</td>
        </tr>
        <tr>
            <td class="label">Room:</td>
            <td class="value">{{ $controller->roomLabel($selectedSubject) ?: 'TBA' }}</td>
            <td class="label">Lab Room:</td>
            <td class="value">{{ (int) $selectedSubject->lab > 0 ? ($controller->roomLabel($selectedSubject) ?: 'TBA') : '' }}</td>
        </tr>
    </table>

    <table class="cl-print-table">
        <thead>
            <tr>
                <th class="cl-print-no">No.</th>
                <th class="cl-print-student-no">Student No.</th>
                <th class="cl-print-name">Student Name</th>
                <th class="cl-print-course">Course</th>
                <th class="cl-print-year">Year Level</th>
                <th class="cl-print-sex">Sex</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sectionStudents as $index => $student)
                <tr>
                    <td class="cl-print-no">{{ $index + 1 }})</td>
                    <td class="cl-print-student-no">{{ $student->student_no }}</td>
                    <td class="cl-print-name">{{ strtoupper($student->name) }}</td>
                    <td class="cl-print-course">{{ $controller->studentCourseLabel($student) ?: 'N/A' }}</td>
                    <td class="cl-print-year">{{ $controller->studentYearLevelLabel($student) ?: 'N/A' }}</td>
                    <td class="cl-print-sex">{{ $controller->studentSexLabel($student) ?: 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:16px;">No students found for this section.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
