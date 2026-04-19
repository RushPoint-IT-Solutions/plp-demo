<?php

namespace App\Http\Controllers\Concerns;

use App\NotificationDelivery;
use App\NotificationType;
use App\PortalNotification;
use App\SystemAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

trait PortalNotifications
{
    protected function portalNotificationModule(): string
    {
        return '';
    }

    protected function portalNotificationAudience(): string
    {
        return SystemAnnouncement::AUDIENCE_EVERYONE;
    }

    protected function portalNotificationRoutePrefix(): string
    {
        return '';
    }

    protected function portalNotificationFallbackTitle(): string
    {
        return 'New notification';
    }

    protected function portalNotificationFallbackMessage(): string
    {
        return 'A notification is available.';
    }

    protected function portalNotificationTitlePrefix(): string
    {
        return 'Announcement: ';
    }

    protected function syncPortalNotificationsForUser($user): void
    {
        $this->syncPortalAnnouncementNotificationsForUser($user);
    }

    protected function activePortalNotifications($user = null)
    {
        $user = $user ?: auth()->user();

        if (!$user || $user->module !== $this->portalNotificationModule()) {
            return collect();
        }

        if (!Schema::hasTable('notification_deliveries') || !Schema::hasTable('portal_notifications')) {
            return collect();
        }

        return NotificationDelivery::query()
            ->with(['notification.type'])
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->orderByDesc('delivered_at')
            ->orderByDesc('id')
            ->get();
    }

    protected function portalUnreadNotificationCount($user = null): int
    {
        $user = $user ?: auth()->user();

        if (!$user || $user->module !== $this->portalNotificationModule()) {
            return 0;
        }

        if (!Schema::hasTable('notification_deliveries')) {
            return 0;
        }

        return (int) NotificationDelivery::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->whereNull('read_at')
            ->count();
    }

    protected function portalNotificationPayloads($user = null)
    {
        $routePrefix = $this->portalNotificationRoutePrefix();

        if ($routePrefix === '') {
            return collect();
        }

        return $this->activePortalNotifications($user)
            ->map(function ($delivery) use ($routePrefix) {
                $notification = $delivery->notification;

                return [
                    'delivery_id' => (int) $delivery->id,
                    'title' => $notification ? (string) $notification->title : 'New notification',
                    'message' => $notification ? (string) $notification->message : '',
                    'source_url' => $notification ? (string) $notification->local_source_url : '',
                    'source_module' => $notification ? (string) $notification->source_module : '',
                    'source_reference' => $notification ? (string) $notification->source_reference : '',
                    'is_read' => !empty($delivery->read_at),
                    'dismiss_url' => route($routePrefix . '.notifications.dismiss', ['notificationDelivery' => $delivery->id], false),
                ];
            })
            ->unique(function ($item) {
                $sourceModule = (string) ($item['source_module'] ?? 'general');
                $sourceReference = trim((string) ($item['source_reference'] ?? ''));

                if ($sourceReference !== '') {
                    return $sourceModule . '|' . $sourceReference;
                }

                $fallback = strtolower(trim((string) ($item['title'] ?? '')) . '|' . trim((string) ($item['message'] ?? '')));

                return $sourceModule . '|' . $fallback;
            })
            ->values();
    }

    protected function portalNotificationsFeed(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $this->syncPortalNotificationsForUser($user);

        return response()->json([
            'ok' => true,
            'unread_count' => $this->portalUnreadNotificationCount($user),
            'notifications' => $this->portalNotificationPayloads($user),
        ]);
    }

