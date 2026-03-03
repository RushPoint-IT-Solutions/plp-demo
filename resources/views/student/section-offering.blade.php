<!-- resources/views/student/section-offering.blade.php -->
@extends('layouts.student')

@section('title', 'Section Offering - PLP')

@section('page-title', 'SECTION OFFERING')

@section('content')
<div class="form-section-container">
    
    <!-- Filter Section (hidden after Download COR) -->
    <div id="filter-section">

    <!-- Row 1: Selected Semester + View COR Button -->
    <div class="form-row">
        
        <!-- Selected Semester -->
        <div class="form-group-grow">
            <label class="form-label-plp">SELECTED SEMESTER</label>
            <select class="form-input-long">
                <option value="" disabled selected>Select Semester</option>
                @foreach($semesters as $semester)
                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                @endforeach
            </select>
        </div>
        
        <!-- View COR / Download COR Button -->
        <div class="form-group">
            <span class="label-spacer"></span>
            <button id="cor-action-btn" class="btn-view-cor">View COR</button>
        </div>
    </div>

    <!-- Row 2: Course + Selected Yr & Block -->
    <div class="form-row">
        
        <!-- Course -->
        <div class="form-group-grow">
            <label class="form-label-plp">COURSE</label>
            <select class="form-input-long">
                <option value="" disabled selected>Select Course</option>
                @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->code }}</option>
                @endforeach
            </select>
        </div>
        
        <!-- Selected Yr & Block -->
        <div class="form-group">
            <label class="form-label-plp">SELECTED YR & BLOCK</label>
            <select class="form-input-short">
                <option value="" disabled selected>Select your block</option>
                @foreach($yearBlocks as $block)
                <option value="{{ $block->id }}">{{ $block->label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    </div>{{-- /#filter-section --}}

    <!-- COR Table (Hidden by Default) -->
    <div class="cor-scroll-wrapper">
    <div id="cor-table" class="cor-container mt-5" style="display: none;">
        
        <!-- COR Header -->
        <div class="cor-header">
            <div class="cor-header-left">
                <img src="{{ asset('img/plplogo.png') }}" alt="PLP Logo" class="cor-logo">
                
            </div>
            <div class="cor-header-center">
                <h1 class="cor-title">CERTIFICATE OF REGISTRATION</h1>
            </div>
            <div class="cor-header-right">
                <p class="cor-label">Registration No:</p>
                <p class="cor-reg-number">{{ optional($student)->registration_no }}</p>
            </div>
        </div>

        <!-- Student Information -->
        <div class="cor-student-info">
            <div class="cor-info-row">
                <div class="cor-info-group">
                    <span class="cor-info-label">Student No.:</span>
                    <span class="cor-info-value">{{ optional($student)->student_no }}</span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">College:</span>
                    <span class="cor-info-value">{{ optional($student)->college }}</span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">School Year:</span>
                    <span class="cor-info-value">{{ optional($student)->school_year_label }}</span>
                </div>
            </div>
            <div class="cor-info-row">
                <div class="cor-info-group">
                    <span class="cor-info-label">Name:</span>
                    <span class="cor-info-value">{{ optional($student)->name }}</span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">Program:</span>
                    <span class="cor-info-value">{{ optional($student)->program }}</span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">Curriculum:</span>
                    <span class="cor-info-value">{{ optional($student)->curriculum }}</span>
                </div>
            </div>
            <div class="cor-info-row">
                <div class="cor-info-group">
                    <span class="cor-info-label">Sex:</span>
                    <span class="cor-info-value">{{ optional($student)->sex }}</span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">Major:</span>
                    <span class="cor-info-value"></span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">Scholarship:</span>
                    <span class="cor-info-value">{{ optional($student)->scholarship }}</span>
                </div>
            </div>
            <div class="cor-info-row">
                <div class="cor-info-group">
                    <span class="cor-info-label">Age:</span>
                    <span class="cor-info-value">{{ optional($student)->age }}</span>
                </div>
                <div class="cor-info-group">
                    <span class="cor-info-label">Year Level:</span>
                    <span class="cor-info-value">{{ optional($student)->year_level }}</span>
                </div>
                <div class="cor-info-group"></div>
            </div>
        </div>

        <!-- Schedule Header -->
        <div class="cor-schedule-header">
            <h2 class="cor-schedule-title">SCHEDULE</h2>
        </div>

        <!-- Schedule Table -->
        <table class="cor-table">
            <thead>
                <tr>
                    <th class="cor-th">Code</th>
                    <th class="cor-th">Subject</th>
                    <th class="cor-th">Units</th>
                    <th class="cor-th">Days</th>
                    <th class="cor-th">Time</th>
                    <th class="cor-th">Room</th>
                    <th class="cor-th">Faculty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td class="cor-td">{{ $subject->code }}</td>
                    <td class="cor-td">{{ $subject->name }}</td>
                    <td class="cor-td">{{ number_format($subject->units, 1) }}</td>
                    <td class="cor-td">{{ $subject->days }}</td>
                    <td class="cor-td">{{ $subject->time_range }}</td>
                    <td class="cor-td">{{ $subject->room }}</td>
                    <td class="cor-td">{{ $subject->faculty }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="cor-totals">
            <p class="cor-totals-text">Totals: &nbsp; Subjects = <strong>{{ $subjects->count() }}</strong> &nbsp; Credit Units = <strong>{{ number_format($subjects->sum('units'), 1) }}</strong></p>
        </div>

        <!-- Note Bar -->
        <div class="cor-note-bar">
            <p class="cor-note-text">Note: Invalid Without the Registrar's Signature</p>
        </div>

        <!-- Signatures -->
        <div class="cor-signatures">
            <div class="cor-signature-left">
                <p class="cor-signature-name">{{ strtoupper(optional($student)->name ?? '') }}</p>
                <p class="cor-signature-role">Student's Signature</p>
            </div>
            <div class="cor-signature-right">
                <p class="cor-signature-name">MARIA CONSUELO</p>
                <p class="cor-signature-role">College Registrar</p>
            </div>
        </div>

    </div>
    </div>{{-- /.cor-scroll-wrapper --}}

</div>
@endsection
