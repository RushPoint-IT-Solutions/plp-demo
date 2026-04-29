@extends('layouts.registrar')

@section('title', 'PLP - Batch Update to Student')
@section('page-title', 'BATCH UPDATE TO STUDENT')

@section('content')
<div class="student-page-container">
    <div class="batch-update-wrapper">
        
        {{-- Filter Section --}}
        <form class="app-filter-bar batch-filter-bar" id="batchUpdateFilterForm">
                <div class="app-filter-row">
                    <div class="app-filter-group">
                        <label class="app-filter-label" for="batchFilterDepartment">Department</label>
                        <select id="batchFilterDepartment" name="department" class="app-filter-input w-100">
                            <option value="">All Departments</option>
                            <option>College of Engineering</option>
                            <option>College of Business</option>
                            <option>College of Education</option>
                        </select>
                    </div>
                    <div class="app-filter-group">
                        <label class="app-filter-label" for="batchFilterCourse">Course</label>
                        <select id="batchFilterCourse" name="course" class="app-filter-input w-100">
                            <option value="">All Courses</option>
                            <option>BS Computer Engineering</option>
                            <option>BS Information Technology</option>
                            <option>BS Civil Engineering</option>
                        </select>
                    </div>
                    <div class="app-filter-group">
                        <label class="app-filter-label" for="batchFilterYear">Year Level</label>
                        <select id="batchFilterYear" name="year_level" class="app-filter-input w-100">
                            <option value="">All Year Levels</option>
                            <option>1st Year</option>
                            <option>2nd Year</option>
                            <option>3rd Year</option>
                            <option>4th Year</option>
                        </select>
                    </div>
                    <div class="app-filter-group">
                        <label class="app-filter-label" for="batchFilterSection">Section</label>
                        <select id="batchFilterSection" name="section" class="app-filter-input w-100">
                            <option value="">All Sections</option>
                            <option>Section A</option>
                            <option>Section B</option>
                            <option>Section C</option>
                        </select>
                    </div>
                </div>
                <div class="app-filter-row batch-filter-actions-row">
                    <div class="batch-filter-actions">
                        <button type="submit" class="clean-btn-primary batch-filter-btn">Apply Filters</button>
                    </div>
                </div>
        </form>

        {{-- Table Section --}}
        <div class="batch-results-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); overflow: hidden;">
            <div class="batch-table-head" style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background-color: #f8fafc;">
                <h5 class="batch-table-title" style="margin: 0; font-weight: 700; color: #006837;">Student List</h5>
                <div class="batch-table-actions" style="display: flex; gap: 12px; align-items: center;">
                    <span class="batch-selected-label" style="font-size: 0.85rem; color: #64748b;">Selected: <strong id="selectedCount">0</strong></span>
                    <button class="clean-btn-primary" id="batchActionBtn" disabled style="background-color: #006837; border-radius: 8px; padding: 8px 16px; font-size: 0.9rem;">
                        Batch Actions
                    </button>
                </div>
            </div>
            <div class="student-table-wrapper table-responsive" style="padding: 0;">
                <table class="student-table registrar-table" style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th style="width: 40px; padding: 14px 20px;"><input type="checkbox" id="selectAllStudents" class="app-apply-check-input app-header-checkbox"></th>
                            <th style="padding: 14px 20px;">Student No.</th>
                            <th style="padding: 14px 20px;">Student Name</th>
                            <th style="padding: 14px 20px;">Course</th>
                            <th style="padding: 14px 20px;">Year & Section</th>
                            <th style="padding: 14px 20px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $mockStudents = [
                                ['id' => 1, 'no' => '2023-0001', 'name' => 'John Doe', 'course' => 'BSIT', 'year' => '1st Year', 'section' => 'A', 'status' => 'Regular'],
                                ['id' => 2, 'no' => '2023-0002', 'name' => 'Jane Smith', 'course' => 'BSIT', 'year' => '1st Year', 'section' => 'A', 'status' => 'Regular'],
                                ['id' => 3, 'no' => '2023-0003', 'name' => 'Robert Johnson', 'course' => 'BSCE', 'year' => '2nd Year', 'section' => 'B', 'status' => 'Irregular'],
                                ['id' => 4, 'no' => '2023-0004', 'name' => 'Emily Brown', 'course' => 'BSEE', 'year' => '3rd Year', 'section' => 'C', 'status' => 'Regular'],
                            ];
                        @endphp
                        @foreach($mockStudents as $s)
                        <tr class="batch-student-row" data-student-form-url="{{ route('registrar.process.batch-update-student.form') }}?view=application&student_id={{ urlencode($s['no']) }}&name={{ urlencode($s['name']) }}&course={{ urlencode($s['course']) }}&year_level={{ urlencode($s['year']) }}" style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.2s;">
                            <td style="padding: 14px 20px;"><input type="checkbox" class="student-checkbox app-apply-check-input applicant-row-checkbox"></td>
                            <td style="padding: 14px 20px; font-weight: 600; color: #0f172a;">{{ $s['no'] }}</td>
                            <td style="padding: 14px 20px; color: #334155;">
                                <a class="batch-student-link" href="{{ route('registrar.process.batch-update-student.form') }}?view=application&student_id={{ urlencode($s['no']) }}&name={{ urlencode($s['name']) }}&course={{ urlencode($s['course']) }}&year_level={{ urlencode($s['year']) }}">{{ $s['name'] }}</a>
                            </td>
                            <td style="padding: 14px 20px; color: #475569;">{{ $s['course'] }}</td>
                            <td style="padding: 14px 20px; color: #475569;">{{ $s['year'] }} - {{ $s['section'] }}</td>
                            <td style="padding: 14px 20px;">
                                <span style="background: {{ $s['status'] === 'Regular' ? '#dcfce7' : '#fef9c3' }}; color: {{ $s['status'] === 'Regular' ? '#166534' : '#854d0e' }}; padding: 4px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 600;">
                                    {{ $s['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Batch Action Modal --}}
<div class="pf-modal-overlay faculty-gs-hidden" id="batchActionModal" aria-hidden="true" style="backdrop-filter: blur(4px); background-color: rgba(15, 23, 42, 0.5);">
    <div class="pf-modal-box" style="width: 100%; max-width: 500px; padding: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <div style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 1.125rem; font-weight: 600; color: #0f172a;">Batch Update Information</div>
            <button type="button" class="close-batch-modal" style="background: none; border: none; color: #64748b; cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div style="padding: 24px; background-color: #ffffff;">
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 20px;">Updating <strong id="modalSelectedCount">0</strong> students.</p>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">Update Year Level</label>
                    <select class="clean-input" style="width: 100%;">
                        <option value="">- No Change -</option>
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <option>3rd Year</option>
                        <option>4th Year</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">Update Section</label>
                    <select class="clean-input" style="width: 100%;">
                        <option value="">- No Change -</option>
                        <option>Section A</option>
                        <option>Section B</option>
                        <option>Section C</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">Update Status</label>
                    <select class="clean-input" style="width: 100%;">
                        <option value="">- No Change -</option>
                        <option>Regular</option>
                        <option>Irregular</option>
                        <option>On-Leave</option>
                        <option>Transferred</option>
                    </select>
                </div>
            </div>
        </div>
        <div style="padding: 16px 24px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button" class="close-batch-modal" style="padding: 10px 20px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; font-weight: 500; cursor: pointer;">Cancel</button>
            <button type="button" id="confirmBatchUpdate" style="padding: 10px 20px; background: #059669; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Save Changes</button>
        </div>
    </div>
</div>

<style>
    .batch-filter-bar {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .batch-filter-actions-row {
        justify-content: flex-end;
    }
    .batch-filter-actions {
        display: flex;
        justify-content: flex-end;
        width: 100%;
    }
    .batch-filter-btn {
        background-color: #006837;
        border-radius: 8px;
        padding: 8px 20px;
        min-width: 160px;
    }
    .batch-student-link {
        color: #006837;
        font-weight: 600;
        text-decoration: none;
    }
    .batch-student-link:hover {
        text-decoration: underline;
    }
    .batch-student-row {
        cursor: pointer;
    }
    .app-apply-check-input {
        width: 18px !important;
        height: 18px !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        background-color: #fff !important;
        border: 2px solid #006837 !important;
        border-radius: 4px !important;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        display: block;
        margin: 0 auto;
    }
    .app-apply-check-input::before,
    .app-apply-check-input::after {
        display: none !important;
        content: none !important;
    }
    .app-header-checkbox {
        border-color: #ffffff !important;
    }
    .app-header-checkbox:checked {
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23006837' stroke-linecap='round' stroke-linejoin='round' stroke-width='3.5' d='m6 10 3 3 6-6'/%3e%3c/svg%3e") !important;
        background-size: 80% 80% !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }
    .applicant-row-checkbox:checked {
        background-color: #006837 !important;
        border-color: #006837 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3.5' d='m6 10 3 3 6-6'/%3e%3c/svg%3e") !important;
        background-size: 80% 80% !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }
    .clean-input {
        padding: 10px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.95rem;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        background-color: #ffffff;
    }
    .clean-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .clean-btn-primary {
        color: white;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .clean-btn-primary:hover:not(:disabled) {
        filter: brightness(1.1);
        transform: translateY(-1px);
    }
    .clean-btn-primary:active:not(:disabled) {
        transform: translateY(0);
    }
    .clean-btn-primary:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .clean-btn-ghost {
        background: transparent;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .clean-btn-ghost:hover {
        background: #f1f5f9;
        color: #0f172a !important;
    }
    .student-table tr:hover {
        background-color: #f8fafc;
    }
    @media (max-width: 768px) {
        .batch-table-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
        .batch-table-actions {
            width: 100%;
            justify-content: space-between;
        }
        .batch-filter-actions {
            justify-content: stretch;
        }
        .batch-filter-btn {
            width: 100%;
        }
    }
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllStudents');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const batchActionBtn = document.getElementById('batchActionBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const modalSelectedCount = document.getElementById('modalSelectedCount');
    const batchModal = document.getElementById('batchActionModal');
    const closeBtns = document.querySelectorAll('.close-batch-modal');
    const confirmBtn = document.getElementById('confirmBatchUpdate');

    function updateUI() {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        const totalCount = studentCheckboxes.length;
        selectedCountSpan.textContent = checkedCount;
        modalSelectedCount.textContent = checkedCount;
        batchActionBtn.disabled = checkedCount === 0;
        if (selectAll) {
            selectAll.checked = checkedCount > 0 && checkedCount === totalCount;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            studentCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateUI();
        });
    }

    studentCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateUI);
    });

    const studentRows = document.querySelectorAll('.batch-student-row');
    studentRows.forEach(row => {
        row.addEventListener('click', function(event) {
            if (event.target.closest('input') || event.target.closest('button') || event.target.closest('a')) {
                return;
            }
            const targetUrl = row.getAttribute('data-student-form-url');
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        });
    });

    if (batchActionBtn) {
        batchActionBtn.addEventListener('click', function() {
            batchModal.classList.remove('faculty-gs-hidden');
        });
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            batchModal.classList.add('faculty-gs-hidden');
        });
    });

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Saving...';
            setTimeout(() => {
                alert('Batch update completed successfully.');
                batchModal.classList.add('faculty-gs-hidden');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Save Changes';
                // Reset checkboxes
                selectAll.checked = false;
                selectAll.indeterminate = false;
                studentCheckboxes.forEach(cb => cb.checked = false);
                updateUI();
            }, 1000);
        });
    }

    batchModal.addEventListener('click', function(e) {
        if (e.target === batchModal) batchModal.classList.add('faculty-gs-hidden');
    });
});
</script>
@endpush
