@extends('layouts.registrar')

@section('title', 'PLP - Messaging')
@section('page-title', 'MESSAGING')

@section('content')
<style>
/* Responsive improvements for messaging */
.messaging-shell {
    max-width: 100%;
}
.msg-content {
    min-width: 0; /* Important for grid/flex child to NOT blow out container */
    max-width: 100%;
}
.msg-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    display: block; /* Ensure it respects block width */
}
.msg-table {
    min-width: 700px; /* Forces scrolling on small screens */
    width: 100%;
}
.msg-table th, .msg-table td {
    white-space: nowrap;
}

@media (max-width: 900px) {
    .msg-sidebar {
        display: flex;
        flex-direction: row;
        width: 100%;
        margin-bottom: 20px;
        padding: 0;
        background: #fdfdfd;
        border: 1px solid #eaeaea;
        border-radius: 8px;
        min-height: auto;
        overflow: hidden; /* Contains the active background to the rounded border */
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .msg-nav-link {
        flex: 1 1 0; /* Equal width for all tabs */
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
        padding: 12px 0;
        border-left: none !important;
        border-bottom: none !important;
        border-right: 1px solid #eaeaea;
        border-radius: 0;
        background: transparent;
        color: #777;
        font-weight: 500;
        font-size: 0.9rem;
        transform: none !important; /* Disable desktop hover slide */
    }
    .msg-nav-link:last-child {
        border-right: none;
    }
    .msg-nav-link:hover {
        background: #f4f4f4;
    }
    .msg-nav-link.active {
        background: #0a813c !important;
        color: #ffffff !important;
    }
    .msg-nav-icon {
        display: none;
    }
}
@media (max-width: 768px) {
    .msg-toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    .msg-toolbar-actions {
        width: 100%;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .msg-search {
        flex: 1;
        min-width: 150px;
    }
    .msg-search-input {
        width: 100%;
    }
    .msg-compose-btn {
        flex-shrink: 0;
    }
    .msg-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .pf-modal-actions {
        flex-direction: column;
        justify-content: stretch;
        width: 100%;
    }
    .pf-modal-actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>
@php
    $folder = request('folder', 'drafts');
    $messages = [
        'inbox' => [
            ['sender' => 'Registrar Office', 'type' => 'Staff', 'subject' => 'Enrollment status update', 'date' => 'Mar 12, 2026'],
            ['sender' => 'IT Department', 'type' => 'Admin', 'subject' => 'System maintenance notice', 'date' => 'Mar 10, 2026'],
            ['sender' => 'Accounting', 'type' => 'Staff', 'subject' => 'Billing checklist for AY 2025-2026', 'date' => 'Mar 08, 2026'],
        ],
        'drafts' => [
            ['sender' => 'You', 'type' => 'Draft', 'subject' => 'Memo: Section merging guidelines', 'date' => 'Mar 09, 2026'],
            ['sender' => 'You', 'type' => 'Draft', 'subject' => 'Faculty load request template', 'date' => 'Mar 07, 2026'],
        ],
        'sent' => [
            ['sender' => 'You', 'type' => 'Sent', 'subject' => 'Room file update for next term', 'date' => 'Mar 11, 2026'],
            ['sender' => 'You', 'type' => 'Sent', 'subject' => 'Evaluation schedule reminder', 'date' => 'Mar 05, 2026'],
        ],
        'trash' => [
            ['sender' => 'Admissions', 'type' => 'Staff', 'subject' => 'Old application checklist', 'date' => 'Feb 27, 2026'],
        ],
    ];
    $folderMessages = $messages[$folder] ?? [];
@endphp

<div class="messaging-page">
    <div class="messaging-shell">
        <aside class="msg-sidebar">
            <a href="{{ route('registrar.messaging', ['folder' => 'inbox']) }}" class="msg-nav-link {{ $folder === 'inbox' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                        <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                    </svg>
                </span>
                <span>Inbox</span>
            </a>
            <a href="{{ route('registrar.messaging', ['folder' => 'drafts']) }}" class="msg-nav-link {{ $folder === 'drafts' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </span>
                <span>Drafts</span>
            </a>
            <a href="{{ route('registrar.messaging', ['folder' => 'sent']) }}" class="msg-nav-link {{ $folder === 'sent' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </span>
                <span>Sent</span>
            </a>
            <a href="{{ route('registrar.messaging', ['folder' => 'trash']) }}" class="msg-nav-link {{ $folder === 'trash' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/>
                        <path d="M14 11v6"/>
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                </span>
                <span>Trash</span>
            </a>
        </aside>

        <section class="msg-content">
            <div class="msg-toolbar">
                <div class="msg-folder-title">{{ ucfirst($folder) }}</div>
                <div class="msg-toolbar-actions">
                    <div class="msg-search">
                        <svg class="msg-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="msg-search-input" placeholder="Search...">
                    </div>
                    <button type="button" class="msg-compose-btn" id="msgComposeBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Compose
                    </button>
                </div>
            </div>

            <div class="msg-table-wrap">
                <table class="msg-table">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>User Type</th>
                            <th>Subject</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($folderMessages as $msg)
                            <tr>
                                <td>{{ $msg['sender'] }}</td>
                                <td>{{ $msg['type'] }}</td>
                                <td>{{ $msg['subject'] }}</td>
                                <td>{{ $msg['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="msg-empty">No message(s) found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            
        </section>
    </div>
</div>

<div class="pf-modal-overlay" id="composeModal" style="display:none;">
    <div class="pf-modal-box" style="max-width: 600px; padding-bottom: 24px;">
        <div class="pf-modal-title" style="text-align: left; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 5v14"/><path d="M5 12h14"/>
                </svg>
                Compose Message
            </div>
            <button type="button" onclick="closeComposeModal()" aria-label="Close" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: #888; padding: 0; margin: 0; line-height: 1; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f5f5f5'; this.style.color='#d9534f'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#888'">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="pf-modal-form">
            <div class="pf-modal-field" style="margin-bottom: 16px;">
                <label class="pf-modal-label" for="msgTo">To</label>
                <input type="text" class="pf-modal-input" id="msgTo" placeholder="Enter recipient email or group">
            </div>
            <div class="pf-modal-field" style="margin-bottom: 16px;">
                <label class="pf-modal-label" for="msgSubject">Subject</label>
                <input type="text" class="pf-modal-input" id="msgSubject" placeholder="Enter subject">
            </div>
            <div class="pf-modal-field">
                <label class="pf-modal-label" for="msgBody">Message</label>
                <textarea class="pf-modal-input" id="msgBody" rows="7" placeholder="Write your message..." style="resize: vertical;"></textarea>
            </div>
        </div>
        <div class="pf-modal-actions" style="margin-top: 24px; justify-content: flex-end; gap: 12px;">
            <button type="button" class="pf-modal-btn-cancel" onclick="closeComposeModal()" style="border: 1px solid #ccc; background: transparent; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; color: #555; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#f5f5f5'; this.style.color='#333'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#555'">Discard</button>
            <button type="button" class="pf-modal-btn-save" onclick="sendComposeModal()" style="background: #0a813c; color: white; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='#086a31'" onmouseout="this.style.backgroundColor='#0a813c'">
                Send 
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openComposeModal() {
        document.getElementById('composeModal').style.display = 'flex';
    }

    function closeComposeModal() {
        document.getElementById('composeModal').style.display = 'none';
        document.getElementById('msgTo').value = '';
        document.getElementById('msgSubject').value = '';
        document.getElementById('msgBody').value = '';
    }

    function sendComposeModal() {
        var to = document.getElementById('msgTo').value.trim();
        var subject = document.getElementById('msgSubject').value.trim();
        var body = document.getElementById('msgBody').value.trim();
        
        if(!to || !subject || !body) {
            alert('Please fill in all fields before sending.');
            return;
        }

        // Add dummy send feature
        var sendBtn = document.querySelector('.pf-modal-btn-save');
        var originalText = sendBtn.innerHTML;
        sendBtn.innerHTML = 'Sending...';
        
        setTimeout(function() {
            closeComposeModal();
            alert('Message Sent successfully (Demo)');
            sendBtn.innerHTML = originalText;
        }, 800);
    }

    (function () {
        var composeBtn = document.getElementById('msgComposeBtn');
        if (!composeBtn) return;
        composeBtn.addEventListener('click', openComposeModal);
    })();
</script>
@endpush
