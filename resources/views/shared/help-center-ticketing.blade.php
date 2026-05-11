@php
    $ticketCategories = ['Inquiry', 'Request', 'Feedback', 'Complaint', 'Concern', 'Technical'];
    $ticketPriorities = ['Low', 'Normal', 'High', 'Urgent'];
    $recentTickets = $helpTickets ?? collect();
@endphp

<section class="reg-help-ticket-panel" id="helpCenterTicketing">
    <div class="reg-help-ticket-head">
        <div>
            <h3 class="reg-help-panel-title">Ticketing System</h3>
            <p class="reg-help-ticket-sub">Submit complaints, concerns, requests, and feedback to the Registrar ticketing queue.</p>
        </div>
        <span class="reg-help-ticket-type">{{ $helpTicketRequesterType ?? 'User' }}</span>
    </div>

    @if(session('help_ticket_success'))
        <div class="reg-help-ticket-success">{{ session('help_ticket_success') }}</div>
    @endif

    @if($errors->any())
        <div class="reg-help-ticket-error">{{ $errors->first() }}</div>
    @endif

    <div class="reg-help-ticket-actions reg-help-ticket-actions--start">
        <a href="{{ $helpTicketCreateRoute }}" class="reg-help-ticket-submit">Submit Ticket</a>
    </div>

    <div class="reg-help-ticket-list">
        <h3 class="reg-help-panel-title">My Recent Tickets</h3>
        @forelse($recentTickets as $ticket)
            <div class="reg-help-ticket-row">
                <div>
                    <strong>{{ $ticket->ticket_no }}</strong>
                    <span>{{ $ticket->subject }}</span>
                    <small>{{ optional($ticket->updated_at)->format('M d, Y h:i A') }}</small>
                </div>
                <em class="{{ in_array($ticket->status, ['Open', 'In Progress', 'Pending'], true) ? 'is-open' : '' }}">{{ $ticket->status }}</em>
            </div>
        @empty
            <div class="reg-help-ticket-empty">No tickets submitted yet.</div>
        @endforelse
    </div>
</section>
