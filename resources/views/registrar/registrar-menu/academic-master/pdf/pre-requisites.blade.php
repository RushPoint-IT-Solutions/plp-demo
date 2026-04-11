<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pre-requisites Export</title>
</head>
<body>
    <h2>PLP Pre-requisites</h2>
    <p>
        Program: {{ $payload['program_title'] ?? '' }}<br>
        Course: {{ ($payload['course']['code'] ?? '') . ' - ' . ($payload['course']['name'] ?? '') }}<br>
        Curriculum Year: {{ $payload['curriculum_year'] ?? '' }}<br>
        Generated At: {{ optional($generatedAt)->format('Y-m-d H:i:s') }}
    </p>

    @forelse(($payload['years'] ?? []) as $year)
        <h3>{{ $year['label'] ?? '' }}</h3>

        @foreach(($year['semesters'] ?? []) as $semester)
            <h4>{{ $semester['label'] ?? '' }}</h4>

            @if(!empty($semester['subjects']))
                <table width="100%" border="1" cellspacing="0" cellpadding="4">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Description</th>
                            <th>Credited Units</th>
                            <th>Pre-requisite</th>
                            <th>Co-requisite</th>
                            <th>Equivalent Subject</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semester['subjects'] as $subject)
                            <tr>
                                <td>{{ $subject['code'] ?? '' }}</td>
                                <td>{{ $subject['description'] ?? '' }}</td>
                                <td>{{ $subject['credited_units'] ?? 0 }}</td>
                                <td>{{ $subject['pre_requisite_text'] ?? 'None' }}</td>
                                <td>{{ $subject['co_requisite_text'] ?? 'None' }}</td>
                                <td>{{ $subject['equivalent_subject_text'] ?? 'None' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No subject rows in this semester.</p>
            @endif
        @endforeach
    @empty
        <p>No curriculum subject records found for the selected filters.</p>
    @endforelse
</body>
</html>
