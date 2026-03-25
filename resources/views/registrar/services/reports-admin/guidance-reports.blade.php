@extends('layouts.registrar')

@section('title', 'PLP - Guidance Reports')
@section('page-title', 'GUIDANCE REPORTS')

@section('content')
<div class="pf-page">
    <div class="rep-dashboard">
        <div class="rep-top-row">
            <div class="rep-sys-card rep-sys-card--compact">
                <div class="rep-sys-title">System Configuration</div>
                <div class="rep-sys-grid">
                    <div class="rep-sys-field">
                        <label class="app-filter-label">School Year:</label>
                        <select class="app-filter-select">
                            <option>2025-2026</option>
                            <option>2024-2025</option>
                        </select>
                    </div>
                    <div class="rep-sys-field">
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
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.3rem;">ADMISSION REPORTS</div>
            <div class="rep-grid-3">
                <button class="rep-btn" onclick="openReportModal('List of Freshmen examinees pass/fail per college / program')">List of Freshmen examinees pass/fail per college / program</button>
                <button class="rep-btn" onclick="openReportModal('List of overall examiness for freshman pass/fail')">List of overall examiness for freshman pass/fail</button>
                <button class="rep-btn" onclick="openReportModal('List of Junior High School pass/fail examinees')">List of Junior High School pass/fail examinees</button>
                <button class="rep-btn" onclick="openReportModal('List of Senior High School pass/fail examinees')">List of Senior High School pass/fail examinees</button>
                <button class="rep-btn" onclick="openReportModal('List of overall examinees for basic education pass/fail')">List of overall examinees for basic education pass/fail</button>
                <button class="rep-btn" onclick="openReportModal('Report of No. of Enrollees with Psychological Test')">Report of No. of Enrollees with Psychological Test</button>
                <button class="rep-btn" onclick="openReportModal('List of readmission with behavioral contract')">List of readmission with behavioral contract</button>
                <button class="rep-btn" onclick="openReportModal('Status report of freshman examinees/enrollees')">Status report of freshman examinees/enrollees</button>
                <button class="rep-btn" onclick="openReportModal('Master-list of applicants for college with test permit')">Master-list of applicants for college with test permit</button>
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.3rem;">ENROLLMENT REPORTS</div>
            <div class="rep-grid-3">
                <button class="rep-btn" onclick="openReportModal('Report of Shifting students')">Report of Shifting students</button>
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
                <input type="text" class="req-modal-input" id="repStudent" placeholder="Enter student...">
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
    function escHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
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
        var student = document.getElementById('repStudent').value.trim() || 'Juan Dela Cruz (2022-00123)';
        var purpose = document.getElementById('repPurpose').value.trim() || 'Employment Requirement';
        var dateIssued = document.getElementById('repDate').value || new Date().toISOString().slice(0, 10);

        document.getElementById('repPreviewSheet').innerHTML = buildDocumentTemplate(title, student, purpose, dateIssued);
        closeReportModal();
        document.getElementById('repPreviewModal').style.display = 'flex';
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
</script>
@endpush
