document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('rf137a-page');
    var sheet = page ? page.querySelector('.rf137a-sheet') : null;
    var sentenceInputs = page ? page.querySelectorAll('.rf137a-sentence-field .rf137a-inline-input') : [];

    function autoResizeSentenceInput(input) {
        var nextWidth = Math.max((input.value.length || 1) + 2, 18);
        input.style.width = nextWidth + 'ch';
    }

    function syncA4Scale() {
        if (!page || !sheet) {
            return;
        }

        var targetWidth = 794;
        var availableWidth = page.clientWidth;
        var scale = 1;

        if (availableWidth > 0 && availableWidth < targetWidth) {
            scale = availableWidth / targetWidth;
        }

        sheet.style.setProperty('--rf137a-zoom', scale.toFixed(4));
        sheet.style.setProperty('--rf137a-scale', scale.toFixed(4));
    }

    if (sentenceInputs.length) {
        sentenceInputs.forEach(function (input) {
            autoResizeSentenceInput(input);
            input.addEventListener('input', function () {
                autoResizeSentenceInput(input);
            });
        });
    }

    var printBtn = page ? page.querySelector('#rf137a-print-btn') : null;
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            try {
                // Give immediate visual feedback and rely on beforeprint/afterprint to reset
                printBtn.disabled = true;
                printBtn.classList.add('is-printing');
                window.print();
            } catch (e) {
                console.error('Print failed', e);
                try { printBtn.disabled = false; printBtn.classList.remove('is-printing'); } catch (er) {}
            }
        });
    }

    window.addEventListener('resize', syncA4Scale);
    window.addEventListener('orientationchange', syncA4Scale);

    window.addEventListener('beforeprint', function () {
        if (!sheet) {
            return;
        }

        sheet.style.setProperty('--rf137a-zoom', '1');
        sheet.style.setProperty('--rf137a-scale', '1');

        if (printBtn) {
            try { printBtn.disabled = true; printBtn.classList.add('is-printing'); } catch (e) {}
        }
    });

    window.addEventListener('afterprint', function () {
        try { syncA4Scale(); } catch (e) {}
        if (printBtn) {
            try { printBtn.disabled = false; printBtn.classList.remove('is-printing'); } catch (e) {}
        }
    });

    syncA4Scale();
});
