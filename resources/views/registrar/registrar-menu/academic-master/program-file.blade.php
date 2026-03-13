@extends('layouts.registrar')

@section('title', 'PLP - Program File')
@section('page-title', 'PROGRAM FILE')

@section('content')
<div class="pf-page" id="programFilePage" data-success="{{ session('program_file_success', '') }}" data-open-setup="{{ $errors->any() ? '1' : '0' }}">
    <div class="pf-toolbar">
        <div class="pf-toolbar-actions">
            <button type="button" class="pf-btn-new" onclick="openSetupDepartmentsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                Setup Department
            </button>
            <button type="button" class="pf-btn-new" onclick="openNewProgramModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Program
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('registrar.registrar-menu.academic-master.program-file') }}" class="pf-top-filter" id="pfTopFilterForm">
        <div class="pf-top-filter-grid">
            <div class="pf-top-field">
                <label class="pf-top-label" for="filterDepartment">Department</label>
                <select name="department_id" id="filterDepartment" class="pf-modal-select">
                    <option value="">-All Group-</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string)$filters['department_id'] === (string)$department->id ? 'selected' : '' }}>
                            {{ $department->description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pf-top-field">
                <label class="pf-top-label" for="filterProgramType">Program Type</label>
                <select name="program_type" id="filterProgramType" class="pf-modal-select">
                    <option value="">-Select Type-</option>
                    <option value="Degree" {{ $filters['program_type'] === 'Degree' ? 'selected' : '' }}>Degree</option>
                    <option value="Diploma" {{ $filters['program_type'] === 'Diploma' ? 'selected' : '' }}>Diploma</option>
                    <option value="Certificate" {{ $filters['program_type'] === 'Certificate' ? 'selected' : '' }}>Certificate</option>
                </select>
            </div>

            <div class="pf-top-field">
                <label class="pf-top-label" for="filterProgramCode">Program Code</label>
                <input type="text" id="filterProgramCode" name="program_code" class="pf-modal-input" value="{{ $filters['program_code'] }}">
            </div>

            <div class="pf-top-field">
                <label class="pf-top-label" for="filterDescription">Description</label>
                <input type="text" id="filterDescription" name="description" class="pf-modal-input" value="{{ $filters['description'] }}">
            </div>

            <div class="pf-top-search-btn-wrap">
                <button type="submit" class="pf-btn-new pf-top-search-btn">Search</button>
            </div>
        </div>
    </form>

    <div class="pf-table-controls">
        <div class="pf-entries-control">
            <label for="pfEntriesLimit">Show Entries</label>
            <select id="pfEntriesLimit" class="pf-entries-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100" selected>100</option>
            </select>
        </div>
    </div>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="pfTable">
            <thead>
                <tr>
                    <th>Program Code</th>
                    <th>Program Name</th>
                    <th>Department</th>
                    <th>Program Type</th>
                    <th>Slots</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr>
                        <td>{{ $program->code }}</td>
                        <td>{{ $program->description ?: $program->name }}</td>
                        <td>{{ optional($program->department)->description ?: '-' }}</td>
                        <td>{{ $program->program_type ?: '-' }}</td>
                        <td>{{ (int) $program->slots }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center pf-empty-row" style="padding: 18px; color: #888;">No programs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pf-pagination">
        <span class="pf-page-info">Showing {{ $programs->count() }} program(s)</span>
    </div>
</div>

<div class="pf-modal-overlay" id="setupDepartmentsModal" style="display:none;">
    <div class="pf-modal-box pf-dept-modal-box">
        <div class="pf-modal-title pf-dept-modal-title">Setup Department</div>

        <form method="POST" action="{{ route('registrar.registrar-menu.academic-master.program-file.setup') }}" id="setupDepartmentsForm">
            @csrf

            @if($errors->any())
                <div class="pf-form-error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="pf-dept-form-grid">
                <div class="pf-modal-field pf-item-program-type">
                    <label class="pf-modal-label" for="setupProgramType">Program Type</label>
                    <select class="pf-modal-select" id="setupProgramType" name="program_type" required>
                        <option value="">-Select Type-</option>
                        <option value="Degree" {{ old('program_type') === 'Degree' ? 'selected' : '' }}>Degree</option>
                        <option value="Diploma" {{ old('program_type') === 'Diploma' ? 'selected' : '' }}>Diploma</option>
                        <option value="Certificate" {{ old('program_type') === 'Certificate' ? 'selected' : '' }}>Certificate</option>
                    </select>
                </div>

                <div class="pf-modal-field pf-item-program-code">
                    <label class="pf-modal-label" for="setupProgramCode">Program Code</label>
                    <input type="text" class="pf-modal-input" id="setupProgramCode" name="program_code" value="{{ old('program_code') }}" required>
                </div>

                <div class="pf-modal-field pf-item-department">
                    <label class="pf-modal-label" for="setupDepartment">Department</label>
                    <select class="pf-modal-select" id="setupDepartment" name="department_id" required>
                        <option value="">-Select Department-</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string)old('department_id') === (string)$department->id ? 'selected' : '' }}>
                                {{ $department->description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-modal-field pf-item-description">
                    <label class="pf-modal-label" for="setupDescription">Description</label>
                    <input type="text" class="pf-modal-input" id="setupDescription" name="description" value="{{ old('description') }}" required>
                </div>

                <div class="pf-modal-field pf-item-slots">
                    <label class="pf-modal-label" for="setupSlots">Slots</label>
                    <input type="number" class="pf-modal-input" id="setupSlots" name="slots" min="0" value="{{ old('slots', 0) }}">
                </div>

                <div class="pf-modal-field pf-item-track">
                    <label class="pf-modal-label">Track Category for SHS</label>
                    <div class="pf-track-options">
                        <label class="pf-track-option"><input type="radio" name="track_category" value="Academic" {{ old('track_category') === 'Academic' ? 'checked' : '' }}> Academic</label>
                        <label class="pf-track-option"><input type="radio" name="track_category" value="TVL" {{ old('track_category') === 'TVL' ? 'checked' : '' }}> TVL</label>
                        <label class="pf-track-option"><input type="radio" name="track_category" value="Academic/TVL" {{ old('track_category') === 'Academic/TVL' ? 'checked' : '' }}> Academic/TVL</label>
                        <label class="pf-track-option pf-track-option-check"><input type="checkbox" name="non_filipino" value="1" {{ old('non_filipino') ? 'checked' : '' }}> Non-Filipino</label>
                    </div>
                </div>

                <div class="pf-modal-field pf-item-dean">
                    <label class="pf-modal-label" for="setupDeanDirector">Dean / Director</label>
                    <select class="pf-modal-select" id="setupDeanDirector" name="dean_director_id">
                        <option value="">-Select Faculty-</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}" {{ (string)old('dean_director_id') === (string)$faculty->id ? 'selected' : '' }}>
                                {{ $faculty->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pf-modal-actions pf-dept-modal-actions">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeSetupDepartmentsModal()">Cancel</button>
                <button type="submit" class="pf-modal-btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="pf-modal-overlay" id="newProgramModal" style="display:none;">
    <div class="pf-modal-box pf-new-modal-box">
        <div class="pf-modal-title pf-new-modal-title">Add New Program</div>

        <form id="newProgramForm" onsubmit="return handleNewProgramSave(event)">
            <div class="pf-new-form-grid">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramCode">Program Code</label>
                    <input type="text" class="pf-modal-input" id="newProgramCode" placeholder="Code" required>
                </div>

                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramName">Program Name:</label>
                    <input type="text" class="pf-modal-input" id="newProgramName" placeholder="Name" required>
                </div>

                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramDepartment">Select Department</label>
                    <select class="pf-modal-select" id="newProgramDepartment" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramAccreditation">Accreditation Level</label>
                    <select class="pf-modal-select" id="newProgramAccreditation" required>
                        <option value="">Select Level</option>
                        <option value="Level I Accredited">Level I Accredited</option>
                        <option value="Level II Accredited">Level II Accredited</option>
                        <option value="Level III Accredited">Level III Accredited</option>
                        <option value="Level IV Accredited">Level IV Accredited</option>
                        <option value="Pending Review">Pending Review</option>
                    </select>
                </div>
            </div>

            <div class="pf-modal-actions pf-new-modal-actions">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeNewProgramModal()">Cancel</button>
                <button type="submit" class="pf-modal-btn-save">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
