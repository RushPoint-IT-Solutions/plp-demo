<?php

namespace App\Http\Controllers\ParentModule;

use App\Http\Controllers\Concerns\PortalNotifications;
use App\Http\Controllers\Controller;
use App\SystemAnnouncement;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    use PortalNotifications;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            $this->syncPortalNotificationsForUser($user);

            view()->share('parentNotifications', $this->portalNotificationPayloads($user));
            view()->share('parentUnreadNotificationCount', $this->portalUnreadNotificationCount($user));

            return $next($request);
        });
    }

    protected function portalNotificationModule(): string
    {
        return 'parent';
    }

    protected function portalNotificationAudience(): string
    {
        return SystemAnnouncement::AUDIENCE_STUDENTS;
    }

    protected function portalNotificationRoutePrefix(): string
    {
        return 'parent';
    }

    protected function portalNotificationFallbackTitle(): string
    {
        return 'New Parent Notification';
    }

    protected function portalNotificationFallbackMessage(): string
    {
        return 'A student announcement is available';
    }

    public function dashboard()
    {
        $user = Auth::user();
        $parent = null;
        $linkedStudents = collect();

        if ($user) {
            $parent = $user->parentProfile;

            if ($parent) {
                $linkedStudents = $parent->studentLinks()
                    ->with(['student', 'relationshipType'])
                    ->orderByDesc('is_primary_contact')
                    ->orderBy('id')
                    ->get();
            }
        }

        return view('parent.dashboard', [
            'parent' => $parent,
            'linkedStudents' => $linkedStudents,
            'user' => $user,
        ]);
    }
}
