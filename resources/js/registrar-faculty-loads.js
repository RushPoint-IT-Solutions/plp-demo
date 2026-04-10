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
    });
})();
