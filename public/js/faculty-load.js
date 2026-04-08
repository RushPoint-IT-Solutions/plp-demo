document.addEventListener('DOMContentLoaded', function () {
    var schoolYearSelect = document.getElementById('flSchoolYear');
    var semesterSelect = document.getElementById('flSemester');
    var displayBtn = document.getElementById('flDisplayBtn');
    var yearLabel = document.getElementById('flYearLabel');
    var tbody = document.getElementById('facultyLoadBody');

    if (!schoolYearSelect || !semesterSelect || !displayBtn || !yearLabel || !tbody) {
        return;
    }

    function applyFilters() {
        var year = schoolYearSelect.value || '';
        var semester = semesterSelect.value || '';
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        var visibleCount = 0;

        rows.forEach(function (row) {
            if (row.classList.contains('faculty-load-empty-row')) {
                row.parentNode.removeChild(row);
            }
        });

        rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

        rows.forEach(function (row) {
            var rowYear = row.getAttribute('data-school-year') || '';
            var rowSemester = row.getAttribute('data-semester') || '';

            var matchYear = !year || rowYear === year;
            var matchSemester = !semester || rowSemester === semester;
            var show = matchYear && matchSemester;

            row.style.display = show ? '' : 'none';
            if (show) {
                visibleCount += 1;
            }
        });

        yearLabel.textContent = year || 'All School Years';

        if (!visibleCount) {
            var tr = document.createElement('tr');
            tr.className = 'faculty-load-empty-row';
            tr.innerHTML = '<td colspan="7" class="text-center text-muted py-4">No subjects found.</td>';
            tbody.appendChild(tr);
        }
    }

    displayBtn.addEventListener('click', applyFilters);
    schoolYearSelect.addEventListener('change', function () {
        yearLabel.textContent = schoolYearSelect.value || 'All School Years';
    });

    applyFilters();
});
