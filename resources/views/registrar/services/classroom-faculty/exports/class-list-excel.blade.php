<table border="1">
    <thead>
        <tr>
            <th colspan="5" style="font-size: 16pt; font-weight: bold; text-align: center;">PAMANTASAN NG LUNGSOD NG PASIG</th>
        </tr>
        <tr>
            <th colspan="5" style="font-size: 12pt; font-weight: bold; text-align: center;">OFFICE OF THE UNIVERSITY REGISTRAR</th>
        </tr>
        <tr>
            <th colspan="5" style="font-size: 14pt; font-weight: bold; text-align: center;">STUDENT CLASS LIST REPORT</th>
        </tr>
        <tr><th colspan="5"></th></tr>
        @if($selectedSubject)
            <tr>
                <th colspan="2" style="font-weight: bold;">SECTION:</th>
                <td colspan="3">{{ $controller->sectionLabel($selectedSubject) }}</td>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">SUBJECT:</th>
                <td colspan="3">{{ $selectedSubject->code }} - {{ $selectedSubject->name }}</td>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">PROFESSOR:</th>
                <td colspan="3">{{ (string) optional($selectedSubject->facultyModel)->name ?: 'TBA' }}</td>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">TOTAL STUDENTS:</th>
                <td colspan="3">{{ $sectionStudents->count() }}</td>
            </tr>
            <tr><th colspan="5"></th></tr>
            <tr>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">#</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">STUDENT NO.</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">NAME</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">COURSE / PROGRAM</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">YEAR LEVEL</th>
            </tr>
        @else
            <tr>
                <th colspan="2" style="font-weight: bold;">SCHOOL YEAR:</th>
                <td colspan="3">{{ $state['selected_school_year'] ?: 'ALL YEARS' }}</td>
            </tr>
            <tr>
                <th colspan="2" style="font-weight: bold;">SEMESTER:</th>
                <td colspan="3">{{ strtoupper($state['selected_semester'] ?: 'ALL SEMESTERS') }}</td>
            </tr>
            <tr><th colspan="5"></th></tr>
            <tr>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">#</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">SECTION</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">CODE</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">SUBJECT DESCRIPTION</th>
                <th style="background-color: #006837; color: #ffffff; font-weight: bold;">SCHEDULE</th>
            </tr>
        @endif
    </thead>
    <tbody>
        @if($selectedSubject)
            @foreach($sectionStudents->values() as $index => $student)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $student->student_no }}</td>
                    <td>{{ strtoupper($student->name) }}</td>
                    <td>{{ optional($student->canonicalCourse)->name }}</td>
                    <td style="text-align: center;">{{ optional($student->yearBlock)->label }}</td>
                </tr>
            @endforeach
        @else
            @foreach($subjects->values() as $index => $subject)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $controller->sectionLabel($subject) }}</td>
                    <td>{{ $subject->code }}</td>
                    <td>{{ strtoupper($subject->name) }}</td>
                    <td>{{ $controller->scheduleLabel($subject) }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
