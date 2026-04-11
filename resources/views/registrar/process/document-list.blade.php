@extends('layouts.registrar')

@section('title', 'PLP - Document List')
@section('page-title', 'DOCUMENT LIST')

@section('content')
<div class="doclist-page" id="doclistPage">
    <div
        id="doclistConfig"
        data-store-url="{{ route('registrar.process.document-list.store') }}"
        data-update-url-template="{{ route('registrar.process.document-list.update', ['documentRequirement' => '__ID__']) }}"
        data-delete-url-template="{{ route('registrar.process.document-list.delete', ['documentRequirement' => '__ID__']) }}"
    ></div>

    <div class="doclist-filter-bar">
        <div class="doclist-filter-left">
            <div class="doclist-filter-group">
                <span class="app-filter-label">Search</span>
                <input type="text" class="app-filter-input doclist-filter-input" id="doclistSearch" placeholder="Search document...">
            </div>
            <div class="doclist-filter-group">
                <span class="app-filter-label">Sort By</span>
                <select class="app-filter-select doclist-filter-select" id="doclistSort">
                    <option value="asc">A - Z</option>
                    <option value="desc">Z - A</option>
                </select>
            </div>
        </div>
        <div class="doclist-filter-right">
            <button type="button" class="doclist-add-btn" id="doclistAddBtn">+ Add Requirements</button>
        </div>
    </div>

    <div class="student-table-wrapper table-responsive">
        <table id="doclistTable" class="student-table registrar-table doclist-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Year Level</th>
                    <th>Document/ Requirements</th>
                    <th>Type</th>
                    <th>Non Filipino</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="doclistTableBody">
                @forelse ($requirements as $requirement)
                    @php($menuId = 'docMenu' . $requirement['id'])
                    <tr
                        data-row-id="{{ $requirement['id'] }}"
                        data-row-year-level="{{ $requirement['year_level'] }}"
                        data-row-document="{{ $requirement['document'] }}"
                        data-row-type="{{ $requirement['type'] }}"
                        data-row-non-filipino="{{ $requirement['non_filipino'] ? 1 : 0 }}"
                    >
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $requirement['year_level'] }}</td>
                        <td>{{ $requirement['document'] }}</td>
                        <td>{{ $requirement['type'] }}</td>
                        <td>{{ $requirement['non_filipino'] ? 'True' : 'False' }}</td>
                        <td>
                            <div class="apst-action-btn" data-doc-menu-toggle="{{ $menuId }}" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>
                            <div class="apst-dropdown" id="{{ $menuId }}">
                                <button type="button" class="js-doc-edit-btn" data-doc-id="{{ $requirement['id'] }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                                <button type="button" class="apst-del-btn js-doc-delete-btn" data-doc-id="{{ $requirement['id'] }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="doclist-empty-row">
                        <td colspan="6">No requirements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden" id="addDocModal">
    <div class="req-modal-box">
        <div class="req-modal-title">Add New Requirements</div>
        <form id="addDocForm">
            <div class="req-modal-fields doclist-modal-fields-stacked">
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="addGradeLevel">Year Level</label>
                    <select class="req-modal-input" id="addGradeLevel" name="grade_level">
                        <option value="">Select Year Level</option>
                        @foreach ($yearLevelOptions as $yearLevelOption)
                            <option value="{{ $yearLevelOption }}">{{ $yearLevelOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="addDocument">Document / Requirements</label>
                    <input type="text" class="req-modal-input" id="addDocument" name="document" placeholder="Enter document name">
                </div>
                <div class="doclist-modal-option-row">
                    <div class="req-modal-field-group doclist-modal-option-group">
                        <label class="req-modal-label">Type</label>
                        <div class="doclist-modal-option-list">
                            <label class="doclist-radio-label" for="addTypeMedical">
                                <input type="radio" id="addTypeMedical" name="doc_type" value="Medical">
                                <span>Medical</span>
                            </label>
                            <label class="doclist-radio-label" for="addTypeDocument">
                                <input type="radio" id="addTypeDocument" name="doc_type" value="Document" checked>
                                <span>Document</span>
                            </label>
                        </div>
                    </div>
                    <div class="req-modal-field-group doclist-modal-option-group">
                        <label class="req-modal-label">Non Filipino</label>
                        <label class="doclist-checkbox-label doclist-non-filipino-label" for="addNonFilipino">
                            <input type="checkbox" id="addNonFilipino" name="non_filipino" value="1">
                            <span>Yes</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="req-modal-actions">
                <button type="button" class="req-btn-cancel" id="addDocCancelBtn">Cancel</button>
                <button type="submit" class="req-btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden" id="editModal">
    <div class="req-modal-box">
        <div class="req-modal-title">Edit Requirement</div>
        <form id="editForm" method="POST" action="#">
            @csrf
            <input type="hidden" id="editId" name="id">

            <div class="req-modal-fields doclist-modal-fields-stacked">
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="editGradeLevel">Year Level</label>
                    <select class="req-modal-input" id="editGradeLevel" name="grade_level">
                        @foreach ($yearLevelOptions as $yearLevelOption)
                            <option value="{{ $yearLevelOption }}">{{ $yearLevelOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label" for="editDocument">Document / Requirements</label>
                    <input type="text" class="req-modal-input" id="editDocument" name="document">
                </div>
                <div class="doclist-modal-option-row">
                    <div class="req-modal-field-group doclist-modal-option-group">
                        <label class="req-modal-label">Type</label>
                        <div class="doclist-modal-option-list">
                            <label class="doclist-radio-label" for="editTypeMedical">
                                <input type="radio" name="doc_type" id="editTypeMedical" value="Medical">
                                <span>Medical</span>
                            </label>
                            <label class="doclist-radio-label" for="editTypeDocument">
                                <input type="radio" name="doc_type" id="editTypeDocument" value="Document">
                                <span>Document</span>
                            </label>
                        </div>
                    </div>
                    <div class="req-modal-field-group doclist-modal-option-group">
                        <label class="req-modal-label">Non Filipino</label>
                        <label class="doclist-checkbox-label doclist-non-filipino-label" for="editNonFilipino">
                            <input type="checkbox" id="editNonFilipino" name="non_filipino" value="1">
                            <span>Yes</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="req-modal-actions">
                <button type="button" class="req-btn-cancel" id="editDocCancelBtn">Cancel</button>
                <button type="submit" class="req-btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="req-modal-overlay doclist-modal-hidden" id="deleteModal">
    <div class="req-modal-box doclist-delete-modal">
        <div class="doclist-delete-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="req-modal-title doclist-delete-title">Delete Requirement</div>
        <p class="doclist-delete-text">Are you sure you want to delete</p>
        <p class="doclist-delete-name" id="deleteDocName"></p>
        <p class="doclist-delete-note">This action cannot be undone.</p>
        <form id="deleteForm" method="POST" action="#">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteId" name="id">
            <div class="req-modal-actions doclist-delete-actions">
                <button type="button" class="req-btn-cancel" id="deleteDocCancelBtn">Cancel</button>
                <button type="submit" class="req-btn-save req-btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/document-list.js') }}?v={{ time() }}"></script>
@endpush
@endsection