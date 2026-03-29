@extends('layouts.registrar')

@section('title', 'PLP - Registrar Dashboard')
@section('page-title', 'DASHBOARD')
@section('body-class', 'page-registrar-dashboard')

@section('content')
@php
    $dashboardData = $dashboardData ?? [];
    $studentCount = (int) ($dashboardData['studentCount'] ?? 0);
    $maleCount = (int) ($dashboardData['maleCount'] ?? 0);
    $femaleCount = (int) ($dashboardData['femaleCount'] ?? 0);
    $applicantCount = (int) ($dashboardData['applicantCount'] ?? 0);
    $facultyCount = (int) ($dashboardData['facultyCount'] ?? 0);
    $departmentCount = (int) ($dashboardData['departmentCount'] ?? 0);
    $trendPercent = (float) ($dashboardData['trendPercent'] ?? 0);
    $trendPercentText = ($trendPercent >= 0 ? '+' : '') . rtrim(rtrim(number_format($trendPercent, 1), '0'), '.') . '%';
    $sparklinePath = $dashboardData['sparklinePath'] ?? 'M 2,44 L 24,38 L 46,33 L 68,26 L 90,22 L 108,18';
    $sparklineAreaPath = $dashboardData['sparklineAreaPath'] ?? 'M 2,44 L 24,38 L 46,33 L 68,26 L 90,22 L 108,18 L 108,56 L 2,56 Z';
    $todayLabel = 'Today, ' . now()->format('j M Y');
