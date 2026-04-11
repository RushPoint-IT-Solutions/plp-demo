<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subject Configuration Export</title>
</head>
<body>
    <h2>PLP Subject Configuration</h2>
    <p>
        Program: {{ ($payload['course']['code'] ?? '') . ' - ' . ($payload['course']['name'] ?? '') }}<br>
        Curriculum Year: {{ $payload['curriculum_year'] ?? '' }}<br>
        Subject: {{ ($payload['subject']['code'] ?? '') . ' - ' . ($payload['subject']['description'] ?? '') }}<br>
        Credited Units: {{ $payload['subject']['credited_units'] ?? 0 }}<br>
        Generated At: {{ optional($generatedAt)->format('Y-m-d H:i:s') }}
    </p>

    <h3>Pre-requisite(s)</h3>
    @if(!empty($payload['selected']['pre']))
        <ul>
            @foreach($payload['selected']['pre'] as $item)
                <li>{{ ($item['code'] ?? '') . ' - ' . ($item['description'] ?? '') }}</li>
            @endforeach
        </ul>
    @else
        <p>None</p>
    @endif

    <h3>Co-requisite(s)</h3>
    @if(!empty($payload['selected']['co']))
        <ul>
            @foreach($payload['selected']['co'] as $item)
                <li>{{ ($item['code'] ?? '') . ' - ' . ($item['description'] ?? '') }}</li>
            @endforeach
        </ul>
    @else
        <p>None</p>
    @endif

    <h3>Equivalent Subject(s)</h3>
    @if(!empty($payload['selected']['equivalent']))
        <ul>
            @foreach($payload['selected']['equivalent'] as $item)
                <li>{{ ($item['code'] ?? '') . ' - ' . ($item['description'] ?? '') }}</li>
            @endforeach
        </ul>
    @else
        <p>None</p>
    @endif
</body>
</html>
