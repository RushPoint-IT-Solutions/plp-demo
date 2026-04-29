@extends('layouts.applicant')

@section('title', 'PLP - Messaging')
@section('page-title', 'MESSAGING')

@section('content')
@php
    $folder = request('folder', 'inbox');
    $messages = [
        'inbox' => [
            ['sender' => 'Registrar Office', 'type' => 'Staff', 'subject' => 'Enrollment status update', 'date' => 'Mar 12, 2026'],
            ['sender' => 'IT Department', 'type' => 'Admin', 'subject' => 'System maintenance notice', 'date' => 'Mar 10, 2026'],
            ['sender' => 'Admissions', 'type' => 'Staff', 'subject' => 'Initial application review', 'date' => 'Mar 08, 2026'],
        ],
        'drafts' => [
            ['sender' => 'You', 'type' => 'Draft', 'subject' => 'Query: Document submission clarification', 'date' => 'Mar 09, 2026'],
        ],
        'sent' => [
            ['sender' => 'You', 'type' => 'Sent', 'subject' => 'Updated PSA birth certificate', 'date' => 'Mar 11, 2026'],
        ],
        'trash' => [
            ['sender' => 'System', 'type' => 'Auto', 'subject' => 'Welcome to the applicant portal', 'date' => 'Feb 27, 2026'],
        ],
    ];
    $folderMessages = $messages[$folder] ?? [];
@endphp

<div class="messaging-page msg-theme-lite">
    <div class="messaging-shell">
        <aside class="msg-sidebar">
            <a href="{{ route('applicant.messaging', ['folder' => 'inbox']) }}" class="msg-nav-link {{ $folder === 'inbox' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                        <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                    </svg>
                </span>
                <span>Inbox</span>
            </a>
            <a href="{{ route('applicant.messaging', ['folder' => 'drafts']) }}" class="msg-nav-link {{ $folder === 'drafts' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </span>
                <span>Drafts</span>
            </a>
            <a href="{{ route('applicant.messaging', ['folder' => 'sent']) }}" class="msg-nav-link {{ $folder === 'sent' ? 'active' : '' }}">
                <span class="msg-nav-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </span>
                <span>Sent</span>
            </a>
            <a href="{{ route('applicant.messaging', ['folder' => 'trash']) }}" class="msg-nav-link {{ $folder === 'trash' ? 'active' : '' }}">
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
                    @if($folder === 'inbox')
                    <button type="button" class="msg-compose-btn" id="msgComposeBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        Compose
                    </button>
                    @endif
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
                            <tr @if($folder === 'drafts') class="draft-row" data-subject="{{ $msg['subject'] }}" @else class="msg-row" data-sender="{{ $msg['sender'] }}" data-type="{{ $msg['type'] }}" data-subject="{{ $msg['subject'] }}" data-date="{{ $msg['date'] }}" data-folder="{{ $folder }}" @endif>
                                <td>
                                    <div style="font-weight: 700;">{{ $msg['sender'] }}</div>
                                    <div class="msg-row-hint">Click to view</div>
                                </td>
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

@include('includes.messaging-compose-modal')
@include('includes.messaging-view-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/applicant-messaging.js') }}?v={{ file_exists(public_path('js/applicant-messaging.js')) ? filemtime(public_path('js/applicant-messaging.js')) : time() }}"></script>
@endpush
