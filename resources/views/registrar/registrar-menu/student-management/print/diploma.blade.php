<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Diploma - {{ strtoupper($student->name) }}</title>
<link rel="stylesheet" href="{{ asset('css/forms.css') }}?v={{ time() }}">
<style>
body {
    margin: 0;
    background: #eef2f0;
    color: #191919;
}

.diploma-direct-toolbar {
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 24px;
    background: #006837;
    color: #fff;
    font-family: Arial, sans-serif;
}

.diploma-direct-toolbar span {
    font-size: 14px;
    font-weight: 700;
}

.diploma-direct-toolbar a {
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    text-decoration: none;
}

.diploma-direct-toolbar a:hover {
    color: #fff;
}

.diploma-direct-print {
    border: 0;
    border-radius: 6px;
    padding: 7px 20px;
    background: #fff;
    color: #006837;
    font-family: Arial, sans-serif;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.diploma-direct-wrap {
    padding: 24px 0;
    overflow-x: auto;
}

.diploma-direct-wrap .dpl-sheet {
    border: none;
}

@media print {
    @page { size: letter landscape; margin: 8mm; }

    body {
        background: #fff;
    }

    .diploma-direct-toolbar {
        display: none !important;
    }

    .diploma-direct-wrap {
        padding: 0;
        overflow: visible;
    }

    .diploma-direct-wrap .dpl-sheet {
        width: 100%;
        min-height: 0;
        margin: 0;
        box-shadow: none;
        border: none;
        padding: 22px 44px;
    }
}
</style>
</head>
<body>
@php
    $profile = $student->profile;
    $fullName = $profile
        ? trim($profile->first_name . ' ' . ($profile->middle_name ? substr($profile->middle_name, 0, 1) . '. ' : '') . $profile->last_name . ($profile->suffix ? ', ' . $profile->suffix : ''))
        : trim((string) $student->name);
    $fullName = $fullName !== '' ? $fullName : (string) $student->name;

    $programLabel = strtoupper(trim((string) (optional($student->canonicalCourse)->name ?: $student->program)));
    $degreeLine = optional($student->canonicalCourse)->name ?: $student->program ?: 'Bachelor of Science in Entrepreneurship';
    if ($programLabel === 'BSIT' || strpos($programLabel, 'INFORMATION TECHNOLOGY') !== false) {
        $degreeLine = 'Bachelor of Science in Information Technology';
    } elseif ($programLabel === 'BSCS' || strpos($programLabel, 'COMPUTER SCIENCE') !== false) {
        $degreeLine = 'Bachelor of Science in Computer Science';
    } elseif ($programLabel === 'BSED' || strpos($programLabel, 'SECONDARY EDUCATION') !== false) {
        $degreeLine = 'Bachelor of Secondary Education';
    } elseif ($programLabel === 'BSBA' || strpos($programLabel, 'BUSINESS ADMINISTRATION') !== false) {
        $degreeLine = 'Bachelor of Science in Business Administration';
    } elseif ($programLabel === 'BS ENTREPRENEURSHIP' || $programLabel === 'BSENT' || strpos($programLabel, 'ENTREPRENEURSHIP') !== false) {
        $degreeLine = 'Bachelor of Science in Entrepreneurship';
    }

    $gt = $graduateTagging;
    $issueDate = $gt && $gt->date_graduated ? $gt->date_graduated : now();
    $day = (int) $issueDate->format('j');
    $suffix = 'th';
    if ($day % 10 === 1 && $day % 100 !== 11) {
        $suffix = 'st';
    } elseif ($day % 10 === 2 && $day % 100 !== 12) {
        $suffix = 'nd';
    } elseif ($day % 10 === 3 && $day % 100 !== 13) {
        $suffix = 'rd';
    }
    $formalIssueDate = $day . $suffix . ' of ' . $issueDate->format('F Y');
@endphp

<div class="diploma-direct-toolbar">
    <span>Diploma - {{ strtoupper($student->name) }}</span>
    <div style="display:flex;gap:12px;align-items:center;">
        <a href="{{ url()->previous() }}">Back to Profile</a>
        <button class="diploma-direct-print" onclick="window.print()">Print Form</button>
    </div>
</div>

<div class="diploma-direct-wrap">
    <article class="dpl-sheet">
        <div class="dpl-header-row">
            <div>
                <div class="dpl-gov-text">City Government of Pasig<br>Republic of the Philippines</div>
                <div class="dpl-school-name">Pamantasan ng Lungsod ng Pasig</div>
            </div>
        </div>

        <p class="dpl-presnts">TO ALL PERSONS TO WHOM THESE PRESENTS:</p>
        <p class="dpl-script-line">Be it known, that the Board of Regents of this University, by authority granted by the Republic</p>
        <p class="dpl-script-line" style="margin-top:0;">of the Philippines and on the recommendation of the Academic Council, has conferred upon</p>

        <div class="dpl-name">{{ $fullName }}</div>

        <p class="dpl-script-line">who has fulfilled all the requirements for the degree of</p>
        <div class="dpl-degree">{{ $degreeLine }}</div>
        <p class="dpl-script-line">with all the rights, honors, and privileges as well as the obligations and responsibilities thereto appertaining.</p>
        <p class="dpl-script-line" style="margin-top:0;">In testimony thereof, the seal of the University and the signatures of the Chairman of the</p>
        <p class="dpl-script-line" style="margin-top:0;">Board of Regents, the University President, and the Registrar are hereunto affixed.</p>
        <p class="dpl-footer-line">Given in Pasig City, Philippines this {{ $formalIssueDate }}.</p>

        <div class="dpl-bottom dpl-bottom-copy2">
            <div class="dpl-left-copy">
                <div class="dpl-left-note">Certified text of the original:</div>
                <div class="dpl-left-name">FEDERICO C. NUEVA</div>
                <div class="dpl-left-role">University Registrar</div>
            </div>
            <div class="dpl-right-stack">
                <div class="dpl-signatures">
                    <div class="dpl-sign-item">
                        <div class="name"><span class="sgd">(Sgd.)</span> <span class="person">PROF. MARIANO L. CHING</span></div>
                        <div class="title">University Registrar</div>
                    </div>
                    <div class="dpl-sign-item">
                        <div class="name"><span class="sgd">(Sgd.)</span> <span class="person">AMB. ROSALINDA V. TIRONA</span></div>
                        <div class="title">University President</div>
                    </div>
                    <div class="dpl-sign-item">
                        <div class="name"><span class="sgd">(Sgd.)</span> <span class="person">HON. ROBERT C. EUSEBIO</span></div>
                        <div class="title">Chairman, Board of Regents</div>
                    </div>
                </div>
            </div>
        </div>
    </article>
</div>

<script>
document.addEventListener('keydown', function (event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 'p') {
        event.preventDefault();
        window.print();
    }
});
</script>
</body>
</html>
