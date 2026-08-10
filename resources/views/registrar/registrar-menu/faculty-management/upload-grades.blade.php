@extends('layouts.registrar')

@section('title', 'PLP - Upload Grades')
@section('page-title', 'UPLOAD GRADES')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.min.css">
<style>
    .ug-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    @media (max-width: 900px) {
        .ug-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 520px) {
        .ug-stats-grid { grid-template-columns: 1fr; }
    }
    .ug-stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 18px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .ug-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        flex-shrink: 0;
        font-size: 1.1rem;
    }
    .ug-stat-card-total .ug-stat-icon { background: #eff6ff; color: #1d4ed8; }
    .ug-stat-card-graded .ug-stat-icon { background: #f0fdf4; color: #15803d; }
    .ug-stat-card-incomplete .ug-stat-icon { background: #fffbeb; color: #b45309; }
    .ug-stat-card-submitted .ug-stat-icon { background: #faf5ff; color: #7e22ce; }
    .ug-stat-number {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }
    .ug-stat-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-top: 2px;
    }
    .ug-picker-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: flex-end;
        margin-bottom: 16px;
    }
    .ug-picker-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 220px;
    }
    .ug-picker-group label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
    }
    .ug-picker-group select {
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.85rem;
    }
    .ug-picker-group .chosen-container { width: 100% !important; font-size: 0.85rem; }
    .ug-picker-group .chosen-container-single .chosen-single {
        height: 36px;
        line-height: 34px;
        padding: 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        box-shadow: none;
        background: #fff;
    }
    .ug-picker-group .chosen-container-single .chosen-single div b {
        background-position: 0 4px;
    }
    .ug-picker-group .chosen-container-active.chosen-with-drop .chosen-single {
        border-color: #15803d;
    }
    .ug-picker-group .chosen-container .chosen-results li.highlighted {
        background-color: #15803d;
    }
    .ug-picker-group .chosen-container-single .chosen-search input[type="text"] {
        border: 1px solid #cbd5e1;
        border-radius: 4px;
    }
    .ug-selected-banner {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 18px;
    }
    .ug-selected-banner strong { color: #1d4ed8; }
    .ug-change-link {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1d4ed8;
        cursor: pointer;
        background: none;
        border: none;
    }
    .ug-panel { display: none; }
    .ug-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin: 14px 0;
    }
    .ug-template-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 6px;
        border: 1px solid #15803d;
        color: #15803d;
        background: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
    }
    .ug-template-btn:hover { background: #f0fdf4; color: #15803d; text-decoration: none; }
    .ug-submit-btn {
        padding: 9px 16px;
        border-radius: 6px;
        border: none;
        background: #15803d;
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
    }
    .ug-submit-btn:hover { opacity: 0.9; }
    .ug-select-btn {
        padding: 5px 12px;
        border-radius: 5px;
        border: none;
        background: #15803d;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="student-page-container">
    <div class="batch-upload-wrapper">

        {{-- Summary dashboard --}}
        <div class="ug-stats-grid">
            <div class="ug-stat-card ug-stat-card-total">
                <div class="ug-stat-icon"><i class="ri-file-list-3-line"></i></div>
                <div>
                    <div class="ug-stat-number">{{ $stats['total'] }}</div>
                    <div class="ug-stat-label">Total Sections</div>
                </div>
            </div>
            <div class="ug-stat-card ug-stat-card-graded">
                <div class="ug-stat-icon"><i class="ri-checkbox-circle-line"></i></div>
                <div>
                    <div class="ug-stat-number">{{ $stats['fullyGraded'] }}</div>
                    <div class="ug-stat-label">Fully Graded</div>
                </div>
            </div>
            <div class="ug-stat-card ug-stat-card-incomplete">
                <div class="ug-stat-icon"><i class="ri-time-line"></i></div>
                <div>
                    <div class="ug-stat-number">{{ $stats['incomplete'] }}</div>
                    <div class="ug-stat-label">Incomplete</div>
                </div>
            </div>
            <div class="ug-stat-card ug-stat-card-submitted">
                <div class="ug-stat-icon"><i class="ri-send-plane-line"></i></div>
                <div>
                    <div class="ug-stat-number">{{ $stats['submitted'] }}</div>
                    <div class="ug-stat-label">Submitted for Review</div>
                </div>
            </div>
        </div>

        {{-- Section & Course picker --}}
        <div id="ugPicker">
            <div class="ug-picker-row">
                <div class="ug-picker-group">
                    <label for="ugCourse">Course</label>
                    <select id="ugCourse" class="ug-chosen">
                        <option value="">All Courses</option>
                    </select>
                </div>
                <div class="ug-picker-group">
                    <label for="ugSection">Section</label>
                    <select id="ugSection" class="ug-chosen">
                        <option value="">All Sections</option>
                    </select>
                </div>
                <div class="ug-picker-group">
                    <label for="ugFaculty">Faculty</label>
                    <select id="ugFaculty" class="ug-chosen">
                        <option value="">All Faculty</option>
                    </select>
                </div>
                <div class="ug-picker-group">
                    <label for="ugStatus">Status</label>
                    <select id="ugStatus" class="ug-chosen">
                        <option value="">All Statuses</option>
                    </select>
                </div>
                <div class="ug-picker-group" style="flex:1 1 240px;">
                    <label for="ugSearch">Search</label>
                    <input type="text" id="ugSearch" placeholder="Search section, course code, description, faculty..." style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:0.85rem;">
                </div>
            </div>

            <div class="student-table-wrapper table-responsive">
                <table class="student-table registrar-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Section</th>
                            <th>Course Code</th>
                            <th>Description</th>
                            <th>Faculty</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="ugPickerBody"></tbody>
                </table>
            </div>
        </div>

        {{-- Selected subject banner --}}
        <div class="ug-selected-banner" id="ugSelectedBanner">
            <div>Uploading grades for <strong id="ugSelectedLabel"></strong></div>
            <button type="button" class="ug-change-link" id="ugChangeBtn">Change Section / Course</button>
        </div>

        {{-- Upload panel (shown once a subject is selected) --}}
        <div class="ug-panel" id="ugPanel">

            @if ($uploadReport && (int) ($uploadReport['subject_id'] ?? 0) === (int) $selectedSubjectId)
                @php
                    $updatedCount = count($uploadReport['updated']);
                    $skippedCount = count($uploadReport['skipped']);
                @endphp
                <div class="alert {{ $updatedCount > 0 ? 'alert-success' : 'alert-warning' }} batch-upload-alert" role="alert">
                    <strong>Upload Summary:</strong>
                    {{ $updatedCount }} row(s) updated, {{ $skippedCount }} skipped.
                    @if ($skippedCount > 0)
                        <ul class="batch-upload-report-list">
                            @foreach ($uploadReport['skipped'] as $skip)
                                <li>Row {{ $skip['row'] }}: {{ $skip['reason'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger batch-upload-alert" role="alert">
                    <strong>{{ $errors->has('submit') ? 'Cannot submit for review.' : 'Upload failed.' }}</strong>
                    <ul class="batch-upload-report-list">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="ugUploadForm" action="{{ route('registrar.registrar-menu.faculty-mgmt.upload-grades.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="subject_id" id="ugUploadSubjectId" value="{{ $selectedSubjectId ?: '' }}">

                <div class="batch-dropzone" id="ugDropzone">
                    <p class="dropzone-text">Drop a CSV file here or click to upload</p>
                    <input type="file" id="ugFileInput" name="file" accept=".csv,text/csv" hidden>
                </div>

                <div class="ug-actions-row">
                    <p class="batch-file-count" id="ugFileCount" style="margin:0;">No file selected.</p>
                    <button type="submit" id="ugUploadButton" class="batch-upload-btn" disabled>Upload Grades</button>
                    <a href="#" id="ugTemplateLink" class="ug-template-btn">Download CSV Template</a>
                </div>
            </form>

            <div class="batch-upload-note">
                <p><strong>Note:</strong></p>
                <p>CSV must include a <strong>student_no</strong> column, and at least one of <strong>midterm</strong> / <strong>final</strong>.</p>
                <p>Only students already enrolled in the selected section will be matched. Download the template above to get the exact student list.</p>
            </div>

            <div class="batch-upload-results">
                <h5 class="batch-upload-results-title">Current Grades</h5>
                <div class="student-table-wrapper table-responsive">
                    <table class="student-table registrar-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student No.</th>
                                <th>Name</th>
                                <th>Midterm</th>
                                <th>Final</th>
                            </tr>
                        </thead>
                        <tbody id="ugStudentsBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="ug-actions-row">
                <form id="ugSubmitForm" action="{{ route('registrar.registrar-menu.faculty-mgmt.upload-grades.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="subject_id" id="ugSubmitSubjectId" value="{{ $selectedSubjectId ?: '' }}">
                    <button type="button" id="ugSubmitBtn" class="ug-submit-btn">Submit for Dean Review</button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    window.UG_SUBJECTS = @json($uploadSubjects);
    window.UG_SELECTED_SUBJECT_ID = {{ (int) $selectedSubjectId }};
    window.UG_LIST_URL = "{{ route('registrar.registrar-menu.faculty-mgmt.upload-grades') }}";
    window.UG_TEMPLATE_URL_TPL = "{{ route('registrar.registrar-menu.faculty-mgmt.upload-grades.template', ['subject' => '__SUBJECT__']) }}";
</script>
<script src="https://cdn.jsdelivr.net/npm/chosen-js@1.8.7/chosen.jquery.min.js"></script>
<script src="{{ asset('js/upload-grades.js') }}?v={{ file_exists(public_path('js/upload-grades.js')) ? filemtime(public_path('js/upload-grades.js')) : time() }}"></script>
@endpush
