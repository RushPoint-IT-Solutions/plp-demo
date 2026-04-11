(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('regHelpSearchInput');
    var topicCards = Array.prototype.slice.call(document.querySelectorAll('.reg-help-topic-card'));

    if (searchInput && topicCards.length) {
      searchInput.addEventListener('input', function () {
        var query = (searchInput.value || '').toLowerCase().trim();
        topicCards.forEach(function (card) {
          var text = (card.getAttribute('data-help-keywords') || '').toLowerCase();
          var show = query === '' || text.indexOf(query) !== -1;
          card.style.display = show ? '' : 'none';
        });
      });
    }

    document.querySelectorAll('.reg-help-faq-item').forEach(function (item) {
      var btn = item.querySelector('.reg-help-faq-q');
      if (!btn) return;

      btn.addEventListener('click', function () {
        var expanded = item.getAttribute('data-expanded') === 'true';
        item.setAttribute('data-expanded', expanded ? 'false' : 'true');
      });
    });
  });
})();
