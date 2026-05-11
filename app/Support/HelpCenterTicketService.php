<?php

namespace App\Support;

use App\SupportTicket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HelpCenterTicketService
{
    public static function createFromRequest(Request $request, string $requesterType, ?User $user = null): SupportTicket
    {
        $validated = $request->validate([
            'requester_name' => 'required|string|max:190',
            'requester_email' => 'nullable|email|max:190',
            'category' => 'required|in:Inquiry,Request,Feedback,Complaint,Concern,Technical',
            'subject' => 'required|string|max:190',
            'message' => 'required|string|max:5000',
            'priority' => 'required|in:Low,Normal,High,Urgent',
        ]);

        $ticket = SupportTicket::query()->create([
            'ticket_no' => self::nextTicketNumber(),
            'requester_type' => $requesterType,
            'requester_name' => trim((string) $validated['requester_name']),
            'requester_email' => trim((string) ($validated['requester_email'] ?? '')) ?: null,
            'category' => $validated['category'],
            'subject' => trim((string) $validated['subject']),
            'message' => trim((string) $validated['message']),
            'priority' => $validated['priority'],
            'status' => 'Open',
            'created_by_user_id' => $user ? (int) $user->id : null,
        ]);

        self::createRegistrarInboxMessage($ticket, $user);

        return $ticket;
    }

    public static function recentTicketsForUser(string $requesterType, ?User $user = null)
    {
        $query = SupportTicket::query()
            ->where('requester_type', $requesterType)
            ->orderByDesc('updated_at')
            ->orderByDesc('id');

        if ($user) {
            $email = trim((string) ($user->email ?? ''));
            $query->where(function ($builder) use ($user, $email) {
                $builder->where('created_by_user_id', (int) $user->id);

                if ($email !== '') {
                    $builder->orWhere('requester_email', $email);
                }
            });
        }

        return $query->limit(8)->get();
    }

    private static function createRegistrarInboxMessage(SupportTicket $ticket, ?User $user = null): void
    {
        if (!Schema::hasTable('registrar_messages')) {
            return;
        }

        DB::table('registrar_messages')->insert([
            'folder' => 'inbox',
            'sender_name' => (string) $ticket->requester_name,
            'sender_type' => (string) $ticket->requester_type,
            'recipient' => 'Registrar Office',
            'subject' => '[' . $ticket->ticket_no . '] ' . $ticket->subject,
            'body' => (string) $ticket->message,
            'source_type' => 'support_ticket',
            'source_id' => (int) $ticket->id,
            'created_by_user_id' => $user ? (int) $user->id : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function nextTicketNumber(): string
    {
        $prefix = 'TKT-' . now()->format('Ymd') . '-';
        $count = SupportTicket::query()
            ->where('ticket_no', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
