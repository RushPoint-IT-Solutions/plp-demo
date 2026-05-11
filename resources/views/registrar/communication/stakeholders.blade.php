@extends('layouts.registrar')

@section('title', 'PLP - Stakeholder Communication')
@section('page-title', 'STAKEHOLDER COMMUNICATION')

@section('content')
<div class="pf-page comm-page">
    <style>.stake-grid{display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;margin-bottom:16px}.stake-card{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:14px}.stake-type{font-weight:900;color:#143521}.stake-meta{color:#66756b;font-size:.84rem;margin-top:6px}.stake-links{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}.stake-link{background:#146c43;color:#fff;border-radius:7px;padding:8px 12px;text-decoration:none;font-weight:800}@media(max-width:900px){.stake-grid{grid-template-columns:1fr}}</style>
    <div class="stake-links">
        <a class="stake-link" href="{{ route('registrar.communication.tickets') }}">Open Tickets</a>
        <a class="stake-link" href="{{ route('registrar.messaging') }}">Messages Module</a>
        <a class="stake-link" href="{{ route('registrar.communication.email-templates') }}">Email Templates</a>
    </div>
    <div class="stake-grid">
        @foreach($stakeholders as $stakeholder)
            <div class="stake-card">
                <div class="stake-type">{{ $stakeholder['type'] }}</div>
                <div class="stake-meta">{{ $stakeholder['tickets'] }} ticket(s), {{ $stakeholder['open'] }} open, {{ $stakeholder['resolved'] }} resolved</div>
            </div>
        @endforeach
    </div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead><tr><th>Ticket</th><th>Stakeholder</th><th>Subject</th><th>Status</th><th>Last Update</th></tr></thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr><td>{{ $ticket->ticket_no }}</td><td>{{ $ticket->requester_type }} · {{ $ticket->requester_name }}</td><td>{{ $ticket->subject }}</td><td>{{ $ticket->status }}</td><td>{{ optional($ticket->updated_at)->format('M d, Y h:i A') }}</td></tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:24px;color:#607264;">No stakeholder communication records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
