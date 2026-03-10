(function () {
    var tabs   = document.querySelectorAll('.pv-tab');
    var panels = document.querySelectorAll('.pv-panel');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var target = this.getAttribute('data-target');

            tabs.forEach(function (t) { t.classList.remove('active'); });
            panels.forEach(function (p) { p.classList.remove('active'); });

            this.classList.add('active');
            var panel = document.getElementById(target);
            if (panel) panel.classList.add('active');
        });
    });
})();
