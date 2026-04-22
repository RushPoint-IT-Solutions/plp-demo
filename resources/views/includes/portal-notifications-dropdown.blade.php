@php
    $notificationContainerId = $notificationContainerId ?? 'portalNotificationsDropdown';
    $notificationTitle = $notificationTitle ?? 'Notifications';
    $notificationDetailModalId = $notificationDetailModalId ?? 'portalNotificationDetailModal';
    $notifications = $notifications ?? collect();
    $unreadCount = (int) ($unreadCount ?? 0);
    $feedUrl = $feedUrl ?? '';
    $markReadUrl = $markReadUrl ?? '';
@endphp

<style>
    /* Ensure notification trigger button is clean and consistent with portal style */
    .student-portal-body .topbar-icon-link.topbar-notif-icon,
    .applicant-body .topbar-icon-link.topbar-notif-icon,
    .registrar-body .topbar-icon-link.topbar-notif-icon,
    .faculty-body .topbar-icon-link.topbar-notif-icon {
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      -webkit-appearance: none !important;
      appearance: none !important;
      text-decoration: none !important;
    }

    .student-portal-body .topbar-icon-link.topbar-notif-icon:hover,
    .student-portal-body .topbar-icon-link.topbar-notif-icon.show,
    .applicant-body .topbar-icon-link.topbar-notif-icon:hover,
    .applicant-body .topbar-icon-link.topbar-notif-icon.show,
    .registrar-body .topbar-icon-link.topbar-notif-icon:hover,
    .registrar-body .topbar-icon-link.topbar-notif-icon.show,
    .faculty-body .topbar-icon-link.topbar-notif-icon:hover,
    .faculty-body .topbar-icon-link.topbar-notif-icon.show {
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      transform: none !important;
    }
</style>

<div class="dropdown d-inline-block" id="{{ $notificationContainerId }}" data-feed-url="{{ $feedUrl }}" data-mark-read-url="{{ $markReadUrl }}">
    <a href="#" class="topbar-icon-link topbar-notif-icon" id="notifDropdownBtn" title="Notifications" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span class="faculty-notif-badge {{ $unreadCount ? '' : 'd-none' }}">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
    </a>

    {{-- This include is used to build the dropdown menu content --}}
    <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu" aria-labelledby="notifDropdownBtn">
        <div class="notif-dropdown-header">
            <h5 class="notif-dropdown-title">{{ $notificationTitle }}</h5>
        </div>
        <div class="faculty-notif-list-container">
            <div class="faculty-notif-empty {{ $notifications->count() ? 'd-none' : '' }}">
                <div class="notif-empty-content">
                    <span style="display: block; padding: 25px 15px; color: #6c757d; font-weight: 500; font-style: italic; text-align: center;">You have no notifications at this time.</span>
                </div>
            </div>
            <div class="faculty-notif-list">
                @forelse($notifications as $notification)
                    @php
                        $notificationTitleStr = trim((string) ($notification['title'] ?? 'New notification'));
                        $notificationMessage = trim((string) ($notification['message'] ?? ''));
                        $notificationSourceUrl = trim((string) ($notification['source_url'] ?? ''));
                        $isAnnouncementNotification = trim((string) ($notification['source_module'] ?? '')) === 'system_announcement';
                    @endphp
                    <div class="faculty-notif-item {{ !empty($notification['is_read']) ? 'is-read' : '' }}" data-delivery-id="{{ $notification['delivery_id'] ?? '' }}">
                        @if($isAnnouncementNotification)
                            <span class="faculty-notif-text">
                                {{ $notificationTitleStr ?: 'Announcement' }}
                            </span>
                        @elseif($notificationSourceUrl !== '')
                            <a href="{{ $notificationSourceUrl }}" class="faculty-notif-text">{{ $notificationTitleStr }}</a>
                        @else
                            <span class="faculty-notif-text">{{ $notificationTitleStr }}</span>
                        @endif
                        <button
                            type="button"
                            class="faculty-notif-dismiss js-portal-notif-dismiss"
                            data-dismiss-url="{{ $notification['dismiss_url'] ?? '' }}"
                            aria-label="Dismiss notification"
                            title="Dismiss"
                        >
                            &times;
                        </button>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>

