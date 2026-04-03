document.addEventListener('DOMContentLoaded', function () {
    var printButton = document.getElementById('cor-registrar-print');
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

    function makeEditable() {
        if (!printedByValue) {
            return;
        }

        printedByValue.setAttribute('contenteditable', 'true');
        printedByValue.setAttribute('spellcheck', 'false');

        printedByValue.addEventListener('blur', function () {
            var currentValue = printedByValue.textContent.trim();
            if (currentValue === '') {
                var defaultValue = printedByValue.getAttribute('data-default') || 'Registrar User';
                printedByValue.textContent = defaultValue;
            }
        });

        printedByValue.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                printedByValue.blur();
            }
        });
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

    makeEditable();
    updatePrintedTimestamp();

    window.addEventListener('beforeprint', function () {
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
                    updatePrintedTimestamp();
                }
            });
        }
    }

    if (printButton) {
        printButton.addEventListener('click', function () {
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
