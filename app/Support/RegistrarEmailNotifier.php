<?php

namespace App\Support;

use App\RegistrarMessage;
use App\SupportTicket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrarEmailNotifier
{
    public static function sendRegistrarMessage(RegistrarMessage $message): bool
    {
        $recipients = self::extractEmails((string) $message->recipient);
        if (empty($recipients)) {
            return false;
        }

        $subject = trim((string) $message->subject) ?: 'Message from PLP Registrar';
        $body = trim((string) $message->body);
        if ($body === '') {
            $body = 'You have a new message from the PLP Registrar Office.';
        }

        return self::sendPlain($recipients, $subject, $body);
    }

    public static function sendTicketCreated(SupportTicket $ticket, bool $notifyRegistrar = true): void
    {
        $requesterRecipients = self::extractEmails((string) $ticket->requester_email);
        if (!empty($requesterRecipients)) {
            self::sendPlain(
                $requesterRecipients,
                'PLP Ticket Received: ' . $ticket->ticket_no,
                self::ticketCreatedRequesterBody($ticket)
            );
        }

        if ($notifyRegistrar) {
            $registrarEmail = self::registrarEmail();
            if ($registrarEmail !== null) {
                self::sendPlain(
                    [$registrarEmail],
                    'New PLP Support Ticket: ' . $ticket->ticket_no,
                    self::ticketCreatedRegistrarBody($ticket)
                );
            }
        }
    }

    public static function sendTicketUpdated(SupportTicket $ticket, ?string $oldStatus = null, ?string $oldPriority = null): void
    {
        $recipients = self::extractEmails((string) $ticket->requester_email);
        if (empty($recipients)) {
            return;
        }

        self::sendPlain(
            $recipients,
            'PLP Ticket Update: ' . $ticket->ticket_no,
            self::ticketUpdatedRequesterBody($ticket, $oldStatus, $oldPriority)
        );
    }

    private static function sendPlain(array $recipients, string $subject, string $body): bool
    {
        $recipients = array_values(array_unique(array_filter($recipients)));
        if (empty($recipients)) {
            return false;
        }

        try {
            Mail::raw($body, function ($mail) use ($recipients, $subject) {
                $mail->to($recipients)->subject($subject);
            });

            return true;
        } catch (\Throwable $exception) {
            Log::warning('Registrar email notification failed.', [
                'recipients' => $recipients,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private static function extractEmails(string $value): array
    {
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches);

        return array_values(array_filter($matches[0] ?? [], function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }));
    }

    private static function registrarEmail(): ?string
    {
        $email = trim((string) (config('mail.from.address') ?: env('MAIL_FROM_ADDRESS')));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private static function ticketCreatedRequesterBody(SupportTicket $ticket): string
    {
        return "Good day {$ticket->requester_name},\n\n"
            . "We received your ticket.\n\n"
            . "Ticket No.: {$ticket->ticket_no}\n"
            . "Subject: {$ticket->subject}\n"
            . "Category: {$ticket->category}\n"
            . "Priority: {$ticket->priority}\n"
            . "Status: {$ticket->status}\n\n"
            . "Message:\n{$ticket->message}\n\n"
            . "Registrar Office\nPamantasan ng Lungsod ng Pasig";
    }

    private static function ticketCreatedRegistrarBody(SupportTicket $ticket): string
    {
        return "A new support ticket was submitted.\n\n"
            . "Ticket No.: {$ticket->ticket_no}\n"
            . "Requester: {$ticket->requester_name} ({$ticket->requester_type})\n"
            . "Email: " . ($ticket->requester_email ?: 'No email') . "\n"
            . "Category: {$ticket->category}\n"
            . "Priority: {$ticket->priority}\n"
            . "Subject: {$ticket->subject}\n\n"
            . "Message:\n{$ticket->message}";
    }

    private static function ticketUpdatedRequesterBody(SupportTicket $ticket, ?string $oldStatus, ?string $oldPriority): string
    {
        $changes = [];
        if ($oldStatus !== null && $oldStatus !== (string) $ticket->status) {
            $changes[] = "Status: {$oldStatus} -> {$ticket->status}";
        }
        if ($oldPriority !== null && $oldPriority !== (string) $ticket->priority) {
            $changes[] = "Priority: {$oldPriority} -> {$ticket->priority}";
        }

        $changeText = empty($changes)
            ? "Your ticket details were updated."
            : implode("\n", $changes);

        return "Good day {$ticket->requester_name},\n\n"
            . "There is an update to your PLP support ticket.\n\n"
            . "Ticket No.: {$ticket->ticket_no}\n"
            . "{$changeText}\n\n"
            . "Current Message / Notes:\n{$ticket->message}\n\n"
            . "Registrar Office\nPamantasan ng Lungsod ng Pasig";
    }
}
