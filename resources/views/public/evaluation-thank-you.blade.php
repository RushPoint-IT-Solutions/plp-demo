<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Thank You — Faculty Evaluation</title>
<style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:Arial,Helvetica,sans-serif; background:#f1f5f4; color:#1f2937; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
    .pe-card { max-width:440px; width:100%; background:#fff; border-radius:12px; box-shadow:0 4px 18px rgba(0,0,0,.08); padding:40px 30px; text-align:center; }
    .pe-icon { width:56px; height:56px; margin:0 auto 18px; }
    .pe-card h1 { color:#006837; font-size:1.15rem; margin-bottom:8px; }
    .pe-card p { font-size:.88rem; color:#4b5563; line-height:1.5; }
</style>
</head>
<body>
<div class="pe-card">
    <svg class="pe-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg>
    <h1>Thank you for your feedback!</h1>
    <p>Your evaluation for {{ $evaluation->subject_name ?: $evaluation->name }}{{ $evaluation->faculty_name ? ' (Prof. ' . $evaluation->faculty_name . ')' : '' }} has been recorded.</p>
</div>
</body>
</html>
