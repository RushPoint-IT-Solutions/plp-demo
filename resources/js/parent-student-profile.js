(function () {
    function initParentStudentProfileFilters() {
        var form = document.getElementById('parentStudentProfileFilterForm');
        var childField = document.getElementById('parentStudentProfileChild');

        if (!form || !childField) {
            return;
        }

        childField.addEventListener('change', function () {
            form.submit();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initParentStudentProfileFilters);
        return;
    }

    initParentStudentProfileFilters();
})();
