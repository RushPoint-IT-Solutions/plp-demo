document.addEventListener('DOMContentLoaded', function () {
    var notificationContainer = document.querySelector('[data-feed-url]');
    if (!notificationContainer) {
        return;
    }

    var notificationList = notificationContainer.querySelector('.faculty-notif-list');
    var emptyState = notificationContainer.querySelector('.faculty-notif-empty');
    var badge = document.querySelector('.faculty-notif-badge');
    var notificationDetailModal = document.querySelector('.faculty-notif-detail-modal');
    var notificationDetailTitle = document.querySelector('.faculty-notif-detail-modal .modal-title');
    var notificationDetailMessage = document.querySelector('.faculty-notif-detail-message');
    var notificationDetailInstance = null;
    var notificationContainerInstance = null;
    var reopenNotificationsAfterDetail = false;
    var feedUrl = notificationContainer.getAttribute('data-feed-url') || '';
    var markReadUrl = notificationContainer.getAttribute('data-mark-read-url') || '';
    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    var feedTimer = null;
    var feedInFlight = false;

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
            var sourceModule = String(item.source_module || 'general');
            var isAnnouncement = sourceModule === 'system_announcement';
            var messageText = escapeHtml(item.message || '');
            var textHtml;

            // Define icons based on category
            var iconHtml = '';
            if (sourceModule.includes('evaluation')) {
                iconHtml = '<div class="notif-icon-box evaluation"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></div>';
            } else if (sourceModule.includes('calendar') || sourceModule.includes('event')) {
                iconHtml = '<div class="notif-icon-box calendar"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>';
            } else {
                iconHtml = '<div class="notif-icon-box general"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>';
            }

            if (isAnnouncement) {
                textHtml = '<span class="faculty-notif-text">' + title + '</span>';
            } else if (item.source_url) {
                textHtml = '<a href="' + escapeHtml(item.source_url) + '" class="faculty-notif-text">' + title + '</a>';
            } else {
                textHtml = '<span class="faculty-notif-text">' + title + '</span>';
            }

            return '' +
                '<div class="faculty-notif-item' + readClass + '" data-delivery-id="' + String(item.delivery_id || '') + '">' +
                    iconHtml +
                    '<div class="notif-item-body">' +
                        textHtml +
                        '<span class="notif-item-meta">' + (isAnnouncement ? 'System Announcement' : (sourceModule.charAt(0).toUpperCase() + sourceModule.slice(1).replace(/_/g, ' '))) + '</span>' +
                    '</div>' +
                    '<button type="button" class="faculty-notif-dismiss js-portal-notif-dismiss" data-dismiss-url="' + dismissUrl + '" aria-label="Dismiss notification" title="Dismiss">&times;</button>' +
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

                /* Removed immediate mark read during poll to prevent 'insta-grey' */
            })
            .catch(function () {
                // Keep existing UI state when polling fails.
            })
            .finally(function () {
                feedInFlight = false;
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


    // ===== GLOBAL LOGOUT HANDLER =====
    document.addEventListener('click', function(event) {
        const logoutBtn = event.target.closest('.js-registrar-logout');
        if (logoutBtn) {
            event.preventDefault();
            const formId = 'registrar-logout-form';
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            } else {
                // Fallback for older pages
                const fallbackForm = logoutBtn.nextElementSibling;
                if (fallbackForm && fallbackForm.tagName === 'FORM') {
                    fallbackForm.submit();
                }
            }
        }
    });

    notificationContainer.addEventListener('click', function (event) {
        var dismissButton = event.target.closest('.js-portal-notif-dismiss');
        if (!dismissButton) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        handleDismissClick(dismissButton);
    });


    // Restore hover-to-open logic with stability fix
    var triggerToggle = notificationContainer.querySelector('[data-bs-toggle="dropdown"]');
    if (triggerToggle && window.bootstrap && window.bootstrap.Dropdown) {
        var dropdownInstance = window.bootstrap.Dropdown.getOrCreateInstance(triggerToggle);
        var hideTimeout;
        
        notificationContainer.addEventListener('mouseenter', function() {
            clearTimeout(hideTimeout);
            if (!notificationContainer.querySelector('.show')) {
                dropdownInstance.show();
            }
        });

        notificationContainer.addEventListener('mouseleave', function() {
            hideTimeout = setTimeout(function() {
                if (notificationContainer.querySelector('.show')) {
                    dropdownInstance.hide();
                }
            }, 350); // 350ms grace period
        });

        // Click still toggles manually
        triggerToggle.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    function onTriggerShow() {
        return fetchNotificationFeed();
    }

    function onTriggerHidden() {
        markNotificationsRead().then(function() {
            // Optional: refresh count after marking all as read
            updateUnreadBadge(0);
        });
    }

    notificationContainer.addEventListener('shown.bs.dropdown', onTriggerShow);
    notificationContainer.addEventListener('hidden.bs.dropdown', onTriggerHidden);


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