(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var card = document.querySelector('.reg-announce-card');
    var scheduleCard = document.querySelector('.reg-schedule-card');
    var dashboardRoot = document.querySelector('.reg-dashboard');
    if (!card && !scheduleCard) return;

    var seeAll = card ? card.querySelector('.reg-see-all') : null;
    var menuBackdrop = document.createElement('div');
    menuBackdrop.className = 'reg-menu-backdrop';
    document.body.appendChild(menuBackdrop);

    menuBackdrop.addEventListener('click', function () {
      closeAllMenus();
    });

    var editModal = document.getElementById('regEditModal');
    var editModalTitle = document.getElementById('regEditModalTitle');
    var editInput = document.getElementById('regEditModalInput');
    var editCancelBtn = document.getElementById('regEditCancelBtn');
    var editSaveBtn = document.getElementById('regEditSaveBtn');
    var activeTitleEl = null;

    function openEditModal(titleEl, label) {
      if (!editModal || !editInput || !titleEl) return;
      activeTitleEl = titleEl;
      editModalTitle.textContent = label;
      editInput.value = titleEl.textContent.trim();
      editModal.style.display = 'flex';
      editModal.setAttribute('aria-hidden', 'false');
      window.setTimeout(function () {
        editInput.focus();
        editInput.select();
      }, 0);
    }

    function closeEditModal() {
      if (!editModal) return;
      editModal.style.display = 'none';
      editModal.setAttribute('aria-hidden', 'true');
      activeTitleEl = null;
    }

    if (editCancelBtn) {
      editCancelBtn.addEventListener('click', closeEditModal);
    }

    if (editSaveBtn) {
      editSaveBtn.addEventListener('click', function () {
        if (!activeTitleEl || !editInput) return;
        var nextValue = (editInput.value || '').trim();
        if (nextValue !== '') {
          activeTitleEl.textContent = nextValue;
        }
        closeEditModal();
      });
    }

    if (editModal) {
      editModal.addEventListener('click', function (event) {
        if (event.target === editModal) {
          closeEditModal();
        }
      });
    }

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && editModal && editModal.style.display === 'flex') {
        closeEditModal();
      }
    });

    function setPinned(item, pinned) {
      item.classList.toggle('pinned', pinned);
      var btn = item.querySelector('.reg-pin-btn');
      if (!btn) return;
      btn.classList.toggle('active', pinned);
      btn.setAttribute('aria-pressed', pinned ? 'true' : 'false');
      btn.title = pinned ? 'Pinned' : 'Pin';
    }

    if (card) {
      var extraAnnouncements = card.querySelectorAll('.reg-announce-extra');
      if (seeAll) {
        if (!extraAnnouncements.length) {
          seeAll.style.display = 'none';
        } else {
          seeAll.addEventListener('click', function (event) {
            event.preventDefault();
            var expanded = seeAll.getAttribute('data-expanded') === 'true';
            var nextExpanded = !expanded;

            extraAnnouncements.forEach(function (item) {
              item.hidden = !nextExpanded;
            });

            seeAll.setAttribute('data-expanded', nextExpanded ? 'true' : 'false');
            seeAll.textContent = nextExpanded ? 'Show Less Announcements' : 'See More Announcements';
          });
        }
      }

      card.querySelectorAll('.reg-pin-btn').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          var item = btn.closest('.reg-announce-item');
          if (!item) return;
          var willPin = !item.classList.contains('pinned');
          setPinned(item, willPin);

          if (willPin) {
            var firstItem = card.querySelector('.reg-announce-item');
            if (firstItem && firstItem !== item) {
              card.insertBefore(item, firstItem);
            }
            return;
          }

          if (seeAll) {
            card.insertBefore(item, seeAll);
          }
        });
      });
    }

    var moreToggles = document.querySelectorAll('.reg-more-toggle');

    function closeAllMenus() {
      document.querySelectorAll('.reg-more-menu').forEach(function (menu) {
        menu.hidden = true;
      });
      document.querySelectorAll('.reg-more-wrap').forEach(function (wrap) {
        wrap.classList.remove('is-open');
      });
      if (dashboardRoot) {
        dashboardRoot.classList.remove('menu-open');
      }
      menuBackdrop.classList.remove('is-active');
      moreToggles.forEach(function (toggleBtn) {
        toggleBtn.setAttribute('aria-expanded', 'false');
      });
    }

    moreToggles.forEach(function (toggleBtn) {
      toggleBtn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var wrap = toggleBtn.closest('.reg-more-wrap');
        if (!wrap) return;
        var menu = wrap.querySelector('.reg-more-menu');
        if (!menu) return;

        var opening = menu.hidden;
        closeAllMenus();
        menu.hidden = !opening;
        toggleBtn.setAttribute('aria-expanded', opening ? 'true' : 'false');
        if (opening) {
          wrap.classList.add('is-open');
          if (dashboardRoot) {
            dashboardRoot.classList.add('menu-open');
          }
          menuBackdrop.classList.add('is-active');
        }
      });
    });

    document.querySelectorAll('.reg-more-action').forEach(function (actionBtn) {
      actionBtn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        var item = actionBtn.closest('.reg-announce-item, .reg-sched-item');
        if (!item) return;

        var action = actionBtn.getAttribute('data-action');
        if (action === 'edit') {
          var titleEl = item.querySelector('.reg-announce-title, .reg-sched-name');
          if (!titleEl) return;
          var isSchedule = item.classList.contains('reg-sched-item');
          openEditModal(titleEl, isSchedule ? 'Edit schedule title' : 'Edit announcement title');
        }

        if (action === 'archive') {
          item.classList.remove('pinned');
          var pinBtn = item.querySelector('.reg-pin-btn');
          if (pinBtn) {
            pinBtn.classList.remove('active');
            pinBtn.setAttribute('aria-pressed', 'false');
            pinBtn.title = 'Pin';
          }
          var timeEl = item.querySelector('.reg-announce-time');
          if (timeEl) {
            timeEl.textContent = 'Archived just now';
          }
          if (seeAll && card) {
            card.insertBefore(item, seeAll);
          }
        }

        if (action === 'mark-done') {
          item.classList.add('is-done');
          var label = item.querySelector('.reg-sched-time');
          if (label) {
            label.textContent = 'Done just now';
          }
        }

        if (action === 'move-other' || action === 'move-priority') {
          var bar = item.querySelector('.reg-sched-color-bar');
          if (bar) {
            if (action === 'move-other') {
              bar.classList.remove('priority-high');
              bar.classList.add('priority-normal');
            } else {
              bar.classList.remove('priority-normal');
              bar.classList.add('priority-high');
            }
          }

          if (scheduleCard) {
            var tags = scheduleCard.querySelectorAll('.reg-sched-section-tag');
            var targetTag = action === 'move-other' ? tags[1] : tags[0];
            if (targetTag && targetTag.parentNode === scheduleCard) {
              scheduleCard.insertBefore(item, targetTag.nextSibling);
            }
          }
        }

        if (action === 'remove') {
          item.remove();
        }

        closeAllMenus();
      });
    });

    document.addEventListener('click', function () {
      closeAllMenus();
    });
  });
})();
