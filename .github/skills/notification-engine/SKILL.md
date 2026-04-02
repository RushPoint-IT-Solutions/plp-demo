---
name: "notification-engine"
description: "Automated notifications for parents/students."


---

# Skill 18: Notification Engine

##  LEGACY STACK CONTEXT (CRITICAL)
- **Framework:** Laravel 5.7 ONLY (Requires PHP 7.1+ syntax).
- **Frontend:** Bootstrap 4, Vue 2, jQuery.
- **Build Tool:** Laravel Mix (`webpack.mix.js`). Run via `npm run dev`. NO Vite.

##  Explicitly Forbidden PHP 8+ Features
- `match` expressions
- Union types (e.g., `string|int`)
- Nullsafe operator (`?->`)
- Named arguments
- Constructor property promotion
- Arrow functions (`fn() =>`)
- Typed properties
- Null-coalescing assignment (`??=`)

##  Notification Channels
| Channel | Provider | Use Case |
|---------|----------|----------|
| Email | Laravel Mail | Grade posted, announcements |
| SMS | Twilio | Urgent alerts, fee due |
| In-App | Database | All notifications |

##  Notification Events
- Grade posted
- Fee due (3 days before, 1 day before, on due date)
- Absence marked
- Announcement published

##  Queue All Notifications
Notifications should implement `ShouldQueue` and be dispatched to the queue worker.

```php
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class GradePosted extends Notification implements ShouldQueue
{
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // Build the MailMessage
    }
}
```

##  MCP Integration
- **Filesystem MCP:** Verify notification classes in `app/Notifications/` and mailable templates in `resources/views/emails/`.
- **Queue MCP:** Verify `config/queue.php` driver (database, redis) and that `php artisan queue:work` is running or configured in supervisor/PM2.
- **Mail MCP:** Verify `config/mail.php` and environment variables (`MAIL_DRIVER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`).
- **SMS MCP:** Verify Twilio env vars (`TWILIO_SID`, `TWILIO_AUTH_TOKEN`) and test SMS send.

##  Escalation Protocol
- If the queue driver is not configured, jobs table missing, mail driver misconfigured, or SMS provider credentials are missing, STOP and output:
  ` ESCALATION REQUIRED. Notification system misconfigured: <detail>.`

##  STOP COMMAND
Output `WAITING_FOR_HUMAN_OK` when the file is generated.

