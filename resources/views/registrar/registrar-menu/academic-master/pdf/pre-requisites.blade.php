<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pre-requisites Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { margin: 0 0 4px; font-size: 18px; text-align: center; }
        .meta { width: 100%; margin: 8px 0 12px; border-collapse: collapse; }
        .meta td { padding: 3px 6px; border: 0; }
        h2 { margin: 14px 0 6px; font-size: 13px; color: #064e3b; }
        h3 { margin: 10px 0 5px; font-size: 11px; color: #065f46; }
        table.curriculum { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.curriculum th,
        table.curriculum td { border: 1px solid #9ca3af; padding: 5px; vertical-align: top; word-wrap: break-word; }
        table.curriculum th { background: #e8f5ee; font-weight: bold; text-align: center; }
        .code { width: 12%; }
        .desc { width: 26%; }
        .units { width: 8%; text-align: center; }
        .req { width: 18%; }
        .empty { color: #6b7280; font-style: italic; }
    </style>
</head>
<body>
    <h1>PLP Pre-requisites</h1>
    <table class="meta">
        <tr>
            <td><strong>Program:</strong> {{ ($payload['course']['code'] ?? '') . ' - ' . ($payload['course']['name'] ?? '') }}</td>
            <td><strong>Curriculum Year:</strong> {{ $payload['curriculum_year'] ?? '' }}</td>
            <td><strong>Generated:</strong> {{ optional($generatedAt)->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    @forelse(($payload['years'] ?? []) as $year)
        <h2>{{ $year['label'] ?? '' }}</h2>

        @foreach(($year['semesters'] ?? []) as $semester)
            <h3>{{ $semester['label'] ?? '' }}</h3>

            @if(!empty($semester['subjects']))
                <table class="curriculum">
                    <thead>
                        <tr>
                            <th class="code">Course Code</th>
                            <th class="desc">Description</th>
                            <th class="units">Units</th>
                            <th class="req">Pre-requisite</th>
                            <th class="req">Co-requisite</th>
                            <th class="req">Equivalent Course</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semester['subjects'] as $subject)
                            <tr>
                                <td class="code">{{ $subject['code'] ?? '' }}</td>
                                <td class="desc">{{ $subject['description'] ?? '' }}</td>
                                <td class="units">{{ $subject['credited_units'] ?? 0 }}</td>
                                <td>{{ $subject['pre_requisite_text'] ?? 'None' }}</td>
                                <td>{{ $subject['co_requisite_text'] ?? 'None' }}</td>
                                <td>{{ $subject['equivalent_subject_text'] ?? 'None' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty">No course rows in this term.</p>
            @endif
        @endforeach
    @empty
        <p class="empty">No curriculum course records found for the selected filters.</p>
    @endforelse
</body>
</html>
