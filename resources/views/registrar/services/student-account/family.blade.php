@extends('layouts.registrar')

@section('title', 'PLP - Student Family')
@section('page-title', 'STUDENT FAMILY')
@section('body-class', 'page-student-account page-student-family')

@section('content')
<style>
    @media (max-width: 992px) {
        .svc-filter-grid.ga-filter-grid.ga-filter-grid-compact {
            grid-template-columns: 1fr 1fr !important;
        }
    }
    @media (max-width: 768px) {
        .svc-filter-grid.ga-filter-grid.ga-filter-grid-compact {
            grid-template-columns: 1fr !important;
        }
        .svc-actions-row {
            flex-direction: column;
            width: 100%;
        }
        .svc-actions-row > button {
            width: 100%;
            justify-content: center;
        }
    }
</style>
<div class="pf-page">
    <form method="GET" action="{{ route('registrar.services.student-account.family') }}" class="svc-filter-panel mb-2 ga-card ga-filter-card sched-filter-bar">
        <div class="svc-filter-grid ga-filter-grid ga-filter-grid-compact" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; align-items: end;">
            <div class="svc-filter-item">
                <span class="app-filter-label">Student Name / ID</span>
                <input type="text" name="q" class="app-filter-input pf-search-input" placeholder="Search Name, Student ID" style="width: 100%;" value="{{ $search ?? '' }}">
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Parent Name</span>
                <input type="text" name="parent_name" class="app-filter-input pf-search-input" placeholder="Search Parent Name" style="width: 100%;" value="{{ $parent ?? '' }}">
            </div>

            <div class="svc-filter-item">
                <span class="app-filter-label">Year Level</span>
                <select name="year_level" class="app-filter-select" style="width: 100%;">
                    <option value="">Select Year Level...</option>
                    <option value="First" {{ ($yearLevel ?? '') === 'First' ? 'selected' : '' }}>First</option>
                    <option value="Second" {{ ($yearLevel ?? '') === 'Second' ? 'selected' : '' }}>Second</option>
                    <option value="Third" {{ ($yearLevel ?? '') === 'Third' ? 'selected' : '' }}>Third</option>
                    <option value="Fourth" {{ ($yearLevel ?? '') === 'Fourth' ? 'selected' : '' }}>Fourth</option>
                </select>
            </div>

            <div class="svc-filter-item" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <label class="setup-checkbox-label" style="color: #d32f2f; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 6px;">
                    <input type="checkbox" id="showSiblings" name="with_siblings" value="1" {{ !empty($withSiblings) ? 'checked' : '' }}>
                    <span>Note: Check this to see the siblings.</span>
                </label>
                <button type="submit" class="pf-btn-new ga-btn ga-btn-primary" style="width: 100%;">Search</button>
            </div>
        </div>
    </form>

    <div class="svc-actions-row mb-2" style="justify-content: flex-end; gap: 12px; display: flex;">
        <button type="button" class="pf-btn-new ga-btn ga-btn-primary" style="display: flex; align-items: center; gap: 6px;" onclick="document.getElementById('famBatchModal').style.display='flex'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            Batch Family Code
        </button>
        <button type="button" class="pf-btn-new ga-btn ga-btn-primary" style="display: flex; align-items: center; gap: 6px;" onclick="document.getElementById('famNewModal').style.display='flex'">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            New Family Code
        </button>
    </div>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table svc-table" id="familyTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th>Family Code</th>
                    <th>Student No.</th>
                    <th>Name</th>
                    <th>Parent Name</th>
                    <th>Eldest</th>
                    <th>Status</th>
                    <th style="width: 70px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($familyRows as $idx => $row)
                    <tr>
                        <td>{{ $row->family_code }}</td>
                        <td>{{ $row->student_no ?: 'N/A' }}</td>
                        <td>{{ $row->display_name }}</td>
                        <td>{{ $row->parent_name }}</td>
                        <td>{{ $row->eldest_label }}</td>
                        <td>
                            @if($row->status_label === 'Active')
                                <span style="background-color: #e8f5e9; color: #006837; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Active</span>
                            @else
                                <span style="background-color: #fff3e0; color: #e65100; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @php $menuId = 'family-menu-' . (($familyRows->firstItem() ?? 0) + $idx); @endphp
                            <div class="apst-action-btn" data-gc-menu-toggle="{{ $menuId }}" aria-label="Open row actions" title="Actions">
                                <span></span><span></span><span></span>
                            </div>
                            <div class="apst-dropdown" id="{{ $menuId }}">
                                <button type="button" data-ga-open-action="edit" data-ga-item="edit"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</button>
                                <button type="button" class="apst-del-btn" data-ga-open-action="delete" data-ga-item="del"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No family records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="app-table-pager mt-3">
        {{ $familyRows->links() }}
    </div>

    <!-- Modals -->
    <div class="req-modal-overlay" id="famActionModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 25px; text-align: left;">
            <h3 class="req-modal-title" style="margin-bottom: 20px;">EDIT FAMILY DETAILS</h3>
            <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">FAMILY CODE</label>
                    <input class="req-modal-input" id="famEditCode" disabled style="background:#f5f5f5;">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STUDENT NO.</label>
                    <input class="req-modal-input" id="famEditStudentNo">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">NAME</label>
                    <input class="req-modal-input" id="famEditName">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">PARENT NAME</label>
                    <input class="req-modal-input" id="famEditParentName" disabled style="background:#f5f5f5;">
                </div>
                <div class="req-modal-field-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label class="req-modal-label">ELDEST</label>
                          <select class="req-modal-input" id="famEditEldest" style="width: 100%;">
                              <option value="Yes">Yes</option>
                              <option value="No">No</option>
                          </select>
                      </div>
                      <div>
                          <label class="req-modal-label">STATUS</label>
                          <select class="req-modal-input" id="famEditStatus" style="width: 100%;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="req-modal-actions" style="margin-top: 25px;">
                <button type="button" class="req-btn-cancel" onclick="document.getElementById('famActionModal').style.display='none'">Close</button>
                <button type="button" class="req-btn-save" style="background:#006837;" data-fam-save="edit">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="famBatchModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 25px; text-align: left;">
            <h3 class="req-modal-title" style="margin-bottom: 20px;">BATCH FAMILY CODE</h3>
            <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">UPLOAD EXCEL / CSV</label>
                    <input type="file" class="req-modal-input" style="padding:10px;">
                </div>
            </div>
            <div class="req-modal-actions" style="margin-top: 25px;">
                <button type="button" class="req-btn-cancel" onclick="document.getElementById('famBatchModal').style.display='none'">Close</button>
                <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('famBatchModal').style.display='none'">Upload File</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="famNewModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div class="req-modal-box" style="max-width:500px; width: 100%; margin: 0; padding: 25px; text-align: left;">
            <h3 class="req-modal-title" style="margin-bottom: 20px;">NEW FAMILY DETAILS</h3>
            <div class="req-modal-fields" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="req-modal-field-group">
                    <label class="req-modal-label">FAMILY CODE</label>
                    <input class="req-modal-input" placeholder="e.g. FAM-2026-001">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">STUDENT NO.</label>
                    <input class="req-modal-input" placeholder="Student No.">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">NAME</label>
                    <input class="req-modal-input" placeholder="Student Name">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">PARENT NAME</label>
                    <input class="req-modal-input" placeholder="Parent / Guardian Name">
                </div>
                <div class="req-modal-field-group">
                    <label class="req-modal-label">ELDEST</label>
                    <select class="req-modal-input" style="width: 100%;">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>
            <div class="req-modal-actions" style="margin-top: 25px;">
                <button type="button" class="req-btn-cancel" onclick="document.getElementById('famNewModal').style.display='none'">Close</button>
                <button type="button" class="req-btn-save" style="background:#006837;" onclick="document.getElementById('famNewModal').style.display='none'">Create Family Record</button>
            </div>
        </div>
    </div>

    <div class="req-modal-overlay" id="famDeleteModal" style="display:none; align-items:center; justify-content:center; z-index:1050; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div class="req-modal-box" style="margin: 0; padding: 25px; max-width: 400px; width: 100%; text-align: center;">
            <h3 class="req-modal-title" style="color: #d93025; font-size: 1.1rem; text-align: center;">DELETE FAMILY RECORD</h3>
            <p style="font-size: 0.9rem; color: #444; margin: 15px 0 25px;">Are you sure you want to delete this specific family record?</p>
            <div class="req-modal-actions" style="justify-content: center;">
                <button type="button" class="req-btn-cancel" onclick="document.getElementById('famDeleteModal').style.display='none'">Cancel</button>
                <button type="button" class="req-btn-save" style="background: #d93025;" data-fam-save="delete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var page = document.querySelector('.pf-page');
    if (!page) return;

    var activeRow = null;
    var famInputs = {
        code: document.getElementById('famEditCode'),
        studentNo: document.getElementById('famEditStudentNo'),
        name: document.getElementById('famEditName'),
        parentName: document.getElementById('famEditParentName'),
        eldest: document.getElementById('famEditEldest'),
        status: document.getElementById('famEditStatus')
    };

    function closeActionMenus() {
        page.querySelectorAll('.apst-dropdown.open').forEach(function (menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function toggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;
        var isOpen = menu.classList.contains('open');
        closeActionMenus();
        if (isOpen) return;

        var rect = trigger.getBoundingClientRect();
        var spaceBelow = window.innerHeight - rect.bottom;
        
        menu.style.left = 'auto';
        menu.style.right = (window.innerWidth - rect.left + 4) + 'px';
        
        if (spaceBelow < 120) {
            menu.classList.add('drop-up');
            menu.style.top = 'auto';
            menu.style.bottom = (window.innerHeight - rect.bottom) + 'px';
        } else {
            menu.style.top = rect.top + 'px';
            menu.style.bottom = 'auto';
        }
        menu.classList.add('open');
    }

    function fillInputsFromRow(row) {
        if (!row || row.cells.length < 6) return;
        if (famInputs.code) famInputs.code.value = (row.cells[0].textContent || '').trim();
        if (famInputs.studentNo) famInputs.studentNo.value = (row.cells[1].textContent || '').trim();
        if (famInputs.name) famInputs.name.value = (row.cells[2].textContent || '').trim();
        if (famInputs.parentName) famInputs.parentName.value = (row.cells[3].textContent || '').trim();
        if (famInputs.eldest) famInputs.eldest.value = (row.cells[4].textContent || '').trim();
        if (famInputs.status) famInputs.status.value = (row.cells[5].textContent || '').trim();
    }
    
    function saveInputsToRow(row) {
        if (!row || row.cells.length < 6) return;
        row.cells[0].textContent = famInputs.code && famInputs.code.value ? famInputs.code.value.trim() : row.cells[0].textContent;
        row.cells[1].textContent = famInputs.studentNo && famInputs.studentNo.value ? famInputs.studentNo.value.trim() : row.cells[1].textContent;
        row.cells[2].textContent = famInputs.name && famInputs.name.value ? famInputs.name.value.trim() : row.cells[2].textContent;
        row.cells[4].textContent = famInputs.eldest && famInputs.eldest.value ? famInputs.eldest.value.trim() : row.cells[4].textContent;
        
        var statusVal = famInputs.status && famInputs.status.value ? famInputs.status.value.trim() : 'Active';
        if (statusVal === 'Active') {
            row.cells[5].innerHTML = '<span style="background-color: #e8f5e9; color: #006837; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Active</span>';
        } else {
            row.cells[5].innerHTML = '<span style="background-color: #fff3e0; color: #e65100; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Inactive</span>';
        }
    }

    page.addEventListener('click', function(e) {
        var toggleBtn = e.target.closest('[data-gc-menu-toggle]');
        if (toggleBtn) {
            e.stopPropagation();
            toggleActionMenu(toggleBtn.getAttribute('data-gc-menu-toggle'), toggleBtn);
            return;
        }

        if (!e.target.closest('.apst-dropdown')) {
            closeActionMenus();
        }

        var actionBtn = e.target.closest('[data-ga-open-action]');
        if (actionBtn) {
            closeActionMenus();
            activeRow = actionBtn.closest('tr');
            var action = actionBtn.getAttribute('data-ga-open-action');
            if (action === 'delete') {
                document.getElementById('famDeleteModal').style.display = 'flex';
            } else if (action === 'edit') {
                fillInputsFromRow(activeRow);
                document.getElementById('famActionModal').style.display = 'flex';
            }
            return;
        }

        var saveBtn = e.target.closest('[data-fam-save]');
        if (saveBtn) {
            var action = saveBtn.getAttribute('data-fam-save');
            if (action === 'edit' && activeRow) {
                saveInputsToRow(activeRow);
                document.getElementById('famActionModal').style.display = 'none';
            } else if (action === 'delete' && activeRow) {
                activeRow.remove();
                document.getElementById('famDeleteModal').style.display = 'none';
            }
        }
    });

    window.addEventListener('scroll', closeActionMenus, true);
    
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('req-modal-overlay')) {
            e.target.style.display = 'none';
        }
    });
});
</script>
@endpush
