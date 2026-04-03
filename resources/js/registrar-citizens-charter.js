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
    var pageIndicator = document.getElementById('cc-page-indicator');

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

    function createPageButtons() {
        if (!pageNumbersWrap) {
            return;
        }

        pageNumbersWrap.innerHTML = '';

        pages.forEach(function (page, index) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'cc-page-btn cc-page-btn--num';
            button.textContent = String(index + 1);
            button.setAttribute('data-page-index', String(index));
            button.setAttribute('aria-label', 'Go to page ' + (index + 1));

            button.addEventListener('click', function () {
                currentIndex = index;
                updateView();
            });

            pageNumbersWrap.appendChild(button);
        });
    }

    function updateButtons() {
        if (prevButton) {
            prevButton.disabled = currentIndex === 0;
        }

        if (nextButton) {
            nextButton.disabled = currentIndex === pages.length - 1;
        }

        if (pageNumbersWrap) {
            var buttons = pageNumbersWrap.querySelectorAll('.cc-page-btn--num');
            Array.prototype.forEach.call(buttons, function (button, index) {
                var isActive = index === currentIndex;
                button.classList.toggle('is-active', isActive);
                if (isActive) {
                    button.setAttribute('aria-current', 'page');
                } else {
                    button.removeAttribute('aria-current');
                }
            });
        }

        if (pageIndicator) {
            pageIndicator.textContent = 'Page ' + (currentIndex + 1) + ' of ' + pages.length;
        }
    }

    function updatePages() {
        pages.forEach(function (page, index) {
            page.classList.toggle('is-active', index === currentIndex);
        });
    }

    function updateView() {
        updatePages();
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

    createPageButtons();
    updateView();
});
