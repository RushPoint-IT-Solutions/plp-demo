@extends('layouts.registrar')

@section('title', 'PLP - ' . ($pageTitle ?? 'Dean\'s Honors Certificate'))
@section('page-title', $pageTitle ?? 'Dean\'s Honors Certificate')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/deans-honors.css') }}?v={{ time() }}">
@endpush

@section('content')
@php
    $studentModel = isset($student) ? $student : null;
    $certificateAwardTitle = $awardTitle ?? "Dean's Honors List Award";

    $studentName = trim((string) optional($studentModel)->name);
    if (strpos($studentName, ',') !== false) {
        $nameParts = array_map('trim', explode(',', $studentName, 2));
        $studentName = trim(($nameParts[1] ?? '') . ' ' . ($nameParts[0] ?? ''));
    }
    $studentName = $studentName !== '' ? strtoupper($studentName) : 'STUDENT NAME';

    $programText = trim((string) optional($studentModel)->program);
    if ($programText === '' && $studentModel && $studentModel->relationLoaded('canonicalCourse')) {
        $programText = trim((string) (optional($studentModel->canonicalCourse)->name ?: optional($studentModel->canonicalCourse)->code));
    }
    $programText = $programText !== '' ? $programText : 'PROGRAM';

    $semesterText = trim((string) optional($studentModel)->semester);
    if ($semesterText === '' && $studentModel && $studentModel->relationLoaded('academicTerm')) {
        $semesterText = trim((string) optional($studentModel->academicTerm)->term);
    }
    $semesterText = $semesterText !== '' ? $semesterText : '2nd Semester';
    $semesterText = str_ireplace(
        ['Second Semester', 'First Semester'],
        ['2nd Semester', '1st semester'],
        $semesterText
    );

    $academicYearText = trim((string) optional($studentModel)->school_year);
    if ($academicYearText === '' && $studentModel && $studentModel->relationLoaded('academicTerm')) {
        $academicYearText = trim((string) optional($studentModel->academicTerm)->school_year);
    }
    $academicYearText = $academicYearText !== '' ? $academicYearText : '2024-2025';

    try {
        $awardDate = !empty($issuedDate) ? \Carbon\Carbon::parse($issuedDate) : now();
    } catch (\Exception $exception) {
        $awardDate = now();
    }

    $awardDay = (int) $awardDate->format('j');
    $awardSuffix = $awardDate->format('S');
    $awardMonthYear = $awardDate->format('F Y');
@endphp

<div class="pf-page">
    <div class="certificate-print-actions d-print-none">
        <button type="button" class="req-btn-save" onclick="window.print()">Print Certificate</button>
    </div>

    <article class="deans-honors" aria-label="{{ $certificateAwardTitle }}">
        <img class="deans-honors__seal" src="{{ asset('img/logobg.png') }}" alt="PLP Seal">

        <header class="deans-honors__header">
            <p>Republic of the Philippines</p>
            <p>City Government of Pasig</p>
            <h2>PAMANTASAN NG LUNGSOD NG PASIG</h2>
        </header>

        <section class="deans-honors__body">
            <p class="deans-honors__kicker">Award this</p>
            <h1>{{ $certificateAwardTitle }}</h1>
            <p class="deans-honors__recognition">
                In recognition of the exemplary academic achievement<br>
                for the <span>{{ $semesterText }}</span> of Academic Year <span>{{ $academicYearText }}</span>
            </p>
            <p class="deans-honors__of">of</p>
            <p class="deans-honors__student">{{ $studentName }}</p>
        </section>

        <footer class="deans-honors__footer">
            <div class="deans-honors__registrar">
                <img class="deans-honors__signature" src="{{ asset('img/deans-honors-signature.png') }}" alt="Registrar Signature">
                <strong>FEDERICO G. NUEVA, MT</strong>
                <span>University Registrar</span>
            </div>
        </footer>
    </article>
</div>
@endsection
