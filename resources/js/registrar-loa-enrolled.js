document.addEventListener('DOMContentLoaded', function () {
    var studentSelect = document.getElementById('loae-student-id');
    var printButton = document.getElementById('loae-print-btn');
    var stage = document.querySelector('.loae-a4-stage');
    var sheet = document.querySelector('.a4-wrapper');
    var sentenceInputs = document.querySelectorAll('.loae-sentence-field .loae-inline');

    function autoResizeSentenceInput(input) {
        input.style.width = ((input.value.length || 1) + 1) + 'ch';
    }

    function syncA4Scale() {
        if (!stage || !sheet) {
            return;
        }

        var targetWidth = 816;
        var availableWidth = stage.clientWidth;
        var scale = 1;

        if (availableWidth > 0 && availableWidth < targetWidth) {
            scale = availableWidth / targetWidth;
        }

        sheet.style.setProperty('--loae-zoom', scale.toFixed(4));
        sheet.style.setProperty('--loae-scale', scale.toFixed(4));
    }

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

    if (sentenceInputs.length) {
        sentenceInputs.forEach(function (input) {
            autoResizeSentenceInput(input);

            input.addEventListener('input', function () {
                autoResizeSentenceInput(input);
            });
        });
    }

    window.addEventListener('resize', syncA4Scale);
    window.addEventListener('orientationchange', syncA4Scale);

    window.addEventListener('beforeprint', function () {
        if (!sheet) {
            return;
        }

        sheet.style.setProperty('--loae-zoom', '1');
        sheet.style.setProperty('--loae-scale', '1');
    });

    window.addEventListener('afterprint', syncA4Scale);

    syncA4Scale();
});
