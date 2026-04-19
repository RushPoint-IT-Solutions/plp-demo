// Sidebar toggle (mobile drawer)
document.addEventListener('DOMContentLoaded', function () {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.querySelector('.plp-sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var notificationModal = document.getElementById('facultyNotificationsModal');
    var notificationList = notificationModal ? notificationModal.querySelector('.faculty-notif-list') : null;
    var emptyState = notificationModal ? notificationModal.querySelector('.faculty-notif-empty') : null;
    var badge = document.querySelector('.faculty-notif-badge');
    var notificationDetailModal = document.getElementById('facultyNotificationDetailModal');
    var notificationDetailTitle = document.getElementById('facultyNotificationDetailTitle');
    var notificationDetailMessage = document.getElementById('facultyNotificationDetailMessage');
    var notificationDetailInstance = null;
    var notificationModalInstance = null;
    var reopenNotificationsAfterDetail = false;
    var feedUrl = notificationModal ? notificationModal.getAttribute('data-feed-url') : '';
    var markReadUrl = notificationModal ? notificationModal.getAttribute('data-mark-read-url') : '';
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    var feedTimer = null;
    var feedInFlight = false;

    if (sidebarToggle && sidebar && overlay) {
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('sidebar-open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function () {
            sidebar.classList.remove('sidebar-open');
            overlay.classList.remove('active');
        });

        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
            }
        });
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function updateUnreadBadge(unreadCount) {
        if (!badge) {
            return;
        }

        var count = Math.max(0, Number(unreadCount) || 0);

        if (count > 0) {
            badge.classList.remove('d-none');
            badge.textContent = count > 99 ? '99+' : String(count);
            return;
        }

        badge.classList.add('d-none');
        badge.textContent = '0';
    }

    function updateEmptyState() {
        if (!emptyState || !notificationList) {
            return;
        }

        var hasItems = notificationList.querySelectorAll('.faculty-notif-item').length > 0;

        if (hasItems) {
            emptyState.classList.add('d-none');
        } else {
            emptyState.classList.remove('d-none');
        }
    }

    function renderNotifications(notifications) {
        if (!notificationList) {
            return;
        }

        if (!Array.isArray(notifications)) {
            notifications = [];
        }

        var seen = Object.create(null);
        notifications = notifications.filter(function (item) {
            var sourceModule = String(item.source_module || 'general');
            var sourceReference = String(item.source_reference || '');
            var fallbackKey = String(item.title || '') + '|' + String(item.message || '');
            var key = sourceReference !== ''
                ? sourceModule + '|' + sourceReference
                : sourceModule + '|' + fallbackKey.toLowerCase();

            if (seen[key]) {
                return false;
            }

            seen[key] = true;
            return true;
        });

        if (!notifications.length) {
            notificationList.innerHTML = '';
            updateEmptyState();
            return;
        }

        var html = notifications.map(function (item) {
            var title = escapeHtml(item.title || 'New notification');
            var dismissUrl = escapeHtml(item.dismiss_url || '');
            var readClass = item.is_read ? ' is-read' : '';
            var textHtml;
            var isAnnouncement = String(item.source_module || '') === 'system_announcement';
            var messageText = escapeHtml(item.message || '');

            if (isAnnouncement) {
                textHtml = '<button type="button" class="faculty-notif-text faculty-notif-open js-faculty-notif-open" data-title="' + title + '" data-message="' + messageText + '">' + title + '</button>';
            } else if (item.source_url) {
                textHtml = '<a href="' + escapeHtml(item.source_url) + '" class="faculty-notif-text">' + title + '</a>';
            } else {
                textHtml = '<span class="faculty-notif-text">' + title + '</span>';
            }

            return '' +
                '<div class="faculty-notif-item' + readClass + '" data-delivery-id="' + String(item.delivery_id || '') + '">' +
                    textHtml +
                    '<button type="button" class="faculty-notif-dismiss js-faculty-notif-dismiss" data-dismiss-url="' + dismissUrl + '" aria-label="Dismiss notification" title="Dismiss">&times;</button>' +
                '</div>';
        }).join('');

        notificationList.innerHTML = html;
        updateEmptyState();
    }

    function markAllVisibleAsRead() {
        if (!notificationList) {
            return;
        }

        notificationList.querySelectorAll('.faculty-notif-item').forEach(function (item) {
            item.classList.add('is-read');
        });
    }

    function fetchNotificationFeed() {
        if (!feedUrl || feedInFlight) {
            return Promise.resolve();
        }

        feedInFlight = true;

        return fetch(feedUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Unable to load notifications.');
                }

                return response.json();
            })
            .then(function (payload) {
                if (!payload || payload.ok !== true) {
                    return;
                }

                renderNotifications(payload.notifications || []);
                updateUnreadBadge(payload.unread_count || 0);

                if (notificationModal && notificationModal.classList.contains('show') && Number(payload.unread_count || 0) > 0) {
                    markNotificationsRead();
                }
            })
            .catch(function () {
                // Keep existing UI state when polling fails.
            })
            .finally(function () {
                feedInFlight = false;
            });
    }

    function markNotificationsRead() {
        if (!markReadUrl) {
            return Promise.resolve();
        }

        return fetch(markReadUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Unable to mark notifications as read.');
            }

            return response.json();
        }).then(function () {
            updateUnreadBadge(0);
            markAllVisibleAsRead();
        }).catch(function () {
            // Ignore mark-read failures to avoid blocking modal usage.
        });
    }

    function handleDismissClick(button) {
        var dismissUrl = button.getAttribute('data-dismiss-url');
        var notificationItem = button.closest('.faculty-notif-item');

        if (!dismissUrl || !notificationItem) {
            return;
        }

        button.disabled = true;

        fetch(dismissUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Unable to dismiss notification.');
            }

            notificationItem.remove();
            updateEmptyState();
            return fetchNotificationFeed();
        }).catch(function () {
            button.disabled = false;
        });
    }

    function openNotificationDetail(title, message) {
        if (!notificationDetailModal || !window.bootstrap || !window.bootstrap.Modal) {
            return;
        }

        if (notificationModal && notificationModal.classList.contains('show')) {
            reopenNotificationsAfterDetail = true;

            if (!notificationModalInstance) {
                if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                    notificationModalInstance = window.bootstrap.Modal.getOrCreateInstance(notificationModal);
                } else {
                    notificationModalInstance = new window.bootstrap.Modal(notificationModal);
                }
            }

            notificationModalInstance.hide();
        }

        if (!notificationDetailInstance) {
            notificationDetailInstance = new window.bootstrap.Modal(notificationDetailModal);
        }

        if (notificationDetailTitle) {
            notificationDetailTitle.textContent = title || 'Announcement';
        }

        if (notificationDetailMessage) {
            notificationDetailMessage.textContent = message || 'No details available.';
        }

        window.setTimeout(function () {
            notificationDetailInstance.show();
        }, 160);
    }

    if (notificationModal) {
        notificationModal.addEventListener('click', function (event) {
            var openButton = event.target.closest('.js-faculty-notif-open');
            if (openButton) {
                event.preventDefault();
                openNotificationDetail(
                    openButton.getAttribute('data-title') || 'Announcement',
                    openButton.getAttribute('data-message') || ''
                );
                return;
            }

            var dismissButton = event.target.closest('.js-faculty-notif-dismiss');
            if (!dismissButton) {
                return;
            }

            event.preventDefault();
            handleDismissClick(dismissButton);
        });

        notificationModal.addEventListener('shown.bs.modal', function () {
            markNotificationsRead().then(function () {
                return fetchNotificationFeed();
            });
        });
    }

    if (notificationDetailModal) {
        notificationDetailModal.addEventListener('hidden.bs.modal', function () {
            if (!reopenNotificationsAfterDetail || !notificationModal || !window.bootstrap || !window.bootstrap.Modal) {
                reopenNotificationsAfterDetail = false;
                return;
            }

            if (!notificationModalInstance) {
                if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                    notificationModalInstance = window.bootstrap.Modal.getOrCreateInstance(notificationModal);
                } else {
                    notificationModalInstance = new window.bootstrap.Modal(notificationModal);
                }
            }

            reopenNotificationsAfterDetail = false;
            notificationModalInstance.show();
        });
    }

    updateEmptyState();

    if (feedUrl) {
        fetchNotificationFeed();
        feedTimer = window.setInterval(fetchNotificationFeed, 10000);
    }

    window.addEventListener('beforeunload', function () {
        if (feedTimer) {
            window.clearInterval(feedTimer);
        }
    });
});
