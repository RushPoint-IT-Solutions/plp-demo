@extends('layouts.registrar')

@section('title', 'PLP - Forms')
@section('page-title', 'FORMS')
@section('body-class', 'page-forms-placeholder')

@section('content')
@php
    $placeholderSchoolYears = collect($schoolYears ?? [])->values()->all();
    $placeholderTerms = collect($termOptions ?? ['First', 'Second', 'Summer'])->values()->all();
    $placeholderSemesterMap = is_array($semesterMap ?? null) ? $semesterMap : [];
    $placeholderSelectedSchoolYear = (string) ($selectedSchoolYear ?? ($placeholderSchoolYears[0] ?? ''));
    $placeholderSelectedTerm = (string) ($selectedTerm ?? ($placeholderTerms[0] ?? 'First'));
@endphp
<div class="pf-page">
    <div class="rep-dashboard">
        <div class="rep-top-row">
            <div class="rep-sys-card rep-sys-card--compact" id="formsSystemConfigCard" data-semester-map='@json($placeholderSemesterMap)'>
                <div class="rep-sys-title">System Configuration</div>
                <div class="rep-sys-grid">
                    <div class="rep-sys-field">
                        <label class="app-filter-label">School Year:</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'formsSchoolYear',
                            'name' => 'formsSchoolYear',
                            'options' => $placeholderSchoolYears,
                            'selected' => $placeholderSelectedSchoolYear,
                            'placeholder' => '- Select School Year -',
                        ])
                    </div>
                    <div class="rep-sys-field">
                        <label class="app-filter-label">Term:</label>
                        @include('registrar.components.listbox-select', [
                            'id' => 'formsTerm',
                            'name' => 'formsTerm',
                            'options' => $placeholderTerms,
                            'selected' => $placeholderSelectedTerm,
                            'placeholder' => '- Select Term -',
                        ])
                    </div>
                    <div class="rep-sys-action">
                        <button class="req-btn-save" id="formsSetConfigBtn" type="button" style="height:36px; min-width: 100px; padding:0 24px; font-weight:700;">Set</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.3rem;">Official Forms</div>
            <p style="font-size:0.82rem; color:#4b5563; margin: 2px 0 12px;">
                Initial digital placeholders are available. Final layout/content will be updated once your official hardcopy forms are uploaded.
            </p>
            <div class="rep-grid-3">
                <button class="rep-btn" type="button" onclick="openFormsModal('Application Form')">Application Form</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Enrollment Form')">Enrollment Form</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Student Information Sheet')">Student Information Sheet</button>

                <button class="rep-btn" type="button" onclick="openFormsModal('Request Slip')">Request Slip</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Clearance Form')">Clearance Form</button>
                <button class="rep-btn" type="button" onclick="openFormsModal('Release Form')">Release Form</button>
            </div>
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="formsInfoModal" style="display:none;">
    <div class="req-modal-box" style="width: 460px;">
        <h3 class="req-modal-title" id="formsModalTitle" style="color:#006837; font-size: 1rem;">FORM</h3>
        <p style="font-size: 0.82rem; color: #4b5563; margin-bottom: 16px; text-align: center;">
            Placeholder template is currently active. Exact format will follow your official hardcopy sample.
        </p>

        <div class="req-modal-fields" style="display:flex; flex-direction:column; gap:12px;">
            <div class="req-modal-field-group">
                <label class="req-modal-label">Current Version</label>
                <input type="text" class="req-modal-input" value="Draft Placeholder" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Status</label>
                <input type="text" class="req-modal-input" value="Waiting for hardcopy upload" readonly>
            </div>
            <div class="req-modal-field-group">
                <label class="req-modal-label">Notes</label>
                <textarea class="req-modal-input" rows="2" readonly>Once you upload the sample hardcopy, this form will be converted to the final exact design.</textarea>
            </div>
        </div>

        <div class="req-modal-actions" style="margin-top:20px; justify-content:center;">
            <button type="button" class="req-btn-cancel" onclick="closeFormsModal()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-listbox-select.js') }}?v={{ file_exists(public_path('js/registrar-listbox-select.js')) ? filemtime(public_path('js/registrar-listbox-select.js')) : time() }}"></script>
<script>
    function formsRefreshListbox(selectElement) {
        if (!selectElement) {
            return;
        }

        if (window.registrarListboxSelect && typeof window.registrarListboxSelect.refresh === 'function') {
            window.registrarListboxSelect.refresh(selectElement);
            return;
        }

        if (typeof window.CustomEvent === 'function') {
            document.dispatchEvent(new CustomEvent('registrar:listbox:refresh', { detail: { target: selectElement } }));
        }
    }

    function formsNormalizeSemesterLabel(value) {
        var normalized = String(value || '').trim().toLowerCase();
        var aliases = {
            'first': 'First',
            '1st': 'First',
            '1st semester': 'First',
            'first semester': 'First',
            'second': 'Second',
            '2nd': 'Second',
            '2nd semester': 'Second',
            'second semester': 'Second',
            'summer': 'Summer',
            'summer semester': 'Summer'
        };

        return aliases[normalized] || '';
    }

    function formsParseSemesterMap(raw) {
        try {
            var parsed = JSON.parse(raw || '{}');
            return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
        } catch (error) {
            return {};
        }
    }

    function formsTermsForYear(semesterMap, schoolYear) {
        var key = String(schoolYear || '').trim();
        var rawTerms = key && Array.isArray(semesterMap[key]) ? semesterMap[key] : [];
        var normalizedTerms = rawTerms.map(function(item) {
            return formsNormalizeSemesterLabel(item);
        }).filter(function(item, index, list) {
            return item && list.indexOf(item) === index;
        });

        return normalizedTerms.length ? normalizedTerms : ['First', 'Second', 'Summer'];
    }

    function formsSyncTerms(semesterMap, preferredTerm) {
        var schoolYearSelect = document.getElementById('formsSchoolYear');
        var termSelect = document.getElementById('formsTerm');
        if (!schoolYearSelect || !termSelect) {
            return;
        }

        var terms = formsTermsForYear(semesterMap, schoolYearSelect.value);
        var selectedTerm = formsNormalizeSemesterLabel(preferredTerm || termSelect.value);

        termSelect.innerHTML = terms.map(function(term) {
            return '<option value="' + term + '">' + term + '</option>';
        }).join('');

        if (selectedTerm && terms.indexOf(selectedTerm) !== -1) {
            termSelect.value = selectedTerm;
        }

        if (!termSelect.value && terms.length) {
            termSelect.value = terms[0];
        }

        formsRefreshListbox(termSelect);
    }

    function formsBindSystemConfig() {
        var configCard = document.getElementById('formsSystemConfigCard');
        var schoolYearSelect = document.getElementById('formsSchoolYear');
        var termSelect = document.getElementById('formsTerm');
        var setButton = document.getElementById('formsSetConfigBtn');
        if (!configCard || !schoolYearSelect || !termSelect || !setButton) {
            return;
        }

        var semesterMap = formsParseSemesterMap(configCard.getAttribute('data-semester-map'));
        formsSyncTerms(semesterMap, termSelect.value);

        schoolYearSelect.addEventListener('change', function() {
            formsSyncTerms(semesterMap, '');
        });

        setButton.addEventListener('click', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('school_year', schoolYearSelect.value || '');
            url.searchParams.set('term', termSelect.value || '');
            window.location.assign(url.toString());
        });
    }

    function openFormsModal(title) {
        var modal = document.getElementById('formsInfoModal');
        var heading = document.getElementById('formsModalTitle');
        if (!modal || !heading) return;
        heading.textContent = String(title || 'FORM').toUpperCase();
        modal.style.display = 'flex';
    }

    function closeFormsModal() {
        var modal = document.getElementById('formsInfoModal');
        if (!modal) return;
        modal.style.display = 'none';
    }

    window.addEventListener('click', function(event) {
        if (event.target && event.target.id === 'formsInfoModal') {
            closeFormsModal();
        }
    });

    formsBindSystemConfig();
</script>
@endpush
