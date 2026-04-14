(function () {
    function onReady(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
            return;
        }

        callback();
    }

    function schedule(callback) {
        if (typeof window.requestAnimationFrame === 'function') {
            window.requestAnimationFrame(callback);
            return;
        }

        setTimeout(callback, 0);
    }

    onReady(function () {
        var wraps = Array.prototype.slice.call(document.querySelectorAll('.sm-report-table-wrap'));
        if (!wraps.length) {
            return;
        }

        var resizeObserver = null;

        function updateWrapOverflow(wrap) {
            var table = wrap.querySelector('.sm-report-table');
            if (!table) {
                wrap.classList.remove('is-scrollable');
                wrap.setAttribute('data-report-scrollable', '0');
                return;
            }

            var wrapWidth = wrap.getBoundingClientRect().width;
            var tableWidth = table.getBoundingClientRect().width;
            var shouldScroll = tableWidth > wrapWidth + 4;

            wrap.classList.toggle('is-scrollable', shouldScroll);
            wrap.setAttribute('data-report-scrollable', shouldScroll ? '1' : '0');
        }

        function refreshAll() {
            wraps.forEach(updateWrapOverflow);
        }

        function refreshSoon() {
            schedule(refreshAll);
        }

        if (typeof ResizeObserver === 'function') {
            resizeObserver = new ResizeObserver(refreshSoon);
            wraps.forEach(function (wrap) {
                resizeObserver.observe(wrap);

                var table = wrap.querySelector('.sm-report-table');
                if (table) {
                    resizeObserver.observe(table);
                }
            });
        }

        window.addEventListener('resize', refreshSoon);

        if (document.fonts && typeof document.fonts.ready === 'object') {
            document.fonts.ready.then(refreshSoon).catch(function () {
                refreshSoon();
            });
        }

        refreshSoon();
    });
})();