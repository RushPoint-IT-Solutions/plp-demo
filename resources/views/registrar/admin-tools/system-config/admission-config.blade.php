@extends('layouts.registrar')

@section('title', 'PLP - Admission Config')
@section('page-title', 'ADMISSION CONFIG')

@section('content')
<div class="pf-page">
    <div class="adm-page">
        <div class="adm-grid">
            <section class="adm-card">
                <div class="adm-card-head">
                    <h3>Admission Period</h3>
                </div>
                <div class="adm-setting-row">
                    <div class="adm-setting-field">
                        <label class="req-modal-label">Date From</label>
                        <input type="date" id="admDateFromSetting" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
                    </div>
                    <div class="adm-setting-field">
                        <label class="req-modal-label">Date To</label>
                        <input type="date" id="admDateToSetting" class="req-modal-input" onclick="if(this.showPicker){this.showPicker()}" onfocus="if(this.showPicker){this.showPicker()}">
                    </div>
                    <div class="adm-setting-field adm-setting-action">
                        <label class="req-modal-label">&nbsp;</label>
                        <button type="button" class="req-btn-save adm-setting-save" onclick="admSaveAdmissionPeriodSetting()">Save Admission Period</button>
                    </div>
                </div>
                <div class="adm-setting-note" id="admAdmissionNote"></div>
            </section>

            <section class="adm-card">
                <div class="adm-card-head">
                    <h3>Orientation Period</h3>
                    <button type="button" class="pf-btn-new" onclick="admOpenAddItem('orientation')">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="admOrientationTable" class="app-table adm-table" data-no-auto-pager="1" style="min-width: 740px;">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Program</th>
                                <th>Contents</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="admOrientationBody"></tbody>
                    </table>
                </div>
                <div class="adm-pagination" id="admOrientationPager"></div>
            </section>

            <section class="adm-card">
                <div class="adm-card-head">
                    <h3>Application Form Agreement</h3>
                    <button type="button" class="pf-btn-new" onclick="admOpenAddItem('formAgreement')">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="admFormAgreementTable" class="app-table adm-table" data-no-auto-pager="1" style="min-width: 620px;">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Contents</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="admFormAgreementBody"></tbody>
                    </table>
                </div>
                <div class="adm-pagination" id="admFormAgreementPager"></div>
            </section>

            <section class="adm-card">
                <div class="adm-card-head">
                    <h3>Enrollment Guidelines</h3>
                    <button type="button" class="pf-btn-new" onclick="admOpenAddItem('enrollment')">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="admEnrollmentTable" class="app-table adm-table" data-no-auto-pager="1" style="min-width: 740px;">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Program</th>
                                <th>Contents</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="admEnrollmentBody"></tbody>
                    </table>
                </div>
                <div class="adm-pagination" id="admEnrollmentPager"></div>
            </section>

            <section class="adm-card">
                <div class="adm-card-head">
                    <h3>Application Guidelines</h3>
                    <button type="button" class="pf-btn-new" onclick="admOpenAddItem('applicationGuidelines')">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="admAppGuidelinesTable" class="app-table adm-table" data-no-auto-pager="1" style="min-width: 620px;">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Contents</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="admAppGuidelinesBody"></tbody>
                    </table>
                </div>
                <div class="adm-pagination" id="admAppGuidelinesPager"></div>
            </section>

            <section class="adm-card">
                <div class="adm-card-head">
                    <h3>School Compliance Agreement</h3>
                    <button type="button" class="pf-btn-new" onclick="admOpenAddItem('compliance')">Add</button>
                </div>
                <div class="app-table-wrap">
                    <table id="admComplianceTable" class="app-table adm-table" data-no-auto-pager="1" style="min-width: 740px;">
                        <thead>
                            <tr>
                                <th>SY</th>
                                <th>Program</th>
                                <th>Contents</th>
                                <th style="text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="admComplianceBody"></tbody>
                    </table>
                </div>
                <div class="adm-pagination" id="admCompliancePager"></div>
            </section>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="admItemModal" style="display:none;" onclick="if(event.target===this) admCloseModal('admItemModal')">
    <div class="req-modal-box cfg-modal-box cfg-modal-wide">
        <h3 class="req-modal-title" id="admItemModalTitle">ADD ITEM</h3>
        <div class="sc-modal-grid-3">
            <div class="req-modal-field-group">
                <label class="req-modal-label">SY</label>
                <input type="text" id="admItemSY" class="req-modal-input" placeholder="e.g. 2025-2026">
            </div>
            <div class="req-modal-field-group" id="admItemProgramField">
                <label class="req-modal-label">Program</label>
                <input type="text" id="admItemProgram" class="req-modal-input" placeholder="e.g. All Program">
            </div>
            <div class="req-modal-field-group" style="grid-column:1 / -1;">
                <label class="req-modal-label">Contents</label>
                <textarea id="admItemContents" class="req-modal-input sc-modal-textarea" placeholder="Enter contents"></textarea>
            </div>
        </div>
        <div class="req-modal-actions" style="margin-top:14px;">
            <button type="button" class="req-btn-cancel" onclick="admCloseModal('admItemModal')">Cancel</button>
            <button type="button" id="admItemSaveBtn" class="req-btn-save" onclick="admSaveItem()">Save</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var admAdmissionPeriod = { dateFrom: '2025-12-01', dateTo: '2026-03-20' };

    var admData = {
        orientation: [
            { sy: '2025-2026', program: 'All Program', contents: 'Orientation starts with campus introduction and student handbook briefing.' },
            { sy: '2025-2026', program: 'BSIT', contents: 'Department orientation with curriculum walkthrough and lab safety discussion.' },
            { sy: '2026-2027', program: 'BSCS', contents: 'Freshmen orientation for computing programs and student support services.' },
            { sy: '2026-2027', program: 'BSBA', contents: 'Business department orientation and student policy reminders.' },
            { sy: '2026-2027', program: 'BSED', contents: 'Education program orientation and practicum expectations.' },
            { sy: '2027-2028', program: 'All Program', contents: 'General orientation for returning and transferee students.' }
        ],
        formAgreement: [
            { sy: '2025-2026', contents: 'By submitting this form, applicant confirms all provided information is true and complete.' },
            { sy: '2026-2027', contents: 'Applicant agrees to comply with institutional policies and admission requirements.' },
            { sy: '2027-2028', contents: 'Consent to data processing is acknowledged upon submission of application form.' },
            { sy: '2028-2029', contents: 'Applicant confirms understanding of deadlines and documentary obligations.' },
            { sy: '2029-2030', contents: 'All records submitted are subject to verification by admissions office.' },
            { sy: '2030-2031', contents: 'False declarations may result in denial of admission or cancellation of enrollment.' }
        ],
        enrollment: [
            { sy: '2025-2026', program: 'All Program', contents: 'Enrollment requires complete credentials and signed admission clearance.' },
            { sy: '2025-2026', program: 'BSIT', contents: 'Submit pre-enrollment form and validated subject checklist.' },
            { sy: '2026-2027', program: 'BSCS', contents: 'Proceed to advising before section and subject confirmation.' },
            { sy: '2026-2027', program: 'BSBA', contents: 'Finalize subjects and settle initial fees within enrollment schedule.' },
            { sy: '2026-2027', program: 'BSED', contents: 'Advised load must be approved by department chair.' },
            { sy: '2027-2028', program: 'All Program', contents: 'Late enrollment is subject to office approval and available slots.' }
        ],
        applicationGuidelines: [
            { sy: '2025-2026', contents: 'Complete all required fields in the application portal before submission.' },
            { sy: '2026-2027', contents: 'Upload clear and valid copies of required admission documents.' },
            { sy: '2027-2028', contents: 'Monitor announcements for exam schedules and interview notices.' },
            { sy: '2028-2029', contents: 'Applications with incomplete requirements will remain pending.' },
            { sy: '2029-2030', contents: 'Ensure contact information is active for admissions communication.' },
            { sy: '2030-2031', contents: 'Observe all deadlines indicated in the admissions calendar.' }
        ],
        compliance: [
            { sy: '2025-2026', program: 'All Program', contents: 'Student agrees to follow school policies and code of conduct at all times.' },
            { sy: '2025-2026', program: 'BSIT', contents: 'Compliance with laboratory safety standards is mandatory.' },
            { sy: '2026-2027', program: 'BSCS', contents: 'Academic integrity policy must be strictly observed.' },
            { sy: '2026-2027', program: 'BSBA', contents: 'Attendance and classroom behavior standards apply to all students.' },
            { sy: '2026-2027', program: 'BSED', contents: 'Professional conduct is required during classroom observations and practicum.' },
            { sy: '2027-2028', program: 'All Program', contents: 'Violation of school policies may result in disciplinary action.' }
        ]
    };

    var admPager = {
        orientation: { page: 1, size: 5 },
        formAgreement: { page: 1, size: 5 },
        enrollment: { page: 1, size: 5 },
        applicationGuidelines: { page: 1, size: 5 },
        compliance: { page: 1, size: 5 }
    };

    var admEditState = {
        group: null,
        index: null
    };

    function admEscapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function(ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function admFormatDate(dateString) {
        if (!dateString) return '';
        var d = new Date(dateString + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: '2-digit' });
    }

    function admCloseActionMenus() {
        document.querySelectorAll('.apst-dropdown.open').forEach(function(menu) {
            menu.classList.remove('open', 'drop-up');
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
    }

    function admToggleActionMenu(menuId, trigger) {
        var menu = document.getElementById(menuId);
        if (!menu || !trigger) return;
        var isOpen = menu.classList.contains('open');
        admCloseActionMenus();
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

    function admOpenModal(id) {
        admCloseActionMenus();
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'flex';
    }

    function admCloseModal(id) {
        var modal = document.getElementById(id);
        if (modal) modal.style.display = 'none';
    }

    function admMaxPage(group) {
        var data = admData[group] || [];
        return Math.max(1, Math.ceil(data.length / admPager[group].size));
    }

    function admSetPage(group, page) {
        var maxPage = admMaxPage(group);
        admPager[group].page = Math.min(maxPage, Math.max(1, page));
        admRenderGroup(group);
    }

    function admBuildPageButtons(group, current, maxPage) {
        var html = '';
        var start = Math.max(1, current - 2);
        var end = Math.min(maxPage, current + 2);

        if (current <= 3) {
            end = Math.min(maxPage, 5);
        } else if (current >= maxPage - 2) {
            start = Math.max(1, maxPage - 4);
        }

        for (var p = start; p <= end; p++) {
            html += '<button type="button" class="btn adm-page-num ' + (p === current ? 'active' : '') + '" ' +
                (p === current ? 'aria-current="page"' : '') + ' onclick="admSetPage(\'' + group + '\',' + p + ')">' + p + '</button>';
        }

        return html;
    }

    function admRenderPager(group, pagerId) {
        var pager = document.getElementById(pagerId);
        if (!pager) return;
        var total = (admData[group] || []).length;
        if (total <= admPager[group].size) {
            pager.innerHTML = '';
            return;
        }

        var current = admPager[group].page;
        var maxPage = admMaxPage(group);
        pager.innerHTML = '' +
            '<nav aria-label="Admission pagination" class="adm-page-nav-wrap">' +
                '<div class="adm-page-list" role="group" aria-label="Page controls">' +
                    '<button type="button" class="btn adm-page-btn" ' + (current <= 1 ? 'disabled' : '') + ' onclick="admSetPage(\'' + group + '\',' + (current - 1) + ')">&lt;</button>' +
                    admBuildPageButtons(group, current, maxPage) +
                    '<button type="button" class="btn adm-page-btn" ' + (current >= maxPage ? 'disabled' : '') + ' onclick="admSetPage(\'' + group + '\',' + (current + 1) + ')">&gt;</button>' +
                '</div>' +
            '</nav>';
    }

    function admBuildActionMenu(group, index) {
        var menuId = 'admMenu-' + group + '-' + index;
        return '' +
            '<div class="apst-action-btn" data-adm-menu-toggle="' + menuId + '" aria-label="Open row actions" title="Actions"><span></span><span></span><span></span></div>' +
            '<div class="apst-dropdown" id="' + menuId + '">' +
                '<button type="button" onclick="admOpenEditItem(\'' + group + '\',' + index + ')">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>' +
                    'Edit' +
                '</button>' +
                '<button type="button" class="apst-del-btn" onclick="admDeleteItem(\'' + group + '\',' + index + ')">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
                    'Delete' +
                '</button>' +
            '</div>';
    }

    function admRenderAdmissionPeriod() {
        var fromInput = document.getElementById('admDateFromSetting');
        var toInput = document.getElementById('admDateToSetting');
        var note = document.getElementById('admAdmissionNote');
        if (fromInput) fromInput.value = admAdmissionPeriod.dateFrom || '';
        if (toInput) toInput.value = admAdmissionPeriod.dateTo || '';
        if (note) {
            note.textContent = 'Current period: ' + admFormatDate(admAdmissionPeriod.dateFrom) + ' to ' + admFormatDate(admAdmissionPeriod.dateTo);
        }
    }

    function admRenderGroup(group) {
        var map = {
            orientation: { body: 'admOrientationBody', pager: 'admOrientationPager', cols: 4, hasProgram: true },
            formAgreement: { body: 'admFormAgreementBody', pager: 'admFormAgreementPager', cols: 3, hasProgram: false },
            enrollment: { body: 'admEnrollmentBody', pager: 'admEnrollmentPager', cols: 4, hasProgram: true },
            applicationGuidelines: { body: 'admAppGuidelinesBody', pager: 'admAppGuidelinesPager', cols: 3, hasProgram: false },
            compliance: { body: 'admComplianceBody', pager: 'admCompliancePager', cols: 4, hasProgram: true }
        };

        var cfg = map[group];
        if (!cfg) return;

        var body = document.getElementById(cfg.body);
        var list = admData[group] || [];
        var page = admPager[group].page;
        var size = admPager[group].size;
        var start = (page - 1) * size;
        var slice = list.slice(start, start + size);

        var rows = slice.map(function(item, idx) {
            var index = start + idx;
            var tds = '' +
                '<td>' + admEscapeHtml(item.sy) + '</td>';

            if (cfg.hasProgram) {
                tds += '<td>' + admEscapeHtml(item.program) + '</td>';
            }

            tds += '<td>' + admEscapeHtml(item.contents) + '</td>' +
                '<td style="text-align:center;">' + admBuildActionMenu(group, index) + '</td>';

            return '<tr>' + tds + '</tr>';
        }).join('');

        body.innerHTML = rows || '<tr><td colspan="' + cfg.cols + '" class="sc-empty-row">No records found.</td></tr>';
        admRenderPager(group, cfg.pager);
    }

    function admRenderAll() {
        admRenderAdmissionPeriod();
        admRenderGroup('orientation');
        admRenderGroup('formAgreement');
        admRenderGroup('enrollment');
        admRenderGroup('applicationGuidelines');
        admRenderGroup('compliance');
    }

    function admSaveAdmissionPeriodSetting() {
        var dateFrom = document.getElementById('admDateFromSetting').value;
        var dateTo = document.getElementById('admDateToSetting').value;
        if (!dateFrom || !dateTo) {
            alert('Please complete Date From and Date To.');
            return;
        }
        admAdmissionPeriod.dateFrom = dateFrom;
        admAdmissionPeriod.dateTo = dateTo;
        admRenderAdmissionPeriod();
    }

    function admGroupLabel(group) {
        var names = {
            orientation: 'ORIENTATION PERIOD',
            formAgreement: 'APPLICATION FORM AGREEMENT',
            enrollment: 'ENROLLMENT GUIDELINES',
            applicationGuidelines: 'APPLICATION GUIDELINES',
            compliance: 'SCHOOL COMPLIANCE AGREEMENT'
        };
        return names[group] || 'ITEM';
    }

    function admOpenAddItem(group) {
        admEditState.group = group;
        admEditState.index = null;
        document.getElementById('admItemModalTitle').textContent = 'ADD ' + admGroupLabel(group);
        document.getElementById('admItemSaveBtn').textContent = 'Save';
        document.getElementById('admItemSY').value = '2025-2026';
        document.getElementById('admItemProgram').value = 'All Program';
        document.getElementById('admItemContents').value = '';

        var hasProgram = group === 'orientation' || group === 'enrollment' || group === 'compliance';
        document.getElementById('admItemProgramField').style.display = hasProgram ? 'block' : 'none';
        admOpenModal('admItemModal');
    }

    function admOpenEditItem(group, index) {
        var item = (admData[group] || [])[index];
        if (!item) return;
        admEditState.group = group;
        admEditState.index = index;
        document.getElementById('admItemModalTitle').textContent = 'EDIT ' + admGroupLabel(group);
        document.getElementById('admItemSaveBtn').textContent = 'Update';
        document.getElementById('admItemSY').value = item.sy || '';
        document.getElementById('admItemProgram').value = item.program || 'All Program';
        document.getElementById('admItemContents').value = item.contents || '';

        var hasProgram = group === 'orientation' || group === 'enrollment' || group === 'compliance';
        document.getElementById('admItemProgramField').style.display = hasProgram ? 'block' : 'none';
        admOpenModal('admItemModal');
    }

    function admSaveItem() {
        var group = admEditState.group;
        if (!group) return;

        var hasProgram = group === 'orientation' || group === 'enrollment' || group === 'compliance';
        var payload = {
            sy: (document.getElementById('admItemSY').value || '').trim(),
            contents: (document.getElementById('admItemContents').value || '').trim()
        };
        if (hasProgram) {
            payload.program = (document.getElementById('admItemProgram').value || '').trim();
        }

        if (!payload.sy || !payload.contents || (hasProgram && !payload.program)) {
            alert('Please complete all required fields.');
            return;
        }

        if (admEditState.index === null) {
            admData[group].unshift(payload);
            admPager[group].page = 1;
        } else {
            admData[group][admEditState.index] = payload;
            admEditState.index = null;
        }

        admCloseModal('admItemModal');
        admRenderGroup(group);
    }

    function admDeleteItem(group, index) {
        var data = admData[group] || [];
        data.splice(index, 1);
        admPager[group].page = Math.min(admPager[group].page, admMaxPage(group));
        admCloseActionMenus();
        admRenderGroup(group);
    }

    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('[data-adm-menu-toggle]');
        if (toggle) {
            event.stopPropagation();
            admToggleActionMenu(toggle.getAttribute('data-adm-menu-toggle'), toggle);
            return;
        }

        if (!event.target.closest('.apst-dropdown')) {
            admCloseActionMenus();
        }
    });

    window.addEventListener('scroll', admCloseActionMenus, true);

    admRenderAll();
</script>
@endpush