    protected function markPortalNotificationsRead(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if (!Schema::hasTable('notification_deliveries')) {
            return response()->json(['ok' => true, 'unread_count' => 0]);
        }

        NotificationDelivery::query()
            ->where('user_id', $user->id)
            ->whereNull('dismissed_at')
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['ok' => true, 'unread_count' => 0]);
    }

    protected function dismissPortalNotification(Request $request, $notificationDelivery)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if (!Schema::hasTable('notification_deliveries')) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }

            return back();
        }

        $delivery = NotificationDelivery::query()
            ->where('id', (int) $notificationDelivery)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $delivery->dismissed_at = now();
        if (empty($delivery->read_at)) {
            $delivery->read_at = now();
        }
        $delivery->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    protected function syncPortalAnnouncementNotificationsForUser($user): void
    {
        $module = $this->portalNotificationModule();

        if (!$user || $user->module !== $module) {
            return;
        }

        if (!Schema::hasTable('system_announcements')
            || !Schema::hasTable('notification_types')
            || !Schema::hasTable('portal_notifications')
            || !Schema::hasTable('notification_deliveries')) {
            return;
        }

        $type = NotificationType::query()->firstOrCreate(
            ['code' => 'SYSTEM_ANNOUNCEMENT_POSTED'],
            ['name' => 'System Announcement Posted']
        );

        $today = now()->toDateString();
        $announcements = SystemAnnouncement::query()
            ->activeOn($today)
            ->visibleToAudience($this->portalNotificationAudience())
            ->orderBy('id')
            ->get(['id', 'title', 'date_from', 'date_to', 'created_at', 'content']);

        foreach ($announcements as $announcement) {
            $title = $this->buildPortalNotificationTitle($announcement);
            $message = $this->buildPortalNotificationMessage($announcement);

            $notification = PortalNotification::query()->firstOrCreate(
                [
                    'source_module' => 'system_announcement',
                    'source_reference' => 'system_announcement:' . $announcement->id,
                ],
                [
                    'notification_type_id' => $type->id,
                    'title' => $title,
                    'message' => $message,
                    'source_url' => '',
                    'created_by_user_id' => null,
                ]
            );

            $hasChanges = false;

            if ((int) $notification->notification_type_id !== (int) $type->id) {
                $notification->notification_type_id = $type->id;
                $hasChanges = true;
            }

            if ((string) $notification->title !== (string) $title) {
                $notification->title = $title;
                $hasChanges = true;
            }

            if ((string) $notification->message !== (string) $message) {
                $notification->message = $message;
                $hasChanges = true;
            }

            if ((string) ($notification->source_url ?: '') !== '') {
                $notification->source_url = '';
                $hasChanges = true;
            }

            if ($hasChanges) {
                $notification->save();
            }

            NotificationDelivery::query()->firstOrCreate(
                [
                    'portal_notification_id' => $notification->id,
                    'user_id' => $user->id,
                ],
                [
                    'delivered_at' => $announcement->created_at ?: now(),
                ]
            );
        }
    }

    protected function buildPortalNotificationTitle(SystemAnnouncement $announcement): string
    {
        $titleValue = trim((string) $announcement->title);

        if ($titleValue !== '') {
            return $this->portalNotificationTitlePrefix() . $titleValue;
        }

        return $this->portalNotificationFallbackTitle();
    }

    protected function buildPortalNotificationMessage(SystemAnnouncement $announcement): string
    {
        $content = trim((string) $announcement->content);
        $message = $content !== '' ? $content : $this->portalNotificationFallbackMessage();

        $dateFromLabel = optional($announcement->date_from)->format('M d, Y');
        $dateToLabel = optional($announcement->date_to)->format('M d, Y');

        if ($dateFromLabel && $dateToLabel && $dateFromLabel !== $dateToLabel) {
            $message .= ' Effective from ' . $dateFromLabel . ' to ' . $dateToLabel . '.';
        } elseif ($dateFromLabel) {
            $message .= ' Effective on ' . $dateFromLabel . '.';
        } elseif ($dateToLabel) {
            $message .= ' Available until ' . $dateToLabel . '.';
        }

        if (!preg_match('/[.!?]$/', $message)) {
            $message .= '.';
        }

        return $message;
    }
}