(function () {
    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
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
    });
})();
