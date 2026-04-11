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
        var page = document.getElementById('classListPage');
        if (!page) {
            return;
        }

        var filterForm = document.getElementById('clFilterForm');
        if (!filterForm) {
            return;
        }

        var searchInput = filterForm.querySelector('[data-cl-auto-submit-search]');
        if (!searchInput) {
            return;
        }

        var submitSearch = debounce(function () {
            filterForm.submit();
        }, 280);

        searchInput.addEventListener('input', function () {
            submitSearch();
        });

        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                filterForm.submit();
            }
        });
    });
})();
