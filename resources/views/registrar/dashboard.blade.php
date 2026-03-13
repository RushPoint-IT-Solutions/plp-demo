@extends('layouts.registrar')

@section('title', 'PLP - Registrar Dashboard')
@section('page-title', 'DASHBOARD')

@section('content')
<div class="reg-dashboard">

    {{-- ── Top stat cards ── --}}
    <div class="reg-dash-top">

        {{-- Total Students --}}
        <div class="reg-stat-card">
            <div class="reg-stat-card-title">Total Students</div>
            <div class="reg-stat-card-inner">
                <div class="reg-stat-left">
                    <div class="reg-stat-number">216</div>
                    <div class="reg-stat-sub">
                        <span>120 Men</span>
                        <span>96 Women</span>
                    </div>
                </div>
                <div class="reg-stat-right">
                    <svg class="reg-stat-sparkline" viewBox="0 0 110 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <text x="62" y="11" font-size="9" fill="#006837" font-weight="700" font-family="Poppins,sans-serif">+2%</text>
                        <text x="67" y="20" font-size="10" fill="#006837" font-family="Poppins,sans-serif">&#x2191;</text>
                        <path d="M 0,56 C 12,55 20,51 32,45 C 44,39 50,28 64,23 C 74,19 88,18 110,16"
                            fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M 0,56 C 12,55 20,51 32,45 C 44,39 50,28 64,23 C 74,19 88,18 110,16 L 110,60 L 0,60 Z"
                            fill="rgba(0,104,55,0.07)"/>
                    </svg>
                    <div class="reg-stat-badge">+2% Past month</div>
                </div>
            </div>
        </div>

        {{-- Applicants --}}
        <div class="reg-applicants-card">
            <div class="reg-stat-card-title">Applicants</div>
            <div class="reg-stat-number">24</div>
            <div class="reg-applicants-dept">4 Department</div>
        </div>

        {{-- Faculty --}}
        <div class="reg-faculty-card">
            <div class="reg-stat-card-title">Faculty</div>
            <div class="reg-stat-number">10</div>
            <div class="reg-faculty-dept">4 Department</div>
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
                    Today, 13 Sep 2021
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
                <button type="button" class="reg-icon-btn" title="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
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
                <button type="button" class="reg-icon-btn" title="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
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
                <button type="button" class="reg-icon-btn" title="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
            </div>

            <a href="#" class="reg-see-all">See All Announcement</a>
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
                    Today, 13 Sep 2021
                </div>
            </div>

            <div class="reg-sched-priority-label">
                <span class="reg-sched-priority-dot priority-high"></span>
                Priority
            </div>

            <div class="reg-sched-item">
                <div class="reg-sched-color-bar priority-high"></div>
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Review candidate applications</div>
                    <div class="reg-sched-time">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Today &middot; 11:30 AM
                    </div>
                </div>
                <button class="reg-icon-btn" title="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
            </div>

            <div class="reg-sched-other-label">
                <span class="reg-sched-priority-dot priority-normal"></span>
                Other
            </div>

            <div class="reg-sched-item">
                <div class="reg-sched-color-bar priority-normal"></div>
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Interview with candidates</div>
                    <div class="reg-sched-time">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Today &middot; 10:30 AM
                    </div>
                </div>
                <button class="reg-icon-btn" title="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
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
                <button class="reg-icon-btn" title="More options">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var card = document.querySelector('.reg-announce-card');
    if (!card) return;

    var seeAll = card.querySelector('.reg-see-all');

    function setPinned(item, pinned) {
        item.classList.toggle('pinned', pinned);
        var btn = item.querySelector('.reg-pin-btn');
        if (!btn) return;
        btn.classList.toggle('active', pinned);
        btn.setAttribute('aria-pressed', pinned ? 'true' : 'false');
        btn.title = pinned ? 'Pinned' : 'Pin';
    }

    card.querySelectorAll('.reg-pin-btn').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            var item = btn.closest('.reg-announce-item');
            if (!item) return;
            var willPin = !item.classList.contains('pinned');
            setPinned(item, willPin);

            if (willPin) {
                var firstItem = card.querySelector('.reg-announce-item');
                if (firstItem && firstItem !== item) {
                    card.insertBefore(item, firstItem);
                }
                return;
            }

            if (seeAll) {
                card.insertBefore(item, seeAll);
            }
        });
    });
});
</script>
@endpush
