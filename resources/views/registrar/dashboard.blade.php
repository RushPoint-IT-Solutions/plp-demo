@extends('layouts.registrar')

@section('title', 'PLP - Registrar Dashboard')
@section('page-title', 'DASHBOARD')

@section('content')
<div class="reg-dashboard">

    {{-- ── Top stat cards ── --}}
    <div class="reg-dash-top d-flex flex-column flex-md-row">

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
                        {{-- +2% label and arrow above the line --}}
                        <text x="62" y="11" font-size="9" fill="#006837" font-weight="700" font-family="Poppins,sans-serif">+2%</text>
                        <text x="67" y="20" font-size="10" fill="#006837" font-family="Poppins,sans-serif">↑</text>
                        {{-- S-curve line --}}
                        <path d="M 0,56 C 12,55 20,51 32,45 C 44,39 50,28 64,23 C 74,19 88,18 110,16"
                            fill="none" stroke="#006837" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        {{-- subtle fill under line --}}
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

    </div>

    {{-- ── Bottom section ── --}}
    <div class="reg-dash-bottom d-grid">

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
