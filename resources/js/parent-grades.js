(function () {
    function initParentGradesFilters() {
        var form = document.getElementById('parentGradesFilterForm');
        if (!form) {
            return;
        }

        var filterIds = ['parentGradesChild', 'parentGradesSemester'];

        filterIds.forEach(function (id) {
            var field = document.getElementById(id);
            if (!field) {
                return;
            }

            field.addEventListener('change', function () {
                form.submit();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initParentGradesFilters);
        return;
    }

    initParentGradesFilters();
})();
