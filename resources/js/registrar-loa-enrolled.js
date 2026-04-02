document.addEventListener('DOMContentLoaded', function () {
    var studentSelect = document.getElementById('loae-student-id');
    var printButton = document.getElementById('loae-print-btn');

    if (studentSelect) {
        studentSelect.addEventListener('change', function () {
            if (!studentSelect.value) {
                return;
            }

            var form = studentSelect.form;
            if (form) {
                form.submit();
            }
        });
    }

    if (printButton) {
        printButton.addEventListener('click', function () {
            window.print();
        });
    }
});
