@extends('layouts.registrar')

@section('title', 'PLP - Ticketing System')
@section('page-title', 'TICKETING SYSTEM')

@section('content')
<div class="pf-page comm-page" id="ticketPage" data-store-url="{{ route('registrar.communication.tickets.store') }}" data-csrf="{{ csrf_token() }}">
    <style>
        .comm-summary{display:grid;grid-template-columns:repeat(5,minmax(120px,1fr));gap:12px;margin-bottom:16px}.comm-stat{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:12px 14px}.comm-stat span{display:block}.comm-label{color:#607264;font-size:.78rem;font-weight:800;text-transform:uppercase}.comm-value{color:#123822;font-size:1.45rem;font-weight:900;margin-top:4px}.comm-panel{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:14px;margin-bottom:16px}.comm-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.comm-grid .wide{grid-column:span 2}.comm-grid .full{grid-column:1/-1}.comm-page label{display:block;color:#46564a;font-size:.78rem;font-weight:800;margin-bottom:5px}.comm-page input,.comm-page select,.comm-page textarea{width:100%;border:1px solid #cfd9d2;border-radius:7px;padding:8px 10px;background:#fff;color:#143521}.comm-page textarea{min-height:84px}.comm-save{background:#146c43;border:0;border-radius:7px;color:#fff;font-weight:800;min-height:38px;padding:8px 14px}.comm-muted{color:#66756b;font-size:.82rem}.comm-badge{display:inline-flex;border-radius:999px;background:#eef7f1;color:#17633a;font-size:.76rem;font-weight:800;padding:4px 8px}.comm-badge.warn{background:#fff5d8;color:#8a5b00}.comm-badge.bad{background:#fee2e2;color:#991b1b}@media(max-width:900px){.comm-summary,.comm-grid{grid-template-columns:1fr}.comm-grid .wide{grid-column:auto}}
    </style>

    <div class="comm-summary">
        <div class="comm-stat"><span class="comm-label">Tickets</span><span class="comm-value">{{ $summary['total'] }}</span></div>
        <div class="comm-stat"><span class="comm-label">Open</span><span class="comm-value">{{ $summary['open'] }}</span></div>
        <div class="comm-stat"><span class="comm-label">In Progress</span><span class="comm-value">{{ $summary['progress'] }}</span></div>
        <div class="comm-stat"><span class="comm-label">Resolved</span><span class="comm-value">{{ $summary['resolved'] }}</span></div>
        <div class="comm-stat"><span class="comm-label">Complaints</span><span class="comm-value">{{ $summary['complaints'] }}</span></div>
    </div>

    <form class="comm-panel" id="ticketCreateForm">
        <div class="comm-grid">
            <div><label>Requester Type</label><select name="requester_type">@foreach(['Student','Faculty','Parent','Applicant','CHED','Other'] as $v)<option>{{ $v }}</option>@endforeach</select></div>
            <div><label>Category</label><select name="category">@foreach(['Inquiry','Request','Feedback','Complaint','Concern'] as $v)<option>{{ $v }}</option>@endforeach</select></div>
            <div><label>Priority</label><select name="priority">@foreach(['Low','Normal','High','Urgent'] as $v)<option {{ $v==='Normal'?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
            <div><label>Email</label><input type="email" name="requester_email" placeholder="email@example.com"></div>
            <div class="wide"><label>Name</label><input type="text" name="requester_name" required placeholder="Requester name"></div>
            <div class="wide"><label>Subject</label><input type="text" name="subject" required placeholder="Concern or request subject"></div>
            <div class="full"><label>Message</label><textarea name="message" placeholder="Complaint, inquiry, request, or feedback details"></textarea></div>
            <div><button type="submit" class="comm-save">Submit Ticket</button></div>
        </div>
    </form>

    <form class="comm-panel" method="GET" action="{{ route('registrar.communication.tickets') }}">
        <div class="comm-grid">
            <div><label>Status</label><select name="status"><option value="">All Statuses</option>@foreach(['Open','In Progress','Pending','Resolved','Closed'] as $v)<option value="{{ $v }}" {{ $status===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
            <div><label>Category</label><select name="category"><option value="">All Categories</option>@foreach(['Inquiry','Request','Feedback','Complaint','Concern'] as $v)<option value="{{ $v }}" {{ $category===$v?'selected':'' }}>{{ $v }}</option>@endforeach</select></div>
            <div class="wide"><label>Search</label><input type="text" name="q" value="{{ $search }}" placeholder="Ticket no., requester, subject"></div>
            <div><button type="submit" class="comm-save">Filter</button></div>
        </div>
    </form>

    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead><tr><th>Ticket</th><th>Requester</th><th>Category</th><th>Subject</th><th>Priority</th><th>Status</th><th>Updated</th></tr></thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td><strong>{{ $ticket->ticket_no }}</strong></td>
                        <td>{{ $ticket->requester_name }}<div class="comm-muted">{{ $ticket->requester_type }} · {{ $ticket->requester_email ?: 'No email' }}</div></td>
                        <td>{{ $ticket->category }}</td>
                        <td>{{ $ticket->subject }}<div class="comm-muted">{{ \Illuminate\Support\Str::limit($ticket->message, 80) }}</div></td>
                        <td><span class="comm-badge {{ in_array($ticket->priority, ['High','Urgent']) ? 'bad' : '' }}">{{ $ticket->priority }}</span></td>
                        <td><span class="comm-badge {{ $ticket->status === 'Open' ? 'warn' : '' }}">{{ $ticket->status }}</span></td>
                        <td>{{ optional($ticket->updated_at)->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:24px;color:#607264;">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
(function(){var page=document.getElementById('ticketPage'),form=document.getElementById('ticketCreateForm');if(!page||!form)return;form.addEventListener('submit',function(e){e.preventDefault();var data={};new FormData(form).forEach(function(v,k){data[k]=v});fetch(page.dataset.storeUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':page.dataset.csrf},credentials:'same-origin',body:JSON.stringify(data)}).then(function(r){return r.json().then(function(p){if(!r.ok)throw p;return p})}).then(function(p){if(window.showRegistrarToast)showRegistrarToast(p.message||'Ticket submitted.','success');window.location.reload()}).catch(function(p){var m=p&&p.message?p.message:'Unable to submit ticket.';if(window.showRegistrarToast)showRegistrarToast(m,'error')})})})();
</script>
@endsection
