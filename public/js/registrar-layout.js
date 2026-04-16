(function () {
    function closeSiblingTopDropdowns(currentDropdown) {
        if (!currentDropdown || !currentDropdown.parentElement) {
            return;
        }

        Array.prototype.forEach.call(currentDropdown.parentElement.children, function (dropdown) {
            if (!dropdown.classList || !dropdown.classList.contains('sidebar-dropdown')) {
                return;
            }

            if (dropdown !== currentDropdown) {
                dropdown.classList.remove('open');
            }
        });
    }

    function closeSiblingNestedDropdowns(currentNested) {
        if (!currentNested || !currentNested.parentElement) {
            return;
        }

        Array.prototype.forEach.call(currentNested.parentElement.children, function (nested) {
            if (!nested.classList || !nested.classList.contains('sidebar-nested-dropdown')) {
                return;
            }

            if (nested !== currentNested) {
                nested.classList.remove('open');
            }
        });
    }

    function bindSidebarDropdownToggles() {
        document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var dropdown = this.closest('.sidebar-dropdown');
                var menu = dropdown ? dropdown.querySelector('.sidebar-dropdown-menu') : null;
                if (menu && (menu.querySelector('.sidebar-sublink') || menu.querySelector('.sidebar-nested-dropdown'))) {
                    var willOpen = !dropdown.classList.contains('open');
                    closeSiblingTopDropdowns(dropdown);
                    dropdown.classList.toggle('open', willOpen);
                }
            });
        });

        document.querySelectorAll('.sidebar-nested-toggle').forEach(function (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var nested = this.closest('.sidebar-nested-dropdown');
                if (nested) {
                    var willOpen = !nested.classList.contains('open');
                    closeSiblingNestedDropdowns(nested);
                    nested.classList.toggle('open', willOpen);
                }
            });
        });
    }

    function openActiveSidebarBranches() {
        document.querySelectorAll('.sidebar-link.active, .sidebar-sublink.active').forEach(function (link) {
            var current = link.closest('.sidebar-dropdown, .sidebar-nested-dropdown');
            while (current) {
                current.classList.add('open');
                current = current.parentElement
                    ? current.parentElement.closest('.sidebar-dropdown, .sidebar-nested-dropdown')
                    : null;
            }
        });
    }

    function bindSidebarDrawer() {
        var sidebarToggle = document.getElementById('sidebarToggle');
        var sidebar = document.querySelector('.plp-sidebar');
        var overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('sidebar-open');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', function () {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
            });
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.plp-sidebar')) {
                document.querySelectorAll('.sidebar-dropdown.open, .sidebar-nested-dropdown.open').forEach(function (el) {
                    el.classList.remove('open');
                });
                openActiveSidebarBranches();
            }
        });
    }

    function bindCogPrintButton() {
        var cogPrintBtn = document.getElementById('cog-print-btn');
        if (cogPrintBtn) {
            cogPrintBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.print();
            });
        }
    }

    function bindRegistrarLogout() {
        var logoutLink = document.querySelector('.js-registrar-logout');
        var logoutForm = document.getElementById('registrar-logout-form');
        if (logoutLink && logoutForm) {
            logoutLink.addEventListener('click', function (e) {
                e.preventDefault();
                logoutForm.submit();
            });
        }
    }

    function bindToastClose() {
        var toastCloseBtn = document.getElementById('registrar-toast-close');
        if (toastCloseBtn) {
            toastCloseBtn.addEventListener('click', function () {
                var toast = document.getElementById('registrar-toast');
                if (toast) {
                    toast.classList.remove('show');
                }
            });
        }
    }

    function showRegistrarToast(message, type) {
        var toast = document.getElementById('registrar-toast');
        if (!toast) {
            return;
        }

        var messageEl = toast.querySelector('.toast-message');
        if (messageEl) {
            messageEl.textContent = message;
        }

        toast.classList.remove('show', 'toast-error', 'toast-warning');
        if (type === 'warning') {
            toast.classList.add('toast-warning');
        }
        if (type === 'error') {
            toast.classList.add('toast-error');
        }

        void toast.offsetWidth;
        toast.classList.add('show');
        if (toast._timer) {
            clearTimeout(toast._timer);
        }
        toast._timer = setTimeout(function () {
            toast.classList.remove('show');
        }, 3000);
    }

    function bindRegistrarNotifications() {
        var notificationModal = document.getElementById('registrarNotificationsModal');
        if (!notificationModal) {
            return;
        }

        var notificationList = notificationModal.querySelector('.faculty-notif-list');
        var emptyState = notificationModal.querySelector('.faculty-notif-empty');
        var badge = document.querySelector('.faculty-notif-badge');
        var feedUrl = notificationModal.getAttribute('data-feed-url') || '';
        var markReadUrl = notificationModal.getAttribute('data-mark-read-url') || '';
        var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
        var detailModal = document.getElementById('facultyNotificationDetailModal');
        var detailTitle = document.getElementById('facultyNotificationDetailTitle');
        var detailMessage = document.getElementById('facultyNotificationDetailMessage');
        var detailModalInstance = null;
        var notificationModalInstance = null;
        var reopenNotificationsAfterDetail = false;
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
                var isAnnouncement = String(item.source_module || '') === 'system_announcement';
                var messageText = escapeHtml(item.message || '');
                var textHtml;

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

                    if (notificationModal.classList.contains('show') && Number(payload.unread_count || 0) > 0) {
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
            if (!detailModal || !window.bootstrap || !window.bootstrap.Modal) {
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

            if (!detailModalInstance) {
                detailModalInstance = new window.bootstrap.Modal(detailModal);
            }

            if (detailTitle) {
                detailTitle.textContent = title || 'Announcement';
            }

            if (detailMessage) {
                detailMessage.textContent = message || 'No details available.';
            }

            window.setTimeout(function () {
                detailModalInstance.show();
            }, 160);
        }

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

        if (detailModal) {
            detailModal.addEventListener('hidden.bs.modal', function () {
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
    }

    bindSidebarDropdownToggles();
    openActiveSidebarBranches();
    bindSidebarDrawer();
    bindCogPrintButton();
    bindRegistrarLogout();
    bindToastClose();
    bindRegistrarNotifications();

    window.showRegistrarToast = showRegistrarToast;
})();
