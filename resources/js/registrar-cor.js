document.addEventListener('DOMContentLoaded', function () {
    var printButton = document.getElementById('cor-registrar-print');
    var printedByInput = document.getElementById('cor-printed-by-input');
    var printedByValue = document.getElementById('cor-printed-by-value');
    var timePrintedValue = document.getElementById('cor-time-printed');
    var datePrintedValue = document.getElementById('cor-date-printed');

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function formatDate(date) {
        return pad(date.getMonth() + 1) + '/' + pad(date.getDate()) + '/' + date.getFullYear();
    }

    function formatTime(date) {
        var hours = date.getHours();
        var meridian = hours >= 12 ? 'pm' : 'am';
        var displayHour = hours % 12;

        if (displayHour === 0) {
            displayHour = 12;
        }

        return displayHour + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds()) + meridian;
    }

    function syncPrintedBy() {
        if (!printedByValue) {
            return;
        }

        var currentValue = printedByInput ? printedByInput.value : '';
        currentValue = currentValue ? currentValue.trim() : '';

        printedByValue.textContent = currentValue !== '' ? currentValue : 'Registrar User';
    }

    function updatePrintedTimestamp() {
        var now = new Date();

        if (timePrintedValue) {
            timePrintedValue.textContent = formatTime(now);
        }

        if (datePrintedValue) {
            datePrintedValue.textContent = formatDate(now);
        }
    }

    syncPrintedBy();
    updatePrintedTimestamp();

    if (printedByInput) {
        printedByInput.addEventListener('input', syncPrintedBy);
        printedByInput.addEventListener('change', syncPrintedBy);
    }

    window.addEventListener('beforeprint', function () {
        syncPrintedBy();
        updatePrintedTimestamp();
    });

    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        if (mediaQueryList && mediaQueryList.addListener) {
            mediaQueryList.addListener(function (event) {
                var isPrintMode = event && typeof event.matches === 'boolean'
                    ? event.matches
                    : mediaQueryList.matches;

                if (isPrintMode) {
                    syncPrintedBy();
                    updatePrintedTimestamp();
                }
            });
        }
    }

    if (printButton) {
        printButton.addEventListener('click', function () {
            syncPrintedBy();
            updatePrintedTimestamp();
            window.print();
        });
    }

    // Auto-submit COR student selector when user picks a student
    var studentSelect = document.getElementById('cor-student-id');
    if (studentSelect) {
        studentSelect.addEventListener('change', function () {
            // don't submit when no value selected
            if (!studentSelect.value) {
                return;
            }

            // If the select is inside a form, submit that form (GET reload)
            var form = studentSelect.form;
            if (form) {
                form.submit();
            }
        });
    }
});
