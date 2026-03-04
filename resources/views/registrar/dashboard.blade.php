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
                <div>
                    <div class="reg-stat-number">216</div>
                    <div class="reg-stat-sub">
                        <span>120 Men</span>
                        <span>96 Women</span>
                    </div>
                    <div class="reg-stat-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        +2% Past month
                    </div>
                </div>
<svg class="reg-stat-sparkline" viewBox="0 0 100 55" fill="none" xmlns="http://www.w3.org/2000/svg">
                    {{-- S-curve: starts flat low, curves up steeply in the middle, levels off high --}}
                    <path d="M 0,48 C 10,47 18,44 28,38 C 38,32 42,18 55,13 C 65,9 78,8 100,6"
                        fill="none" stroke="#e05070" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="58" y="9" font-size="9" fill="#e05070" font-weight="700" font-family="Poppins,sans-serif">+2%</text>
                    <text x="63" y="19" font-size="10" fill="#e05070" font-family="Poppins,sans-serif">↑</text>
                </svg>
            </div>
        </div>

        {{-- Applicants --}}
        <div class="reg-applicants-card">
            <div class="reg-stat-card-title">Applicants</div>
            <div class="reg-stat-number">24</div>
            <div class="reg-applicants-dept">4 Department</div>
        </div>

    </div>

    {{-- ── Bottom section ── --}}
    <div class="reg-dash-bottom">

        {{-- Announcements --}}
        <div class="reg-announce-card">
            <div class="reg-card-header">
                <div class="reg-card-title">Announcement</div>
                <div class="reg-card-date">
                    Today, 13 Sep 2021
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>

            <div class="reg-announce-item">
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Outing schedule for every departement</div>
                    <div class="reg-announce-time">5 Minutes ago</div>
                </div>
                <div class="reg-announce-actions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#555" stroke="none"><path d="M5 5a2 2 0 012-2h8a2 2 0 012 2v3h3a1 1 0 010 2h-1v11a1 1 0 01-1 1H6a1 1 0 01-1-1V10H4a1 1 0 010-2h3V5zm2 0v3h6V5H7zm-1 5v10h10V10H6z"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
                </div>
            </div>

            <div class="reg-announce-item">
                <div class="reg-announce-text">
                    <div class="reg-announce-title">Meeting HR Department</div>
                    <div class="reg-announce-time">Yesterday, 12:30 PM</div>
                </div>
                <div class="reg-announce-actions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
                </div>
            </div>

            <div class="reg-announce-item">
                <div class="reg-announce-text">
                    <div class="reg-announce-title">IT Department need two more talents for UX/UI Designer position</div>
                    <div class="reg-announce-time">Yesterday, 09:15 AM</div>
                </div>
                <div class="reg-announce-actions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
                </div>
            </div>

            <a href="#" class="reg-see-all">See All Announcement</a>
        </div>

        {{-- Upcoming Schedule --}}
        <div class="reg-schedule-card">
            <div class="reg-card-header">
                <div class="reg-card-title">Upcoming Schedule</div>
                <div class="reg-card-date">
                    Today, 13 Sep 2021
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>

            <div class="reg-sched-priority-label">Priority</div>

            <div class="reg-sched-item">
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Review candidate applications</div>
                    <div class="reg-sched-time">Today · 11:30 AM</div>
                </div>
                <div class="reg-sched-dots">···</div>
            </div>

            <div class="reg-sched-other-label">Other</div>

            <div class="reg-sched-item">
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Interview with candidates</div>
                    <div class="reg-sched-time">Today · 10:30 AM</div>
                </div>
                <div class="reg-sched-dots">···</div>
            </div>

            <div class="reg-sched-item">
                <div class="reg-sched-info">
                    <div class="reg-sched-name">Short meeting with product designer from IT Departement</div>
                    <div class="reg-sched-time">Today · 09:15 AM</div>
                </div>
                <div class="reg-sched-dots">···</div>
            </div>

        </div>

    </div>
</div>
@endsection
