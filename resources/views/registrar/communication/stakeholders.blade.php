@extends('layouts.registrar')

@section('title', 'PLP - Stakeholder Communication')
@section('page-title', 'STAKEHOLDER COMMUNICATION')

@section('content')
<div class="pf-page comm-page" id="stakeholderCommPage" data-csrf="{{ csrf_token() }}" data-store-url="{{ route('registrar.communication.stakeholders.message') }}">
    <style>
        .stake-grid{display:grid;grid-template-columns:repeat(3,minmax(180px,1fr));gap:12px;margin-bottom:16px}
        .stake-card{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:14px}
        .stake-type{font-weight:900;color:#143521}
        .stake-meta{color:#66756b;font-size:.84rem;margin-top:6px}
        .stake-links{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
        .stake-link{background:#146c43;color:#fff;border-radius:7px;padding:8px 12px;text-decoration:none;font-weight:800;border:0;cursor:pointer;font-size:.86rem;font-family:inherit}
        .stake-section-title{font-weight:900;color:#143521;margin:22px 0 10px}
        @media(max-width:900px){.stake-grid{grid-template-columns:1fr}}
    </style>
    @if(session('status'))
        <div class="alert alert-{{ session('status_type', 'success') }}" role="alert" style="margin-bottom:14px;">{{ session('status') }}</div>
    @endif
    <div class="stake-links">
        <button type="button" class="stake-link" onclick="openStakeholderComposeModal()">+ Compose Message</button>
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

    <div class="stake-section-title">Communication Log (Sent Messages)</div>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1" id="stakeholderMessageLog">
            <thead><tr><th>Stakeholder Group</th><th>Subject</th><th>Sent</th></tr></thead>
            <tbody id="stakeholderMessageLogBody">
                @forelse($stakeholderMessages as $message)
                    <tr>
                        <td>{{ str_replace('Stakeholder Group: ', '', (string) $message->recipient) }}</td>
                        <td>{{ $message->subject }}</td>
                        <td>{{ optional($message->created_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr id="stakeholderMessageLogEmpty"><td colspan="3" style="text-align:center;padding:24px;color:#607264;">No messages sent to stakeholders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pf-modal-overlay" id="stakeholderComposeModal" style="display:none;">
        <div class="pf-modal-box" style="max-width:480px;">
            <div class="pf-modal-title">Compose Message to Stakeholder Group</div>
            <form id="stakeholderComposeForm">
                <label class="pf-modal-label">Stakeholder Group</label>
                <select class="req-modal-input" id="stakeholderComposeType" style="margin-bottom:12px;">
                    @foreach($stakeholderTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>

                <label class="pf-modal-label">Subject</label>
                <input type="text" class="req-modal-input" id="stakeholderComposeSubject" maxlength="190" style="margin-bottom:12px;">

                <label class="pf-modal-label">Message</label>
                <textarea class="req-modal-input" id="stakeholderComposeBody" rows="5" maxlength="5000"></textarea>

                <div class="pf-modal-actions" style="margin-top:14px;">
                    <button type="button" class="pf-modal-btn-cancel" onclick="closeStakeholderComposeModal()">Cancel</button>
                    <button type="submit" class="pf-modal-btn-save" id="stakeholderComposeSubmit">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openStakeholderComposeModal() {
        document.getElementById('stakeholderComposeSubject').value = '';
        document.getElementById('stakeholderComposeBody').value = '';
        document.getElementById('stakeholderComposeModal').style.display = 'flex';
    }

    function closeStakeholderComposeModal() {
        document.getElementById('stakeholderComposeModal').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        var page = document.getElementById('stakeholderCommPage');
        var form = document.getElementById('stakeholderComposeForm');
        if (!page || !form) return;

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var subject = document.getElementById('stakeholderComposeSubject').value.trim();
            var body = document.getElementById('stakeholderComposeBody').value.trim();
            var stakeholderType = document.getElementById('stakeholderComposeType').value;
            if (subject === '' || body === '') {
                alert('Please fill in both Subject and Message.');
                return;
            }

            var submitBtn = document.getElementById('stakeholderComposeSubmit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            fetch(page.dataset.storeUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': page.dataset.csrf
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    stakeholder_type: stakeholderType,
                    subject: subject,
                    body: body
                })
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok) throw payload;
                        return payload;
                    });
                })
                .then(function (payload) {
                    var logBody = document.getElementById('stakeholderMessageLogBody');
                    var emptyRow = document.getElementById('stakeholderMessageLogEmpty');
                    if (emptyRow) emptyRow.remove();

                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td></td><td></td><td></td>';
                    tr.children[0].textContent = payload.data.stakeholder_type;
                    tr.children[1].textContent = payload.data.subject;
                    tr.children[2].textContent = payload.data.created_at;
                    logBody.insertBefore(tr, logBody.firstChild);

                    closeStakeholderComposeModal();
                    alert(payload.message || 'Message sent successfully.');
                })
                .catch(function (error) {
                    alert((error && error.message) || 'Unable to send the message.');
                })
                .finally(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send';
                });
        });
    });
</script>
@endsection
