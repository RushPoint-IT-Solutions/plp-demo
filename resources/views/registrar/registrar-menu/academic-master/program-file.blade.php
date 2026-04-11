@extends('layouts.registrar')

@section('title', 'PLP - Program File')
@section('page-title', 'PROGRAM FILE')

@section('content')
<div class="pf-page" id="programFilePage"
     data-success="{{ session('program_file_success', '') }}"
     data-open-setup="{{ ($errors->has('dept_code') || $errors->has('dept_description')) ? '1' : '0' }}"
    data-open-new="{{ ($errors->has('program_code') || $errors->has('program_name') || $errors->has('department_id') || $errors->has('accreditation_level')) ? '1' : '0' }}"
    data-current-page="{{ (int) $programs->currentPage() }}"
    data-last-page="{{ (int) $programs->lastPage() }}">
    <div class="pf-toolbar">
        <div class="pf-toolbar-actions">
            <button type="button" class="pf-btn-new" onclick="openSetupDepartmentsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                Setup Department
            </button>
            <button type="button" class="pf-btn-new" onclick="openNewProgramModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Program
            </button>
        </div>
    </div>

    <form method="GET" action="{{ route('registrar.registrar-menu.academic-master.program-file') }}" class="pf-top-filter" id="pfTopFilterForm">
        <input type="hidden" name="page" id="pfPageInput" value="{{ (int) $programs->currentPage() }}">
        <input type="hidden" name="per_page" id="pfPerPage" value="25">
        <div class="pf-top-filter-grid">
            <div class="pf-top-field">
                <label class="pf-top-label" for="filterDepartment">Department</label>
                <select name="department_id" id="filterDepartment" class="pf-modal-select plp-select" data-plp-select>
                    <option value="">-All Group-</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string)$filters['department_id'] === (string)$department->id ? 'selected' : '' }}>
                            {{ $department->description }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pf-top-field">
                <label class="pf-top-label" for="filterProgramCode">Program Code</label>
                <input type="text" id="filterProgramCode" name="program_code" class="pf-modal-input" value="{{ $filters['program_code'] }}">
            </div>

            <div class="pf-top-field">
                <label class="pf-top-label" for="filterDescription">Program Name</label>
                <input type="text" id="filterDescription" name="description" class="pf-modal-input" value="{{ $filters['description'] }}">
            </div>

            <div class="pf-top-field">
                <label class="pf-top-label" for="filterProgramType">Accreditation</label>
                <select name="program_type" id="filterProgramType" class="pf-modal-select plp-select" data-plp-select>
                    <option value="">-All Levels-</option>
                    <option value="Level I Accredited" {{ $filters['program_type'] === 'Level I Accredited' ? 'selected' : '' }}>Level I Accredited</option>
                    <option value="Level II Accredited" {{ $filters['program_type'] === 'Level II Accredited' ? 'selected' : '' }}>Level II Accredited</option>
                    <option value="Level III Accredited" {{ $filters['program_type'] === 'Level III Accredited' ? 'selected' : '' }}>Level III Accredited</option>
                    <option value="Level IV Accredited" {{ $filters['program_type'] === 'Level IV Accredited' ? 'selected' : '' }}>Level IV Accredited</option>
                    <option value="Pending Review" {{ $filters['program_type'] === 'Pending Review' ? 'selected' : '' }}>Pending Review</option>
                </select>
            </div>

            <div class="pf-top-search-btn-wrap">
                <button type="submit" class="pf-btn-new pf-top-search-btn">Search</button>
            </div>
        </div>
    </form>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" id="pfTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Program Code</th>
                    <th>Program Name</th>
                    <th>Department</th>
                    <th>Accreditation Level</th>
                    <th class="pf-actions-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                    <tr>
                        <td>{{ $program->code }}</td>
                        <td>{{ $program->name ?: $program->description }}</td>
                        <td>{{ optional($program->department)->description ?: '-' }}</td>
                        <td>{{ $program->program_file ?: 'Pending Review' }}</td>
                        <td class="pf-actions-cell">
                            <button type="button"
                                class="apst-action-btn"
                                data-pf-menu-toggle="pfMenu-{{ $program->id }}"
                                data-update-url="{{ route('registrar.registrar-menu.academic-master.program-file.setup.update', $program) }}"
                                data-delete-url="{{ route('registrar.registrar-menu.academic-master.program-file.setup.delete', $program) }}"
                                data-code="{{ $program->code }}"
                                data-name="{{ $program->name ?: $program->description }}"
                                data-department-id="{{ $program->department_id }}"
                                data-accreditation="{{ $program->program_file ?: 'Pending Review' }}"
                                aria-label="Open row actions"
                                title="Actions"><span></span><span></span><span></span></button>
                            <div class="apst-dropdown" id="pfMenu-{{ $program->id }}">
                                <button type="button" data-pf-action="edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn" data-pf-action="delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center pf-empty-row">No programs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @php
        $pfCurrentPage = (int) $programs->currentPage();
        $pfLastPage = max((int) $programs->lastPage(), 1);
        $pfStartPage = max(1, $pfCurrentPage - 2);
        $pfEndPage = min($pfLastPage, $pfCurrentPage + 2);

        if ($pfCurrentPage <= 3) {
            $pfEndPage = min($pfLastPage, 5);
        } elseif ($pfCurrentPage >= ($pfLastPage - 2)) {
            $pfStartPage = max(1, $pfLastPage - 4);
        }
    @endphp

    <div class="sf-pagination-bar sf-pagination-compact" id="pfPaginationBar">
        <div class="rtp-pagination plp-pagination">
            <nav class="rtp-nav plp-pagination__nav" aria-label="Program File pagination">
                <div class="rtp-list plp-pagination__list" role="group" aria-label="Page controls">
                    <button
                        type="button"
                        class="rtp-page-btn plp-pagination__btn"
                        id="pfPrevBtn"
                        data-pf-page="{{ $pfCurrentPage - 1 }}"
                        aria-label="Previous page"
                        {{ $pfCurrentPage <= 1 ? 'disabled' : '' }}
                    >&lt;</button>
                    <div class="rtp-pages plp-pagination__pages" id="pfPageNumbers">
                        @for($pfPage = $pfStartPage; $pfPage <= $pfEndPage; $pfPage++)
                            <button
                                type="button"
                                class="rtp-page-num plp-pagination__page {{ $pfPage === $pfCurrentPage ? 'active is-active' : '' }}"
                                data-pf-page="{{ $pfPage }}"
                                aria-label="Go to page {{ $pfPage }}"
                            >{{ $pfPage }}</button>
                        @endfor
                    </div>
                    <button
                        type="button"
                        class="rtp-page-btn plp-pagination__btn"
                        id="pfNextBtn"
                        data-pf-page="{{ $pfCurrentPage + 1 }}"
                        aria-label="Next page"
                        {{ $pfCurrentPage >= $pfLastPage ? 'disabled' : '' }}
                    >&gt;</button>
                </div>
            </nav>
        </div>
    </div>

</div>

<div class="pf-modal-overlay" id="setupDepartmentsModal" style="display:none;">
    <div class="pf-modal-box pf-dept-modal-box">
        <div class="pf-modal-title pf-dept-modal-title">Setup Department</div>

        <form method="POST" action="{{ route('registrar.registrar-menu.academic-master.program-file.department.store') }}" id="setupDepartmentsForm">
            @csrf

            @if($errors->has('dept_code') || $errors->has('dept_description'))
                <div class="pf-form-error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="pf-dept-setup-form-row">
                <div class="pf-modal-field pf-item-dept-code">
                    <label class="pf-modal-label" for="setupDepartmentCode">Dept. Code</label>
                    <input type="text" class="pf-modal-input" id="setupDepartmentCode" name="dept_code" value="{{ old('dept_code') }}" placeholder="Code" required>
                </div>

                <div class="pf-modal-field pf-item-dept-description">
                    <label class="pf-modal-label" for="setupDepartmentDescription">Dept. Description</label>
                    <input type="text" class="pf-modal-input" id="setupDepartmentDescription" name="dept_description" value="{{ old('dept_description') }}" placeholder="Description" required>
                </div>

                <div class="pf-dept-plus-wrap">
                    <button type="submit" class="pf-dept-plus-btn" aria-label="Add department" title="Add department">+</button>
                </div>
            </div>

            <div class="pf-dept-list-wrap">
                <div class="pf-dept-list-title">List of Departments:</div>
                <div class="pf-dept-list-head">
                    <span>Dept. Code</span>
                    <span>Dept. Description</span>
                </div>

                @foreach($departments as $department)
                    <div class="pf-dept-list-row">
                        <input type="text" class="pf-modal-input pf-dept-list-input" value="{{ $department->code }}" readonly>
                        <input type="text" class="pf-modal-input pf-dept-list-input" value="{{ $department->description }}" readonly>
                    </div>
                @endforeach
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

        <form method="POST" action="{{ route('registrar.registrar-menu.academic-master.program-file.setup') }}" id="newProgramForm">
            @csrf

            @if($errors->has('program_code') || $errors->has('program_name') || $errors->has('department_id') || $errors->has('accreditation_level'))
                <div class="pf-form-error-box">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="pf-new-form-grid">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramCode">Program Code</label>
                    <input type="text" class="pf-modal-input" id="newProgramCode" name="program_code" value="{{ old('program_code') }}" placeholder="Code" required>
                </div>

                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramName">Program Name</label>
                    <input type="text" class="pf-modal-input" id="newProgramName" name="program_name" value="{{ old('program_name') }}" placeholder="Name" required>
                </div>

                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramDepartment">Select Department</label>
                    <select class="pf-modal-select plp-select" id="newProgramDepartment" name="department_id" data-plp-select required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string)old('department_id') === (string)$department->id ? 'selected' : '' }}>{{ $department->description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="newProgramAccreditation">Accreditation Level</label>
                    <select class="pf-modal-select plp-select" id="newProgramAccreditation" name="accreditation_level" data-plp-select required>
                        <option value="">Select Level</option>
                        <option value="Level I Accredited" {{ old('accreditation_level') === 'Level I Accredited' ? 'selected' : '' }}>Level I Accredited</option>
                        <option value="Level II Accredited" {{ old('accreditation_level') === 'Level II Accredited' ? 'selected' : '' }}>Level II Accredited</option>
                        <option value="Level III Accredited" {{ old('accreditation_level') === 'Level III Accredited' ? 'selected' : '' }}>Level III Accredited</option>
                        <option value="Level IV Accredited" {{ old('accreditation_level') === 'Level IV Accredited' ? 'selected' : '' }}>Level IV Accredited</option>
                        <option value="Pending Review" {{ old('accreditation_level') === 'Pending Review' ? 'selected' : '' }}>Pending Review</option>
                    </select>
                </div>
            </div>

            <input type="hidden" name="program_type" value="Degree">

            <div class="pf-modal-actions pf-new-modal-actions">
                <button type="button" class="pf-modal-btn-cancel" onclick="closeNewProgramModal()">Cancel</button>
                <button type="submit" class="pf-modal-btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="pf-modal-overlay" id="pfEditProgramModal" style="display:none;">
    <div class="pf-modal-box pf-dept-modal-box">
        <div class="pf-modal-title pf-dept-modal-title">Edit Program</div>

        <form method="POST" action="" id="pfEditProgramForm">
            @csrf
            @method('PUT')

            <div class="pf-dept-form-grid">
                <div class="pf-modal-field pf-item-program-code">
                    <label class="pf-modal-label" for="pfEditProgramCode">Program Code</label>
                    <input type="text" class="pf-modal-input" id="pfEditProgramCode" name="program_code" required>
                </div>

                <div class="pf-modal-field pf-item-program-name">
                    <label class="pf-modal-label" for="pfEditProgramName">Program Name</label>
                    <input type="text" class="pf-modal-input" id="pfEditProgramName" name="program_name" required>
                </div>

                <div class="pf-modal-field pf-item-department">
                    <label class="pf-modal-label" for="pfEditDepartment">Department</label>
                    <select class="pf-modal-select plp-select" id="pfEditDepartment" name="department_id" data-plp-select required>
                        <option value="">-Select Department-</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->description }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pf-modal-field pf-item-accreditation">
                    <label class="pf-modal-label" for="pfEditAccreditation">Accreditation Level</label>
                    <select class="pf-modal-select plp-select" id="pfEditAccreditation" name="accreditation_level" data-plp-select required>
                        <option value="">Select Level</option>
                        <option value="Level I Accredited">Level I Accredited</option>
                        <option value="Level II Accredited">Level II Accredited</option>
                        <option value="Level III Accredited">Level III Accredited</option>
                        <option value="Level IV Accredited">Level IV Accredited</option>
                        <option value="Pending Review">Pending Review</option>
                    </select>
                </div>
            </div>

            <div class="pf-modal-actions pf-dept-modal-actions">
                <button type="button" class="pf-modal-btn-cancel" onclick="closePfEditProgramModal()">Cancel</button>
                <button type="submit" class="pf-modal-btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="pf-modal-overlay" id="pfDeleteProgramModal" style="display:none;">
    <div class="pf-modal-box" style="max-width:430px;">
        <div class="pf-modal-title" style="color:#b42318;">Delete Program</div>
        <p id="pfDeleteProgramText" style="font-size:0.9rem; color:#444; margin:10px 0 20px; text-align:center;">Are you sure you want to delete this program?</p>
        <form method="POST" action="" id="pfDeleteProgramForm">
            @csrf
            @method('DELETE')
            <div class="pf-modal-actions">
                <button type="button" class="pf-modal-btn-cancel" onclick="closePfDeleteProgramModal()">Cancel</button>
                <button type="submit" class="pf-modal-btn-save" style="background:#b42318;">Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection
