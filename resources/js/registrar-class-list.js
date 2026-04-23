(function () {
    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function debounce(fn, delay) {
        var timer = null;

        return function () {
            var args = arguments;

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function () {
                fn.apply(null, args);
            }, delay);
        };
    }

    function normalizeSemesterValue(value) {
        var normalized = String(value || '').trim().toLowerCase();

        if (normalized === '') {
            return '';
        }

        if (normalized.indexOf('summer') !== -1) {
            return 'Summer';
        }

        if (normalized.indexOf('second') !== -1 || normalized.indexOf('2nd') !== -1 || normalized === '2') {
            return 'Second';
        }

        if (normalized.indexOf('first') !== -1 || normalized.indexOf('1st') !== -1 || normalized === '1') {
            return 'First';
        }

        return '';
    }

    function semesterWeight(value) {
        var normalized = normalizeSemesterValue(value);

        if (normalized === 'First') {
            return 1;
        }

        if (normalized === 'Second') {
            return 2;
        }

        if (normalized === 'Summer') {
            return 3;
        }

        return 4;
    }

    function parseSemesterMap(raw) {
        if (!raw) {
            return {};
        }

        try {
            var parsed = JSON.parse(raw);
            if (parsed && typeof parsed === 'object') {
                return parsed;
            }
        } catch (error) {
            return {};
        }

        return {};
    }

    function emitListboxRefresh(selectElement) {
        if (!selectElement) {
            return;
        }

        if (window.registrarListboxSelect && typeof window.registrarListboxSelect.refresh === 'function') {
            window.registrarListboxSelect.refresh(selectElement);
            return;
        }

        var detail = { target: selectElement };

        if (typeof window.CustomEvent === 'function') {
            document.dispatchEvent(new CustomEvent('registrar:listbox:refresh', { detail: detail }));
            return;
        }

        var legacyEvent = document.createEvent('CustomEvent');
        legacyEvent.initCustomEvent('registrar:listbox:refresh', true, true, detail);
        document.dispatchEvent(legacyEvent);
    }

    function uniqueSemesters(list) {
        var values = [];

        (Array.isArray(list) ? list : []).forEach(function (item) {
            var normalized = normalizeSemesterValue(item);
            if (!normalized || values.indexOf(normalized) !== -1) {
                return;
            }

            values.push(normalized);
        });

        values.sort(function (left, right) {
            return semesterWeight(left) - semesterWeight(right);
        });

        return values;
    }

    function semesterListForYear(semesterMap, selectedSchoolYear) {
        var schoolYear = String(selectedSchoolYear || '').trim();

        if (schoolYear !== '' && semesterMap && Array.isArray(semesterMap[schoolYear])) {
            return uniqueSemesters(semesterMap[schoolYear]);
        }

        var all = [];
        if (!semesterMap || typeof semesterMap !== 'object') {
            return all;
        }

        Object.keys(semesterMap).forEach(function (year) {
            all = all.concat(Array.isArray(semesterMap[year]) ? semesterMap[year] : []);
        });

        return uniqueSemesters(all);
    }

    function syncSemesterOptions(schoolYearSelect, semesterSelect, semesterMap) {
        if (!schoolYearSelect || !semesterSelect) {
            return;
        }

        var activeSchoolYear = schoolYearSelect.value;
        var currentSemester = normalizeSemesterValue(semesterSelect.value);
        var semesterList = semesterListForYear(semesterMap, activeSchoolYear);

        var html = ['<option value="">All Semesters</option>'];
        semesterList.forEach(function (semester) {
            html.push('<option value="' + semester + '">' + semester + '</option>');
        });

        semesterSelect.innerHTML = html.join('');

        if (currentSemester && semesterList.indexOf(currentSemester) !== -1) {
            semesterSelect.value = currentSemester;
        } else {
            semesterSelect.value = '';
        }

        emitListboxRefresh(semesterSelect);
    }

    onReady(function () {
        var page = document.getElementById('classListPage');
        if (!page) {
            return;
        }

        var filterForm = document.getElementById('clFilterForm');
        if (!filterForm) {
            return;
        }

        var schoolYearSelect = document.getElementById('clSchoolYear');
        var semesterSelect = document.getElementById('clSemester');
        var semesterMap = parseSemesterMap(filterForm.getAttribute('data-semester-map') || '{}');

        if (schoolYearSelect && semesterSelect) {
            syncSemesterOptions(schoolYearSelect, semesterSelect, semesterMap);
            schoolYearSelect.addEventListener('change', function () {
                syncSemesterOptions(schoolYearSelect, semesterSelect, semesterMap);
            });
        }

        var searchInput = filterForm.querySelector('[data-cl-auto-submit-search]');
        if (!searchInput) {
            return;
        }

        var submitSearch = debounce(function () {
            filterForm.submit();
        }, 280);

        searchInput.addEventListener('input', function () {
            submitSearch();
        });

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                filterForm.submit();
            }
        });
    });
})();
