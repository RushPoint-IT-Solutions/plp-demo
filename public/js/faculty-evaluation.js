document.addEventListener('DOMContentLoaded', function () {
    var openLinks = document.querySelectorAll('.faculty-eval-open');
    var subjectSelect = document.getElementById('evalSubjectSelect');
    var modalElement = document.getElementById('facultyEvaluationModal');
    var modalBody = document.getElementById('evalModalBody');

    if (!modalElement) return;

    var modal = new bootstrap.Modal(modalElement);

    function interpretationFromScore(score) {
        if (score >= 4.0) return 'Very Satisfactory';
        if (score >= 3.0) return 'Satisfactory';
        return 'Needs Improvement';
    }

    openLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();

            var evalId = link.getAttribute('data-eval-id');
            var detail = (facultyEvaluationDetails || {})[evalId];
            if (!detail) return;

            var criteria = detail.criteria || [];
            var rows = '';

            criteria.forEach(function (item) {
                rows += '<tr>'
                    + '<td>' + item.label + '</td>'
                    + '<td class="score-cell text-center">' + Number(item.score).toFixed(2) + '</td>'
                    + '<td>' + interpretationFromScore(Number(item.score)) + '</td>'
                    + '</tr>';
            });

            rows += '<tr class="faculty-eval-overall-row">'
                + '<td></td>'
                + '<td class="avg-cell score-cell text-center">' + Number(detail.overall || 0).toFixed(2) + '</td>'
                + '<td>' + (detail.interpretation || '') + '</td>'
                + '</tr>';

            modalBody.innerHTML = rows;

            modal.show();
        });
    });

    if (subjectSelect) {
        subjectSelect.addEventListener('change', function () {
            var next = subjectSelect.value;
            var targetUrl = new URL(window.location.href);

            if (!next) {
                targetUrl.searchParams.delete('subject_id');
            } else {
                targetUrl.searchParams.set('subject_id', next);
            }

            window.location.href = targetUrl.toString();
        });
    }
});
