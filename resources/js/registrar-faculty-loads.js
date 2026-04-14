(function () {
    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function debounce(fn, delay) {
        var timer = null;

        return function () {
            var args = arguments;

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(function () {
                fn.apply(null, args);
            }, delay);
        };
    }

    onReady(function () {
        document.querySelectorAll('.rfl-row[data-href]').forEach(function (row) {
            row.addEventListener('click', function (e) {
                var target = e.target;
                if (target && (target.tagName === 'A' || target.closest('a'))) return;
                var href = row.getAttribute('data-href');
                if (href) window.location.href = href;
            });
        });

        document.querySelectorAll('[data-rfl-auto-submit-search]').forEach(function (input) {
            var form = input.closest('form');
            if (!form) {
                return;
            }

            var submitSearch = debounce(function () {
                form.submit();
            }, 280);

            input.addEventListener('input', function () {
                submitSearch();
            });

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    form.submit();
                }
            });
        });

        var assignForm = document.querySelector('.rfl-assign-form');
        if (assignForm) {
            var subjectSelect = assignForm.querySelector('select[name="subject_id"]');
            var addSubjectButton = assignForm.querySelector('#rflAddSubjectBtn');
            var creditedUnitsInput = assignForm.querySelector('input[name="credited_tuition_units"]');
            var loadHoursInput = assignForm.querySelector('input[name="load_hours"]');

            function setAddButtonState() {
                if (!addSubjectButton || !subjectSelect) {
                    return;
                }

                if (!subjectSelect.options.length) {
                    addSubjectButton.disabled = true;
                    return;
                }

                addSubjectButton.disabled = !subjectSelect.value;
            }

            function validateDecimalField(field, label) {
                if (!field) {
                    return true;
                }

                var rawValue = (field.value || '').trim();
                field.setCustomValidity('');

                if (rawValue === '') {
                    return true;
                }

                if (!/^\d{1,3}(\.\d{1,2})?$/.test(rawValue)) {
                    field.setCustomValidity(label + ' must be between 0.00 and 999.99 with up to 2 decimal places.');
                    return false;
                }

                var numericValue = Number(rawValue);
                if (!isFinite(numericValue) || numericValue < 0 || numericValue > 999.99) {
                    field.setCustomValidity(label + ' must be between 0.00 and 999.99.');
                    return false;
                }

                return true;
            }

            if (subjectSelect) {
                subjectSelect.addEventListener('change', setAddButtonState);
            }

            [creditedUnitsInput, loadHoursInput].forEach(function (field) {
                if (!field) {
                    return;
                }

                field.addEventListener('input', function () {
                    field.setCustomValidity('');
                });

                field.addEventListener('blur', function () {
                    validateDecimalField(field, field.name === 'credited_tuition_units' ? 'Credited Tuition Units' : 'Load Hours');
                    field.reportValidity();
                });
            });

            assignForm.addEventListener('submit', function (event) {
                var hasErrors = false;

                if (subjectSelect && !subjectSelect.value) {
                    subjectSelect.setCustomValidity('Please select a subject from the available list before adding.');
                    subjectSelect.reportValidity();
                    hasErrors = true;
                } else if (subjectSelect) {
                    subjectSelect.setCustomValidity('');
                }

                if (!validateDecimalField(creditedUnitsInput, 'Credited Tuition Units')) {
                    if (creditedUnitsInput) {
                        creditedUnitsInput.reportValidity();
                    }
                    hasErrors = true;
                }

                if (!validateDecimalField(loadHoursInput, 'Load Hours')) {
                    if (loadHoursInput) {
                        loadHoursInput.reportValidity();
                    }
                    hasErrors = true;
                }

                if (hasErrors) {
                    event.preventDefault();
                }
            });

            setAddButtonState();
        }

        function buildPrintStylesHtml(configNode) {
            if (!configNode) {
                return '';
            }

            var cssUrls = [
                configNode.getAttribute('data-bootstrap-css') || '',
                configNode.getAttribute('data-print-css') || ''
            ].filter(function (url) {
                return url !== '';
            });

            return cssUrls.map(function (url) {
                return '<link rel="stylesheet" href="' + url + '">';
            }).join('');
        }

        function printTemplate(templateKey) {
            var template = document.getElementById('rflPrintTemplate-' + templateKey);
            var frame = document.getElementById('rflPrintFrame');
            var config = document.getElementById('rflPrintConfig');

            if (!template || !frame) {
                return;
            }

            var styles = buildPrintStylesHtml(config);
            var frameDoc = frame.contentDocument || (frame.contentWindow && frame.contentWindow.document);
            if (!frameDoc) {
                return;
            }

            var html = '' +
                '<!DOCTYPE html>' +
                '<html lang="en">' +
                '<head>' +
                '<meta charset="utf-8">' +
                '<meta name="viewport" content="width=device-width, initial-scale=1">' +
                '<title>Faculty Loads Print</title>' +
                styles +
                '</head>' +
                '<body class="rfl-strength-print-page">' +
                template.innerHTML +
                '</body>' +
                '</html>';

            frameDoc.open();
            frameDoc.write(html);
            frameDoc.close();

            setTimeout(function () {
                if (!frame.contentWindow) {
                    return;
                }

                frame.contentWindow.focus();
                frame.contentWindow.print();
            }, 400);
        }

        document.querySelectorAll('[data-rfl-print-template]').forEach(function (button) {
            button.addEventListener('click', function () {
                var templateKey = (button.getAttribute('data-rfl-print-template') || '').trim();
                if (templateKey === '') {
                    return;
                }

                printTemplate(templateKey);
            });
        });

        document.querySelectorAll('[data-rfl-print-trigger]').forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });
    });
})();