@endphp
<div class="reg-dashboard">

    {{-- ── Top stat cards ── --}}
    <div class="reg-dash-top">

        {{-- Total Students --}}
        <div class="reg-stat-card">
            <div class="reg-stat-card-title">Total Students</div>
            <div class="reg-stat-card-inner">
                <div class="reg-stat-left">
                    <div class="reg-stat-number">{{ $studentCount }}</div>
                    <div class="reg-stat-sub">
                        <span>{{ $maleCount }} Men</span>
                        <span>{{ $femaleCount }} Women</span>
                    </div>
                </div>
                <div class="reg-stat-right">
                    <svg class="reg-stat-sparkline" viewBox="0 0 110 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <text x="62" y="11" font-size="9" fill="#006837" font-weight="700" font-family="Poppins,sans-serif">{{ $trendPercentText }}</text>
                        <text x="67" y="20" font-size="10" fill="#006837" font-family="Poppins,sans-serif">&#x2191;</text>
                        <path d="{{ $sparklinePath }}"
                            fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="{{ $sparklineAreaPath }}"
                            fill="rgba(0,104,55,0.07)"/>
                    </svg>
                    <div class="reg-stat-badge">{{ $trendPercentText }} Past month</div>
                </div>
            </div>
        </div>

        {{-- Applicants --}}
        <div class="reg-applicants-card">
            <div class="reg-stat-card-title">Applicants</div>
            <div class="reg-stat-number">{{ $applicantCount }}</div>
            <div class="reg-applicants-dept">{{ $departmentCount }} Department</div>
        </div>

        {{-- Faculty --}}
        <div class="reg-faculty-card">
            <div class="reg-stat-card-title">Faculty</div>
            <div class="reg-stat-number">{{ $facultyCount }}</div>
            <div class="reg-faculty-dept">{{ $departmentCount }} Department</div>
        </div>

    </div>

    {{-- ── Bottom section ── --}}
    <div class="reg-dash-bottom d-grid">

        {{-- Announcements --}}
        <div class="reg-announce-card">
            <div class="reg-card-header">
                <div class="reg-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                    Announcement
                </div>
                <div class="reg-card-date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ $todayLabel }}
                </div>
            </div>

            <div class="reg-announce-item pinned">
                <div class="reg-announce-dot"></div>
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Outing schedule for every departement</div>
                    <div class="reg-announce-time">5 Minutes ago</div>
                </div>
                <button type="button" class="reg-icon-btn reg-pin-btn active" title="Pinned" aria-pressed="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2m-6 2V2"/><path d="M8 4h8l-1 7 3 3v2H6v-2l3-3-1-7z"/><path d="M12 16v6"/></svg>
                </button>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="archive">Archive</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-announce-item">
                <div class="reg-announce-dot"></div>
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Meeting HR Department</div>
                    <div class="reg-announce-time">Yesterday, 12:30 PM</div>
                </div>
                <button type="button" class="reg-icon-btn reg-pin-btn" title="Pin" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2m-6 2V2"/><path d="M8 4h8l-1 7 3 3v2H6v-2l3-3-1-7z"/><path d="M12 16v6"/></svg>
                </button>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="archive">Archive</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-announce-item">
                <div class="reg-announce-dot"></div>
                <div class="reg-announce-text">
                    <div class="reg-announce-title">IT Department need two more talents for UX/UI Designer position</div>
                    <div class="reg-announce-time">Yesterday, 09:15 AM</div>
                </div>
                <button type="button" class="reg-icon-btn reg-pin-btn" title="Pin" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2m-6 2V2"/><path d="M8 4h8l-1 7 3 3v2H6v-2l3-3-1-7z"/><path d="M12 16v6"/></svg>
                </button>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="archive">Archive</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-announce-item reg-announce-extra" hidden>
                <div class="reg-announce-dot"></div>
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Deadline for grade submission moved to Friday</div>
                    <div class="reg-announce-time">Mar 27, 2026 · 04:20 PM</div>
                </div>
                <button type="button" class="reg-icon-btn reg-pin-btn" title="Pin" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2m-6 2V2"/><path d="M8 4h8l-1 7 3 3v2H6v-2l3-3-1-7z"/><path d="M12 16v6"/></svg>
                </button>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="archive">Archive</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-announce-item reg-announce-extra" hidden>
                <div class="reg-announce-dot"></div>
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Room reassignment for Computer Lab sections</div>
                    <div class="reg-announce-time">Mar 26, 2026 · 11:10 AM</div>
                </div>
                <button type="button" class="reg-icon-btn reg-pin-btn" title="Pin" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2m-6 2V2"/><path d="M8 4h8l-1 7 3 3v2H6v-2l3-3-1-7z"/><path d="M12 16v6"/></svg>
                </button>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="archive">Archive</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-announce-item reg-announce-extra" hidden>
                <div class="reg-announce-dot"></div>
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Scholarship renewal list is available for review</div>
                    <div class="reg-announce-time">Mar 25, 2026 · 03:45 PM</div>
                </div>
                <button type="button" class="reg-icon-btn reg-pin-btn" title="Pin" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4V2m-6 2V2"/><path d="M8 4h8l-1 7 3 3v2H6v-2l3-3-1-7z"/><path d="M12 16v6"/></svg>
                </button>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="archive">Archive</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <a href="#" class="reg-see-all" data-expanded="false">See All Announcement</a>
        </div>

        {{-- Upcoming Schedule --}}
        <div class="reg-schedule-card">
            <div class="reg-card-header">
                <div class="reg-card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#006837" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Upcoming Schedule
                </div>
                <div class="reg-card-date">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $todayLabel }}
                </div>
            </div>

            <div class="reg-sched-section-tag reg-sched-section-tag-priority">Priority Tasks</div>

            <div class="reg-sched-item">
                <div class="reg-sched-color-bar priority-high"></div>
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Review candidate applications</div>
                    <div class="reg-sched-time">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Today &middot; 11:30 AM
                    </div>
                </div>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="mark-done">Mark as done</button>
                        <button type="button" class="reg-more-action" data-action="move-other">Move to Other</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-sched-section-tag reg-sched-section-tag-other">Other Tasks</div>

            <div class="reg-sched-item">
                <div class="reg-sched-color-bar priority-normal"></div>
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Interview with candidates</div>
                    <div class="reg-sched-time">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Today &middot; 10:30 AM
                    </div>
                </div>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="mark-done">Mark as done</button>
                        <button type="button" class="reg-more-action" data-action="move-priority">Move to Priority</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

            <div class="reg-sched-item">
                <div class="reg-sched-color-bar priority-normal"></div>
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Short meeting with product designer from IT Departement</div>
                    <div class="reg-sched-time">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Today &middot; 09:15 AM
                    </div>
                </div>
                <div class="reg-more-wrap">
                    <button type="button" class="reg-icon-btn reg-more-toggle" title="More options" aria-haspopup="menu" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                    </button>
                    <div class="reg-more-menu" role="menu" hidden>
                        <button type="button" class="reg-more-action" data-action="edit">Edit title</button>
                        <button type="button" class="reg-more-action" data-action="mark-done">Mark as done</button>
                        <button type="button" class="reg-more-action" data-action="move-priority">Move to Priority</button>
                        <button type="button" class="reg-more-action danger" data-action="remove">Remove</button>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="pf-modal-overlay" id="regEditModal" aria-hidden="true" style="display:none;">
        <div class="pf-modal-box" role="dialog" aria-modal="true" aria-labelledby="regEditModalTitle" style="max-width:480px;">
            <div class="pf-modal-title" id="regEditModalTitle">Edit title</div>
            <div class="pf-modal-form">
                <div class="pf-modal-field">
                    <label class="pf-modal-label" for="regEditModalInput">Title</label>
                    <input type="text" id="regEditModalInput" class="pf-modal-input" maxlength="180">
                </div>
                <div class="pf-modal-actions">
                    <button type="button" class="pf-modal-btn-cancel" id="regEditCancelBtn">Cancel</button>
                    <button type="button" class="pf-modal-btn-save" id="regEditSaveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-dashboard.js') }}?v={{ time() }}"></script>
@endpush
