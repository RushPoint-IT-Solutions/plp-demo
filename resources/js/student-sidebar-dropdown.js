document.addEventListener('DOMContentLoaded', function () {
    function syncLaoPrintCheckboxState() {
        document.querySelectorAll('.loa-check').forEach(function (label) {
            var checkbox = label.querySelector('input[type="checkbox"]');
            if (!checkbox) {
                return;
            }

            label.setAttribute('data-print-checked', checkbox.checked ? 'true' : 'false');
        });
    }

    function syncPrintableInputState() {
        document.querySelectorAll('.acd-inline-input').forEach(function (input) {
            var hasValue = input.value && input.value.trim().length > 0;
            input.classList.toggle('has-value', hasValue);
        });
    }

    function fitAcdFormCanvas() {
        var page = document.querySelector('.acd-page');
        var canvas = document.querySelector('.acd-canvas');
        var form = document.querySelector('.acd-form');
        if (!page || !canvas || !form) {
            return;
        }

        var baseWidth = form.offsetWidth;

        var availableWidth = Math.max(page.clientWidth - 12, 320);
        var scale = Math.min(1, availableWidth / baseWidth);

        canvas.style.setProperty('--acd-scale', scale.toFixed(4));
        canvas.style.width = (baseWidth * scale) + 'px';
        canvas.style.minWidth = (baseWidth * scale) + 'px';
        canvas.style.height = 'auto';
    }

    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();

            var dropdown = this.closest('.sidebar-dropdown');
            if (!dropdown) {
                return;
            }

            dropdown.classList.toggle('open');
        });
    });

    var printBtn = document.getElementById('acd-print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            syncLaoPrintCheckboxState();
            syncPrintableInputState();
            window.print();
        });
    }

    window.addEventListener('beforeprint', function () {
        syncLaoPrintCheckboxState();
        syncPrintableInputState();
    });

    document.addEventListener('input', function (event) {
        var target = event.target;
        if (!target || target.matches('.acd-inline-input') === false) {
            return;
        }

        syncPrintableInputState();
    });

    document.addEventListener('change', function (event) {
        var target = event.target;
        if (!target || target.matches('.loa-check input[type="checkbox"]') === false) {
            if (target && target.matches('.acd-inline-input')) {
                syncPrintableInputState();
            }
            return;
        }

        syncLaoPrintCheckboxState();
    });

    fitAcdFormCanvas();
    window.addEventListener('resize', fitAcdFormCanvas);
    syncLaoPrintCheckboxState();
    syncPrintableInputState();
});
