document.addEventListener('DOMContentLoaded', function () {
    var pageRoot = document.getElementById('citizens-charter-page');

    if (!pageRoot) {
        return;
    }

    var stage = document.getElementById('cc-stage');
    var pages = Array.prototype.slice.call(pageRoot.querySelectorAll('.cc-sheet'));
    var prevButton = document.getElementById('cc-prev');
    var nextButton = document.getElementById('cc-next');
    var pageNumbersWrap = document.getElementById('cc-page-numbers');

    if (!pages.length || !stage) {
        return;
    }

    var currentIndex = 0;

    function targetSheetWidth() {
        return 816;
    }

    function syncScale() {
        var availableWidth = stage.clientWidth;
        var scale = 1;

        if (availableWidth > 0 && availableWidth < targetSheetWidth()) {
            scale = availableWidth / targetSheetWidth();
        }

        pageRoot.style.setProperty('--cc-zoom', scale.toFixed(4));
        pageRoot.style.setProperty('--cc-scale', scale.toFixed(4));
    }

    function buildVisiblePageTokens(totalPages, activePage) {
        if (totalPages <= 11) {
            var all = [];
            for (var i = 1; i <= totalPages; i += 1) {
                all.push(i);
            }
            return all;
        }

        var visibleMap = {};

        function addRange(start, end) {
            for (var n = start; n <= end; n += 1) {
                if (n >= 1 && n <= totalPages) {
                    visibleMap[n] = true;
                }
            }
        }

        addRange(1, 2);
        addRange(totalPages - 3, totalPages);
        addRange(activePage - 2, activePage + 2);

        var pagesSorted = Object.keys(visibleMap)
            .map(function (value) {
                return parseInt(value, 10);
            })
            .sort(function (a, b) {
                return a - b;
            });

        var tokens = [];
        var previous = 0;

        pagesSorted.forEach(function (pageNumber) {
            if (previous && pageNumber - previous > 1) {
                if (pageNumber - previous === 2) {
                    tokens.push(previous + 1);
                } else {
                    tokens.push('ellipsis');
                }
            }

            tokens.push(pageNumber);
            previous = pageNumber;
        });

        return tokens;
    }

    function renderPageButtons() {
        if (!pageNumbersWrap) {
            return;
        }

        var activePage = currentIndex + 1;
        var tokens = buildVisiblePageTokens(pages.length, activePage);
        pageNumbersWrap.innerHTML = '';

        tokens.forEach(function (token) {
            var item = document.createElement('li');

            if (token === 'ellipsis') {
                item.className = 'page-item disabled cc-page-item cc-page-item--ellipsis';

                var ellipsisSpan = document.createElement('span');
                ellipsisSpan.className = 'page-link cc-page-link';
                ellipsisSpan.textContent = '...';

                item.appendChild(ellipsisSpan);
                pageNumbersWrap.appendChild(item);
                return;
            }

            var targetIndex = token - 1;
            var isActive = targetIndex === currentIndex;

            item.className = 'page-item cc-page-item cc-page-item--num' + (isActive ? ' active' : '');

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link cc-page-link';
            button.textContent = String(token);
            button.setAttribute('data-page-index', String(targetIndex));
            button.setAttribute('aria-label', 'Go to page ' + token);

            if (isActive) {
                button.setAttribute('aria-current', 'page');
            }

            button.addEventListener('click', function () {
                currentIndex = targetIndex;
                updateView();
            });

            item.appendChild(button);
            pageNumbersWrap.appendChild(item);
        });
    }

    function updateButtons() {
        if (prevButton) {
            var prevDisabled = currentIndex === 0;
            prevButton.disabled = prevDisabled;
            prevButton.classList.toggle('disabled', prevDisabled);
        }

        if (nextButton) {
            var nextDisabled = currentIndex === pages.length - 1;
            nextButton.disabled = nextDisabled;
            nextButton.classList.toggle('disabled', nextDisabled);
        }
    }

    function updatePages() {
        pages.forEach(function (page, index) {
            page.classList.toggle('is-active', index === currentIndex);
        });
    }

    function updateView() {
        updatePages();
        renderPageButtons();
        updateButtons();
        syncScale();
    }

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            if (currentIndex <= 0) {
                return;
            }

            currentIndex -= 1;
            updateView();
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            if (currentIndex >= pages.length - 1) {
                return;
            }

            currentIndex += 1;
            updateView();
        });
    }

    window.addEventListener('resize', syncScale);
    window.addEventListener('orientationchange', syncScale);

    window.addEventListener('beforeprint', function () {
        pageRoot.style.setProperty('--cc-zoom', '1');
        pageRoot.style.setProperty('--cc-scale', '1');
    });

    window.addEventListener('afterprint', syncScale);

    updateView();
});
