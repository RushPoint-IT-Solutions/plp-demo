@php
    $notificationModalId = $notificationModalId ?? 'portalNotificationsModal';
    $notificationModalTitleId = $notificationModalTitleId ?? 'portalNotificationsTitle';
    $notificationModalTitle = $notificationModalTitle ?? 'NOTIFICATIONS';
    $notificationDetailModalId = $notificationDetailModalId ?? 'portalNotificationDetailModal';
    $notificationDetailTitleId = $notificationDetailTitleId ?? 'portalNotificationDetailTitle';
    $notificationDetailMessageId = $notificationDetailMessageId ?? 'portalNotificationDetailMessage';
    $notifications = $notifications ?? collect();
    $feedUrl = $feedUrl ?? '';
    $markReadUrl = $markReadUrl ?? '';
@endphp

<div
    class="modal fade faculty-notif-modal"
    id="{{ $notificationModalId }}"
    tabindex="-1"
    aria-labelledby="{{ $notificationModalTitleId }}"
    aria-hidden="true"
    data-feed-url="{{ $feedUrl }}"
    data-mark-read-url="{{ $markReadUrl }}"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="faculty-notif-modal-header">
                <h5 class="modal-title" id="{{ $notificationModalTitleId }}">{{ $notificationModalTitle }}</h5>
            </div>
            <div class="faculty-notif-body">
                <div class="faculty-notif-list">
                    @forelse($notifications as $notification)
                        @php
                            $notificationTitle = trim((string) ($notification['title'] ?? 'New notification'));
                            $notificationMessage = trim((string) ($notification['message'] ?? ''));
                            $notificationSourceUrl = trim((string) ($notification['source_url'] ?? ''));
                            $isAnnouncementNotification = trim((string) ($notification['source_module'] ?? '')) === 'system_announcement';
                        @endphp
                        <div class="faculty-notif-item {{ !empty($notification['is_read']) ? 'is-read' : '' }}" data-delivery-id="{{ $notification['delivery_id'] ?? '' }}">
                            @if($isAnnouncementNotification)
                                <button
                                    type="button"
                                    class="faculty-notif-text faculty-notif-open js-portal-notif-open"
                                    data-title="{{ $notificationTitle ?: 'Announcement' }}"
                                    data-message="{{ $notificationMessage }}"
                                >
                                    {{ $notificationTitle ?: 'Announcement' }}
                                </button>
                            @elseif($notificationSourceUrl !== '')
                                <a href="{{ $notificationSourceUrl }}" class="faculty-notif-text">{{ $notificationTitle }}</a>
                            @else
                                <span class="faculty-notif-text">{{ $notificationTitle }}</span>
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

                <p class="faculty-notif-empty {{ $notifications->count() ? 'd-none' : '' }}">No new notifications.</p>
            </div>
        </div>
    </div>
</div>

<div
    class="modal fade faculty-notif-detail-modal"
    id="{{ $notificationDetailModalId }}"
    tabindex="-1"
    aria-labelledby="{{ $notificationDetailTitleId }}"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="faculty-notif-modal-header">
                <h5 class="modal-title" id="{{ $notificationDetailTitleId }}">Announcement</h5>
            </div>
            <div class="faculty-notif-body">
                <p class="faculty-notif-detail-message" id="{{ $notificationDetailMessageId }}">No details available.</p>
            </div>
            <div class="faculty-notif-detail-footer">
                <button type="button" class="faculty-notif-detail-close" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>