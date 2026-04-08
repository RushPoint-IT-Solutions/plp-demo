(function () {
    function hasAutoPrintFlag() {
        return /(^|[?&])autoprint=1(&|$)/.test(window.location.search);
    }

    var printNowBtn = document.getElementById('printNowBtn');
    if (printNowBtn) {
        printNowBtn.addEventListener('click', function () {
            window.print();
        });
    }

    if (hasAutoPrintFlag()) {
        window.setTimeout(function () {
            window.print();
        }, 200);
    }
})();
