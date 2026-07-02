(function () {
    function injectStyles() {
        if (document.getElementById('reportStudentAutocompleteStyles')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'reportStudentAutocompleteStyles';
        style.textContent = [
            '.rsa-wrap{position:relative;}',
            '.rsa-results{position:absolute;z-index:3000;top:calc(100% + 4px);left:0;right:0;max-height:220px;overflow:auto;border:1px solid #cbd5d1;border-radius:7px;background:#fff;box-shadow:0 12px 26px rgba(15,59,36,.14);display:none;}',
            '.rsa-results.is-open{display:block;}',
            '.rsa-option{width:100%;border:0;background:#fff;text-align:left;padding:9px 11px;cursor:pointer;color:#1f2937;font-size:.84rem;}',
            '.rsa-option strong{display:block;color:#143521;font-size:.86rem;}',
            '.rsa-option span{display:block;color:#6b7280;font-size:.75rem;margin-top:2px;}',
            '.rsa-option.is-active,.rsa-option:hover{background:#edf7f0;}',
            '.rsa-empty{padding:10px 11px;color:#6b7280;font-size:.82rem;font-weight:600;}'
        ].join('');
        document.head.appendChild(style);
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function debounce(fn, wait) {
        var timer = null;
        return function () {
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(null, args);
            }, wait);
        };
    }

    function setup(input) {
        if (!input || input.getAttribute('data-rsa-ready') === '1') {
            return;
        }

        input.setAttribute('data-rsa-ready', '1');
        input.setAttribute('autocomplete', 'off');

        var url = input.getAttribute('data-student-search-url');
        if (!url) {
            return;
        }

        var hidden = input.getAttribute('data-student-id-target')
            ? document.getElementById(input.getAttribute('data-student-id-target'))
            : null;
        var results = document.getElementById(input.getAttribute('data-student-results'));
        var selectedLabelTarget = input.getAttribute('data-student-label-target')
            ? document.getElementById(input.getAttribute('data-student-label-target'))
            : null;

        if (!results) {
            results = document.createElement('div');
            results.className = 'rsa-results';
            input.insertAdjacentElement('afterend', results);
        }

        var items = [];
        var activeIndex = -1;
        var lastQuery = '';

        function close() {
            results.classList.remove('is-open');
            activeIndex = -1;
        }

        function setActive(index) {
            activeIndex = index;
            Array.prototype.forEach.call(results.querySelectorAll('.rsa-option'), function (button, buttonIndex) {
                button.classList.toggle('is-active', buttonIndex === activeIndex);
                if (buttonIndex === activeIndex) {
                    button.scrollIntoView({ block: 'nearest' });
                }
            });
        }

        function choose(item) {
            if (!item) {
                return;
            }

            var label = item.label || [item.student_no, item.name].filter(Boolean).join(' - ');
            var fillKey = input.getAttribute('data-student-fill-key');
            input.value = fillKey && item[fillKey] ? item[fillKey] : label;
            input.setAttribute('data-selected-student-label', label);
            if (hidden) {
                hidden.value = item.id || '';
            }
            if (selectedLabelTarget) {
                selectedLabelTarget.value = label;
            }
            close();
        }

        function render(message) {
            if (message) {
                results.innerHTML = '<div class="rsa-empty">' + escapeHtml(message) + '</div>';
                results.classList.add('is-open');
                return;
            }

            if (!items.length) {
                results.innerHTML = '<div class="rsa-empty">No matching students found.</div>';
                results.classList.add('is-open');
                return;
            }

            results.innerHTML = items.map(function (item, index) {
                return '<button type="button" class="rsa-option" data-rsa-index="' + index + '">' +
                    '<strong>' + escapeHtml(item.student_no || '-') + '</strong>' +
                    '<span>' + escapeHtml(item.name || item.label || '-') + '</span>' +
                    '</button>';
            }).join('');
            results.classList.add('is-open');
            setActive(0);
        }

        var runSearch = debounce(function () {
            var query = input.value.trim();
            if (hidden) {
                hidden.value = '';
            }
            input.removeAttribute('data-selected-student-label');

            if (query.length < 2) {
                items = [];
                close();
                return;
            }

            lastQuery = query;
            render('Searching students...');

            fetch(url + '?q=' + encodeURIComponent(query), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (response) {
                return response.json();
            }).then(function (payload) {
                if (query !== lastQuery) {
                    return;
                }
                items = Array.isArray(payload.results) ? payload.results : [];
                render();
            }).catch(function () {
                items = [];
                render('Unable to search students.');
            });
        }, 180);

        input.addEventListener('input', runSearch);
        input.addEventListener('focus', function () {
            if (items.length) {
                render();
            }
        });
        input.addEventListener('keydown', function (event) {
            if (!results.classList.contains('is-open')) {
                return;
            }
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActive(Math.min(activeIndex + 1, items.length - 1));
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(Math.max(activeIndex - 1, 0));
            } else if (event.key === 'Enter') {
                if (activeIndex >= 0 && items[activeIndex]) {
                    event.preventDefault();
                    choose(items[activeIndex]);
                }
            } else if (event.key === 'Escape') {
                close();
            }
        });

        results.addEventListener('mousedown', function (event) {
            var button = event.target.closest('.rsa-option');
            if (!button) {
                return;
            }
            event.preventDefault();
            choose(items[parseInt(button.getAttribute('data-rsa-index'), 10)]);
        });

        document.addEventListener('click', function (event) {
            if (event.target !== input && !results.contains(event.target)) {
                close();
            }
        });
    }

    function init() {
        injectStyles();
        document.querySelectorAll('[data-student-autocomplete="reports"]').forEach(setup);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
