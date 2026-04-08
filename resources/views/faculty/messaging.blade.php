@extends('layouts.faculty')

@section('title', 'PLP - Messaging')
@section('page-title', 'MESSAGING')

@section('content')
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

<div class="messaging-page msg-theme-lite">
    <div class="messaging-shell">
        <aside class="msg-sidebar">
            <a href="{{ route('faculty.messaging', ['folder' => 'inbox']) }}" class="msg-nav-link {{ $folder === 'inbox' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                        <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                    </svg>
                </span>
                <span>Inbox</span>
            </a>
            <a href="{{ route('faculty.messaging', ['folder' => 'drafts']) }}" class="msg-nav-link {{ $folder === 'drafts' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </span>
                <span>Drafts</span>
            </a>
            <a href="{{ route('faculty.messaging', ['folder' => 'sent']) }}" class="msg-nav-link {{ $folder === 'sent' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </span>
                <span>Sent</span>
            </a>
            <a href="{{ route('faculty.messaging', ['folder' => 'trash']) }}" class="msg-nav-link {{ $folder === 'trash' ? 'active' : '' }}">
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

<div class="pf-modal-overlay faculty-gs-hidden" id="composeModal" aria-hidden="true">
    <div class="pf-modal-box msg-compose-modal-box">
        <div class="msg-compose-modal-head">
            <div class="msg-compose-modal-title-wrap">
                <div class="pf-modal-title msg-compose-modal-title">Compose Message</div>
            </div>
            <button type="button" class="msg-compose-close" id="msgComposeCloseBtn" aria-label="Close compose modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="pf-modal-form">
            <div class="pf-modal-field">
                <label class="pf-modal-label" for="msgTo">To</label>
                <input type="text" class="pf-modal-input" id="msgTo" placeholder="Enter recipient email or group">
            </div>
            <div class="pf-modal-field">
                <label class="pf-modal-label" for="msgSubject">Subject</label>
                <input type="text" class="pf-modal-input" id="msgSubject" placeholder="Enter subject">
            </div>
            <div class="pf-modal-field">
                <label class="pf-modal-label" for="msgBody">Message</label>
                <textarea class="pf-modal-input msg-compose-textarea" id="msgBody" rows="7" placeholder="Write your message..."></textarea>
            </div>
        </div>

        <div class="pf-modal-actions msg-compose-modal-actions">
            <button type="button" class="pf-modal-btn-cancel" id="msgComposeDiscardBtn">Discard</button>
            <button type="button" class="pf-modal-btn-save msg-compose-send-btn" id="msgComposeSendBtn">
                Send
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/faculty-messaging.js') }}?v={{ file_exists(public_path('js/faculty-messaging.js')) ? filemtime(public_path('js/faculty-messaging.js')) : time() }}"></script>
@endpush
