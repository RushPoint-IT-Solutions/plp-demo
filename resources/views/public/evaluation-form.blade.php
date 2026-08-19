<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Faculty Evaluation — {{ $evaluation->subject_name ?: $evaluation->name }}</title>
<style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:Arial,Helvetica,sans-serif; background:#f1f5f4; color:#1f2937; padding:20px 12px 60px; }
    .pe-card { max-width:680px; margin:0 auto; background:#fff; border-radius:12px; box-shadow:0 4px 18px rgba(0,0,0,.08); overflow:hidden; }
    .pe-header { background:#006837; color:#fff; padding:22px 24px; }
    .pe-header h1 { font-size:1.15rem; margin-bottom:4px; }
    .pe-header p { font-size:.85rem; opacity:.9; }
    .pe-meta { padding:16px 24px; background:#f0fdf4; border-bottom:1px solid #dcfce7; font-size:.82rem; color:#166534; display:flex; flex-wrap:wrap; gap:6px 18px; }
    .pe-body { padding:22px 24px; }
    .pe-block { margin-bottom:26px; }
    .pe-block-title { font-weight:700; color:#006837; font-size:.95rem; margin-bottom:12px; border-bottom:2px solid #d1fae5; padding-bottom:6px; }
    .pe-question { margin-bottom:16px; }
    .pe-question-text { font-size:.88rem; margin-bottom:8px; }
    .pe-likert { display:grid; grid-template-columns:repeat(5,1fr); gap:6px; }
    .pe-likert label { display:flex; flex-direction:column; align-items:center; gap:4px; font-size:.68rem; text-align:center; color:#4b5563; cursor:pointer; padding:8px 4px; border:1px solid #e5e7eb; border-radius:8px; transition:.15s; }
    .pe-likert input { accent-color:#006837; width:16px; height:16px; }
    .pe-likert label:hover { border-color:#86efac; background:#f0fdf4; }
    .pe-likert input:checked + span { font-weight:700; color:#006837; }
    .pe-likert label:has(input:checked) { border-color:#006837; background:#f0fdf4; }
    .pe-error { color:#b91c1c; font-size:.78rem; margin-top:4px; display:none; }
    .pe-submit-row { padding:0 24px 26px; }
    .pe-submit-btn { width:100%; background:#006837; color:#fff; border:none; border-radius:8px; padding:13px; font-size:.95rem; font-weight:700; cursor:pointer; }
    .pe-submit-btn:hover { background:#005028; }
    .pe-footer-note { text-align:center; font-size:.72rem; color:#9ca3af; margin-top:14px; }
    @media (max-width:480px) {
        .pe-likert { grid-template-columns:repeat(5,1fr); }
        .pe-likert label { font-size:.6rem; padding:6px 2px; }
    }
</style>
</head>
<body>
<div class="pe-card">
    <div class="pe-header">
        <h1>Faculty Evaluation Form</h1>
        <p>{{ $evaluation->subject_name ?: $evaluation->name }}{{ $evaluation->faculty_name ? ' — Prof. ' . $evaluation->faculty_name : '' }}</p>
    </div>
    <div class="pe-meta">
        @if($evaluation->program)<span>Program: {{ $evaluation->program }}</span>@endif
        @if($evaluation->academic_year)<span>A.Y. {{ $evaluation->academic_year }}</span>@endif
        @if($evaluation->subject_code)<span>{{ $evaluation->subject_code }}</span>@endif
    </div>

    <form method="POST" action="{{ route('public.evaluation.submit', ['token' => $token]) }}" id="peForm">
        @csrf
        <div class="pe-body">
            <p style="font-size:.82rem;color:#4b5563;margin-bottom:20px;">Please rate each statement honestly. Your responses help improve instruction quality.</p>

            @foreach(($evaluation->blocks ?: []) as $block)
                <div class="pe-block">
                    <div class="pe-block-title">{{ $block['letter'] ?? '' }}. {{ $block['title'] ?? '' }}</div>
                    @foreach(($block['questions'] ?? []) as $index => $question)
                        @php $fieldName = 'answers[' . $block['id'] . '_' . $question['id'] . ']'; @endphp
                        <div class="pe-question">
                            <div class="pe-question-text">{{ $index + 1 }}. {{ $question['text'] ?? '' }}</div>
                            <div class="pe-likert">
                                @foreach([5 => 'Strongly Agree', 4 => 'Agree', 3 => 'Neutral', 2 => 'Disagree', 1 => 'Strongly Disagree'] as $value => $label)
                                    <label>
                                        <input type="radio" name="{{ $fieldName }}" value="{{ $value }}" required>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="pe-submit-row">
            <button type="submit" class="pe-submit-btn">Submit Evaluation</button>
            <p class="pe-footer-note">Your response is recorded once you submit. Thank you for your feedback.</p>
        </div>
    </form>
</div>
</body>
</html>
