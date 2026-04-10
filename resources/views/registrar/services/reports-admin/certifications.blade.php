@extends('layouts.registrar')

@section('title', 'PLP - Certifications')
@section('page-title', 'CERTIFICATIONS')

@section('content')
<div class="pf-page">
    <div class="rep-dashboard">
        <div class="rep-top-row">
            <div class="rep-sys-card" style="max-width:none;">
                <div class="rep-sys-title">System Configuration</div>
                <div style="display:flex; align-items:flex-end; gap:12px; flex-wrap:nowrap;">
                    <div style="display:flex; align-items:flex-end; gap:10px; flex:1 1 auto; min-width:0; flex-wrap:nowrap;">
                        <div class="rep-sys-field" style="width:170px; min-width:170px;">
                            <label class="app-filter-label">School Year:</label>
                            <select class="app-filter-select">
                                <option>2025-2026</option>
                                <option>2024-2025</option>
                            </select>
                        </div>
                        <div class="rep-sys-field" style="width:130px; min-width:130px;">
                            <label class="app-filter-label">Term:</label>
                            <select class="app-filter-select">
                                <option>First</option>
                                <option>Second</option>
                                <option>Summer</option>
                            </select>
                        </div>
                        <div class="rep-sys-action">
                            <button class="req-btn-save" style="height:36px; min-width: 100px; padding:0 24px; font-weight:700;">Set</button>
                        </div>
                    </div>
                    <div style="align-self:stretch; width:1px; background:#d7e5dc;"></div>
                    <div style="display:flex; align-items:flex-end; gap:10px; flex:1.45 1 auto; min-width:0;">
                        <div class="rep-sys-field" style="flex:1; min-width:260px;">
                            <label class="app-filter-label">Search Certificate</label>
                            <input type="text" class="app-filter-select" id="repQuickSearch" placeholder="Type keyword (e.g. grades, graduation, enrollment)">
                        </div>
                        <button type="button" class="req-btn-cancel" id="repQuickSearchClear" style="height:36px; min-width: 86px;">Clear</button>
                    </div>
                </div>
                <div id="repQuickSearchEmpty" style="display:none; margin-top:10px; color:#6b7280; font-weight:600;">No certificate matched your search.</div>
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.3rem;">Certificate Reports</div>
            <div class="rep-grid-3">
                <button class="rep-btn" onclick="openReportModal('Certificate of Accreditation')">Certificate of Accreditation</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Candidate for Graduation')">Certificate of Candidate for Graduation</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Completed Academic Requirements')">Certificate of Completed Academic Requirements</button>
                
                <button class="rep-btn" onclick="openReportModal('Certificate of Course Description')">Certificate of Course Description</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of English Grades')">Certificate of English Grades</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of English Proficiency, Medium of Instruction')">Certificate of English Proficiency, Medium of Instruction</button>
                
                <button class="rep-btn" onclick="openReportModal('Certificate of Eligibility to Transfer')">Certificate of Eligibility to Transfer</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Enrolled Semester')">Certificate of Enrolled Semester</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Enrollment')">Certificate of Enrollment</button>
                
                <button class="rep-btn" onclick="openReportModal('Certificate of Enrollment w/ Subjects enrolled')">Certificate of Enrollment w/ Subjects enrolled</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Grades')">Certificate of Grades</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Grades Per Period')">Certificate of Grades Per Period</button>
                
                <button class="rep-btn" onclick="openReportModal('Certificate of Grade Point Average')">Certificate of Grade Point Average</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Grading System')">Certificate of Grading System</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Graduation')">Certificate of Graduation</button>
                
                <button class="rep-btn" onclick="openReportModal('Certificate of Graduation w/ GWA')">Certificate of Graduation w/ GWA</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Scholarship')">Certificate of Scholarship</button>
                <button class="rep-btn" onclick="openReportModal('Certificate of Permit to Study Grades')">Certificate of Permit to Study Grades</button>
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.1rem;">Recent Issued Certificates</div>
            <div class="ga-table-wrap app-table-wrap">
                <table class="ga-table app-table" style="min-width: 720px;">
                    <thead>
                        <tr>
                            <th>Date Issued</th>
                            <th>Student</th>
                            <th>Certificate Type</th>
                            <th>Issued By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($recentCertificates ?? collect()) as $record)
                        <tr>
                            <td>{{ optional($record->date_issued)->format('m/d/Y') ?: '-' }}</td>
                            <td>{{ optional($record->student)->student_no }} - {{ optional($record->student)->name }}</td>
                            <td>{{ $record->certificate_type }}</td>
                            <td>{{ $record->issued_by ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td>04/02/2026</td>
                            <td>2022-00123 - Juan Dela Cruz</td>
                            <td>Certificate of Enrollment</td>
                            <td>Registrar</td>
                        </tr>
                        <tr>
                            <td>03/28/2026</td>
                            <td>2021-00457 - Maria Santos</td>
                            <td>Certificate of Grades</td>
                            <td>Registrar</td>
                        </tr>
                        <tr>
                            <td>03/19/2026</td>
                            <td>2020-00891 - Carlo Reyes</td>
                            <td>Certificate of Graduation</td>
                            <td>Registrar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="repPrintModal" style="display:none;">
    <div class="req-modal-box" style="width: 480px;">
        <h3 class="req-modal-title" id="repModalTitle" style="color:#006837; font-size: 1rem;">REPORT</h3>
        <p style="font-size: 0.8rem; color: #666; margin-bottom: 16px; text-align: center;">Fill up basic details included for the requested form/certificate.</p>
        
        <div class="req-modal-fields" style="display:flex; flex-direction:column; gap:12px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Student ID / Name</label>
                <select class="req-modal-input" id="repStudentId">
                    <option value="">Select student...</option>
                    @foreach(($students ?? collect()) as $student)
                    <option value="{{ $student->id }}">{{ $student->student_no }} - {{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Purpose of Request</label>
                <textarea class="req-modal-input" id="repPurpose" rows="2" placeholder="e.g. For employment..."></textarea>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Date Issued</label>
                <input type="date" class="req-modal-input" id="repDate">
            </div>
        </div>
        
        <div class="req-modal-actions" style="margin-top:20px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="closeReportModal()">Cancel</button>
            <button type="button" class="req-btn-save" onclick="generateReport()" style="min-width: 140px;">Print Document</button>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="repPreviewModal" style="display:none;">
    <div class="req-modal-box rep-preview-modal-box">
        <button type="button" class="rep-modal-close-x" onclick="closePreviewModal()" aria-label="Close">&times;</button>
        <div class="rep-preview-head">
            <h3>Document Preview</h3>
        </div>
        <div class="rep-preview-doc-wrap">
            <div class="rep-doc-sheet" id="repPreviewSheet"></div>
        </div>
        <div class="req-modal-actions" style="padding: 0 18px 18px; justify-content:flex-end;">
            <button type="button" class="req-btn-save" onclick="printPreviewDocument()" style="min-width: 150px;">Print Now</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var repIssueConfig = {
        csrfToken: @json(csrf_token()),
        issueUrl: @json(route('registrar.services.reports-admin.certifications.issue'))
    };

    function requestJson(url, method, payload) {
        return fetch(url, {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': repIssueConfig.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: payload ? JSON.stringify(payload) : null
        }).then(function(response) {
            if (!response.ok) {
                return response.json().catch(function() { return {}; }).then(function(data) {
                    var message = 'Unable to issue certification.';
                    if (data && data.errors) {
                        var keys = Object.keys(data.errors);
                        if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                            message = data.errors[keys[0]][0];
                        }
                    }
                    throw new Error(message);
                });
            }
            return response.json().catch(function() { return { ok: true }; });
        });
    }

    function escHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function runReportSearchFilter() {
        var input = document.getElementById('repQuickSearch');
        if (!input) {
            return;
        }

        var keyword = input.value.toLowerCase().trim();
        var buttons = document.querySelectorAll('.rep-group-card .rep-btn[onclick]');
        var visibleCount = 0;

        Array.prototype.forEach.call(buttons, function(button) {
            var text = (button.textContent || '').toLowerCase();
            var isVisible = !keyword || text.indexOf(keyword) !== -1;
            button.style.display = isVisible ? '' : 'none';
            if (isVisible) {
                visibleCount += 1;
            }
        });

        var empty = document.getElementById('repQuickSearchEmpty');
        if (empty) {
            empty.style.display = visibleCount ? 'none' : 'block';
        }
    }

    function initReportSearchFilter() {
        var input = document.getElementById('repQuickSearch');
        var clearBtn = document.getElementById('repQuickSearchClear');
        if (!input || !clearBtn) {
            return;
        }

        input.addEventListener('input', runReportSearchFilter);
        clearBtn.addEventListener('click', function() {
            input.value = '';
            runReportSearchFilter();
            input.focus();
        });
    }

    function openReportModal(title) {
        document.getElementById('repModalTitle').innerText = title.toUpperCase();
        document.getElementById('repModalTitle').dataset.rawTitle = title;
        document.getElementById('repPrintModal').style.display = 'flex';
        var d = new Date();
        var month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        document.getElementById('repDate').value = [year, month, day].join('-');
    }
    function closeReportModal() {
        document.getElementById('repPrintModal').style.display = 'none';
    }

    function closePreviewModal() {
        document.getElementById('repPreviewModal').style.display = 'none';
    }

    function buildDocumentTemplate(title, student, purpose, dateIssued) {
        var gradesBlock = title.toLowerCase().indexOf('grade') !== -1
            ? '<table class="rep-doc-table"><thead><tr><th>Subject</th><th>Grade</th><th>Remarks</th></tr></thead><tbody><tr><td>English Communication</td><td>1.50</td><td>Passed</td></tr><tr><td>Readings in Philippine History</td><td>1.75</td><td>Passed</td></tr><tr><td>Computer Programming 2</td><td>1.25</td><td>Passed</td></tr></tbody></table>'
            : '';

        return '' +
            '<div class="rep-doc-header">' +
                '<div class="rep-doc-school">Pamantasan ng Lungsod ng Pasig</div>' +
                '<div class="rep-doc-meta">Alkalde Jose St., Kapasigan, Pasig City | Registrar\'s Office</div>' +
            '</div>' +
            '<div class="rep-doc-title">' + escHtml(title) + '</div>' +
            '<div class="rep-doc-grid">' +
                '<div><strong>Control No:</strong> REG-2026-0147</div>' +
                '<div><strong>Date Issued:</strong> ' + escHtml(dateIssued) + '</div>' +
                '<div><strong>Student:</strong> ' + escHtml(student) + '</div>' +
                '<div><strong>Purpose:</strong> ' + escHtml(purpose) + '</div>' +
            '</div>' +
            '<div class="rep-doc-line"></div>' +
            '<p class="rep-doc-p">This is to certify that the above-named student is a bona fide student/graduate of Pamantasan ng Lungsod ng Pasig, and that the record reflected herein is based on official registrar records as of the date indicated.</p>' +
            '<p class="rep-doc-p">This certification is issued upon the request of the student for <strong>' + escHtml(purpose) + '</strong> and for whatever legal purpose it may serve.</p>' +
            gradesBlock +
            '<div class="rep-doc-sign">' +
                '<div class="rep-doc-sign-box">' +
                    '<div class="rep-doc-sign-line">Registrar / Authorized Signatory</div>' +
                '</div>' +
            '</div>';
    }

    function generateReport() {
        var title = document.getElementById('repModalTitle').dataset.rawTitle || document.getElementById('repModalTitle').innerText;
        var studentSelect = document.getElementById('repStudentId');
        var studentId = studentSelect ? studentSelect.value : '';
        var student = studentSelect && studentSelect.options[studentSelect.selectedIndex] ? studentSelect.options[studentSelect.selectedIndex].text : '';
        if (!student) {
            student = 'Juan Dela Cruz (2022-00123)';
        }
        var purpose = document.getElementById('repPurpose').value.trim() || 'Employment Requirement';
        var dateIssued = document.getElementById('repDate').value || new Date().toISOString().slice(0, 10);

        document.getElementById('repPreviewSheet').innerHTML = buildDocumentTemplate(title, student, purpose, dateIssued);
        closeReportModal();
        document.getElementById('repPreviewModal').style.display = 'flex';

        if (studentId) {
            requestJson(repIssueConfig.issueUrl, 'POST', {
                student_id: studentId,
                certificate_type: title,
                purpose: purpose,
                date_issued: dateIssued
            }).then(function() {
                window.location.reload();
            }).catch(function(error) {
                alert(error.message || 'Unable to log issued certification.');
            });
        }
    }

    function printPreviewDocument() {
        var title = document.getElementById('repModalTitle').dataset.rawTitle || 'Document';
        var bodyHtml = document.getElementById('repPreviewSheet').innerHTML;
        var printWindow = window.open('', '_blank', 'width=900,height=700');

        if (!printWindow) {
            return;
        }

        var doc = '' +
            '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + escHtml(title) + '</title>' +
            '<style>body{font-family:Poppins,sans-serif;background:#f1f5f3;padding:20px;} .rep-doc-sheet{max-width:780px;margin:0 auto;background:#fff;border:1px solid #cfd8d2;padding:38px 44px;color:#1f2937;} .rep-doc-header{text-align:center;border-bottom:1px solid #d7e2da;padding-bottom:12px;margin-bottom:18px;} .rep-doc-school{font-size:.95rem;font-weight:700;color:#006837;letter-spacing:.03em;text-transform:uppercase;} .rep-doc-meta{margin-top:5px;font-size:.78rem;color:#4b5563;} .rep-doc-title{margin:18px 0 14px;text-align:center;font-size:1.02rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#1f2937;} .rep-doc-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;margin-bottom:14px;font-size:.84rem;} .rep-doc-line{border-top:1px solid #cfd8d2;margin:14px 0;} .rep-doc-p{font-size:.88rem;line-height:1.65;color:#374151;margin-bottom:10px;text-align:justify;} .rep-doc-table{width:100%;border-collapse:collapse;margin:14px 0;font-size:.8rem;} .rep-doc-table th,.rep-doc-table td{border:1px solid #d7e2da;padding:8px 10px;text-align:left;} .rep-doc-table th{background:#f5fbf7;color:#0f5132;font-weight:700;} .rep-doc-sign{margin-top:28px;display:flex;justify-content:flex-end;} .rep-doc-sign-box{width:280px;text-align:center;} .rep-doc-sign-line{border-top:1px solid #374151;margin-top:24px;padding-top:6px;font-size:.8rem;font-weight:600;} @media print{body{background:#fff;padding:0;} .rep-doc-sheet{border:none;box-shadow:none;}}</style>' +
            '</head><body><div class="rep-doc-sheet">' + bodyHtml + '</div></body></html>';

        printWindow.document.open();
        printWindow.document.write(doc);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
        }, 250);
    }

    window.addEventListener('click', function(event) {
        if (event.target && event.target.id === 'repPrintModal') {
            closeReportModal();
        }
        if (event.target && event.target.id === 'repPreviewModal') {
            closePreviewModal();
        }
    });

    document.addEventListener('DOMContentLoaded', initReportSearchFilter);
</script>
@endpush
