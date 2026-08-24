<aside class="plp-sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <div class="d-flex flex-column lh-1">
            <span class="sidebar-title" style="font-size:14px; color:#dfc937; margin-bottom:2px;">
                PAMANTASAN NG LUNGSOD NG PASIG
            </span>
            <small class="sidebar-subtitle" style="text-align: center; font-size:12px; color:#e1d5d5; margin-top:0;">
                UNIVERSITY OF PASIG CITY
            </small>
        </div>
    </div>

    @php
        $authUser = auth()->user();
        $canView = function (string $routeName) use ($authUser) {
            return \App\Support\UserAccessGate::allowsRoute($authUser, $routeName);
        };
        $canViewAny = function (array $routeNames) use ($canView) {
            foreach ($routeNames as $routeName) {
                if ($canView($routeName)) {
                    return true;
                }
            }
            return false;
        };
    @endphp
    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        @if($canView('registrar.dashboard'))
        <a href="{{ route('registrar.dashboard') }}" class="sidebar-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M9.02 2.84004L3.63 7.04004C2.73 7.74004 2 9.23004 2 10.36V17.77C2 20.09 3.89 21.99 6.21 21.99H17.79C20.11 21.99 22 20.09 22 17.78V10.5C22 9.29004 21.19 7.74004 20.2 7.05004L14.02 2.72004C12.62 1.74004 10.37 1.79004 9.02 2.84004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17.99V14.99" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Dashboard</span>
        </a>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- Communication --}}
        @if($canViewAny(['registrar.communication.tickets', 'registrar.communication.stakeholders', 'registrar.communication.email-templates']))
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.communication.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.communication.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15C21 15.53 20.79 16.04 20.41 16.41C20.04 16.79 19.53 17 19 17H7L3 21V5C3 4.47 3.21 3.96 3.59 3.59C3.96 3.21 4.47 3 5 3H19C19.53 3 20.04 3.21 20.41 3.59C20.79 3.96 21 4.47 21 5V15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Communication</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                @if($canView('registrar.communication.tickets'))<a href="{{ route('registrar.communication.tickets') }}" class="sidebar-sublink {{ request()->routeIs('registrar.communication.tickets') ? 'active' : '' }}">Ticketing System</a>@endif
                @if($canView('registrar.communication.stakeholders'))<a href="{{ route('registrar.communication.stakeholders') }}" class="sidebar-sublink {{ request()->routeIs('registrar.communication.stakeholders') ? 'active' : '' }}">Stakeholder Communication</a>@endif
                @if($canView('registrar.communication.email-templates'))<a href="{{ route('registrar.communication.email-templates') }}" class="sidebar-sublink {{ request()->routeIs('registrar.communication.email-templates') ? 'active' : '' }}">Email Notifications & Templates</a>@endif
            </div>
        </div>
        @endif

        {{-- ADMISSIONS                             --}}
        {{-- ══════════════════════════════════════ --}}
        {{-- Hidden from the sidebar for all users. Remove the `false &&` below to restore. --}}
        @if(false && $canViewAny(['registrar.process.application', 'registrar.process.document-list', 'registrar.process.approval-status', 'registrar.process.exam-list', 'registrar.process.exam-interview-scheduling', 'registrar.process.requirements', 'registrar.process.batch-upload', 'registrar.process.citizenship', 'registrar.process.religion.index', 'registrar.process.exam-category']))
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.process.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.process.*') ? 'active' : '' }}">
                {{-- user-add icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.41003 22C3.41003 18.13 7.26003 15 12 15C12.96 15 13.89 15.13 14.76 15.37" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 18C22 18.75 21.79 19.46 21.42 20.06C21.21 20.42 20.94 20.74 20.63 21C19.93 21.63 19.01 22 18 22C16.54 22 15.27 21.22 14.58 20.06C14.21 19.46 14 18.75 14 18C14 16.74 14.58 15.61 15.5 14.88C16.19 14.33 17.06 14 18 14C20.21 14 22 15.79 22 18Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16.44 18H19.56" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18 16.48V19.61" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Admissions</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <div class="sidebar-section-label">Applications</div>
                @if($canView('registrar.process.application'))<a href="{{ route('registrar.process.application') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.application') ? 'active' : '' }}">Application List</a>@endif
                @if($canView('registrar.process.document-list'))<a href="{{ route('registrar.process.document-list') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.document-list') ? 'active' : '' }}">Document Submission</a>@endif
                @if($canView('registrar.process.approval-status'))<a href="{{ route('registrar.process.approval-status') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.approval-status') ? 'active' : '' }}">Approval Status</a>@endif
                @if($canView('registrar.process.exam-list'))<a href="{{ route('registrar.process.exam-list') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.exam-list') ? 'active' : '' }}">Exam Schedule</a>@endif
                @if($canView('registrar.process.exam-interview-scheduling'))<a href="{{ route('registrar.process.exam-interview-scheduling') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.exam-interview-scheduling') ? 'active' : '' }}">Exam & Interview Scheduling</a>@endif
                @if($canView('registrar.process.requirements'))<a href="{{ route('registrar.process.requirements') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.requirements') ? 'active' : '' }}">Requirements</a>@endif
                @if($canView('registrar.process.batch-upload'))<a href="{{ route('registrar.process.batch-upload') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.batch-upload') ? 'active' : '' }}">Batch Upload Photos</a>@endif

                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section-label">Lookups</div>
                @if($canView('registrar.process.citizenship'))<a href="{{ route('registrar.process.citizenship') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.citizenship') ? 'active' : '' }}">Citizenship</a>@endif
                @if($canView('registrar.process.religion.index'))<a href="{{ route('registrar.process.religion.index') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.religion*') ? 'active' : '' }}">Religion</a>@endif
                @if($canView('registrar.process.exam-category'))<a href="{{ route('registrar.process.exam-category') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.exam-category') ? 'active' : '' }}">Exam Category</a>@endif
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- STUDENT RECORDS                        --}}
        {{-- ══════════════════════════════════════ --}}
        @php
            $studentRecordsActive = request()->routeIs('registrar.registrar-menu.student-mgmt.*')
                                 || request()->routeIs('registrar.services.student-account.*')
                                 || request()->routeIs('registrar.registrar-menu.alumni.*')
                                //  || request()->routeIs('registrar.services.classroom-faculty.class-list')
                                 || request()->routeIs('registrar.services.section-list');
        @endphp
        @if($canViewAny(['registrar.registrar-menu.student-mgmt.student-records', 'registrar.services.section-list', 'registrar.registrar-menu.alumni.tracker', 'registrar.registrar-menu.student-mgmt.student-enrollment', 'registrar.registrar-menu.student-mgmt.clinic-record', 'registrar.services.student-account.student-discipline', 'registrar.services.student-account.change-password']))
        <div class="sidebar-dropdown {{ $studentRecordsActive ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ $studentRecordsActive ? 'active' : '' }}">
                {{-- student icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20.59 22C20.59 18.13 16.74 15 12 15C7.26 15 3.41 18.13 3.41 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Student Records</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <div class="sidebar-section-label">Student Database</div>
                @if($canView('registrar.registrar-menu.student-mgmt.student-records'))<a href="{{ route('registrar.registrar-menu.student-mgmt.student-records') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.student-mgmt.student-records*') ? 'active' : '' }}">Student List</a>@endif
                {{-- <a href="{{ route('registrar.services.classroom-faculty.class-list') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.classroom-faculty.class-list') ? 'active' : '' }}">Class List</a> --}}
                @if($canView('registrar.services.section-list'))<a href="{{ route('registrar.services.section-list') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.section-list') ? 'active' : '' }}">Section List</a>@endif
                @if($canView('registrar.registrar-menu.alumni.tracker'))<a href="{{ route('registrar.registrar-menu.alumni.tracker') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.alumni.tracker') ? 'active' : '' }}">Alumni Tracker</a>@endif
                @if($canView('registrar.registrar-menu.student-mgmt.student-enrollment'))<a href="{{ route('registrar.registrar-menu.student-mgmt.student-enrollment') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.student-mgmt.student-enrollment') ? 'active' : '' }}">Enrollment List</a>@endif
                {{-- Clinic Records link hidden from navbar per request --}}

                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section-label">Student Affairs</div>
                {{-- Student Discipline hidden from the navbar per request. --}}
                {{-- Family Records hidden from the navbar per request. --}}
                @if($canView('registrar.services.student-account.change-password'))<a href="{{ route('registrar.services.student-account.change-password') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.student-account.change-password') ? 'active' : '' }}">Change Password</a>@endif
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- ACADEMICS                              --}}
        {{-- ══════════════════════════════════════ --}}
        {{-- SCHOLARSHIP MODULE                    --}}
        {{-- Hidden from the sidebar per request. Remove the `false &&` below to restore. --}}
        @if(false && $canViewAny(['registrar.registrar-menu.scholarships.index', 'registrar.registrar-menu.scholarships.report']))
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.registrar-menu.scholarships.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.registrar-menu.scholarships.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3L3 7.5L12 12L21 7.5L12 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10V15.5C5 17.43 8.13 19 12 19C15.87 19 19 17.43 19 15.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 7.5V13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Scholarship</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                @if($canView('registrar.registrar-menu.scholarships.index'))<a href="{{ route('registrar.registrar-menu.scholarships.index') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.scholarships.index') ? 'active' : '' }}">Program Setup</a>@endif
                @if($canView('registrar.registrar-menu.scholarships.report'))<a href="{{ route('registrar.registrar-menu.scholarships.report') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.scholarships.report') ? 'active' : '' }}">Scholars by Type</a>@endif
            </div>
        </div>
        @endif

        @php
            $academicsActive = request()->routeIs('registrar.registrar-menu.academic-master.*')
                            || request()->routeIs('registrar.registrar-menu.scheduling.*')
                            || request()->routeIs('registrar.services.classroom-faculty.*')
                            || request()->routeIs('registrar.services.grading-academic.*');
        @endphp
        @php
            $curriculumRoutes = ['registrar.registrar-menu.academic-master.program-file', 'registrar.registrar-menu.academic-master.subject-file', 'registrar.registrar-menu.academic-master.curriculum-file', 'registrar.registrar-menu.academic-master.curriculum-year-tracking', 'registrar.registrar-menu.academic-master.pre-requisites'];
            $schedulingRoutes = ['registrar.registrar-menu.scheduling.academic-term-lifecycle', 'registrar.registrar-menu.scheduling.promotion-readiness', 'registrar.registrar-menu.scheduling.academic-setup-automation', 'registrar.registrar-menu.scheduling.room-file', 'registrar.registrar-menu.scheduling.room-generation-assignment', 'registrar.registrar-menu.scheduling.teacher-generation-assignment', 'registrar.registrar-menu.scheduling.room-section-offering-management', 'registrar.registrar-menu.scheduling.section-offering', 'registrar.registrar-menu.scheduling.class-schedule-preparation', 'registrar.registrar-menu.scheduling.slot-monitoring', 'registrar.registrar-menu.scheduling.section-merging', 'registrar.registrar-menu.scheduling.coordination-deans-faculty'];
            $classroomRoutes = ['registrar.services.classroom-faculty.class-list', 'registrar.services.classroom-faculty.attendance', 'registrar.services.classroom-faculty.faculty-loads.index'];
            $gradingSetupRoutes = ['registrar.services.grading-academic.grading-system', 'registrar.services.grading-academic.grading-periods', 'registrar.services.grading-academic.grading-components', 'registrar.services.grading-academic.transmutation', 'registrar.services.grading-academic.incomplete-failing', 'registrar.services.grading-academic.deficiency', 'registrar.services.grading-academic.scholastic-comments'];
        @endphp
        @if($canViewAny(array_merge($curriculumRoutes, $schedulingRoutes, $classroomRoutes, $gradingSetupRoutes)))
        <div class="sidebar-dropdown {{ $academicsActive ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ $academicsActive ? 'active' : '' }}">
                {{-- book icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M22 16.74V4.67C22 3.47 21.02 2.58 19.83 2.68H19.77C17.67 2.86 14.48 3.93 12.7 5.05L12.53 5.16C12.24 5.34 11.76 5.34 11.47 5.16L11.22 5.01C9.44 3.9 6.26 2.84 4.16 2.67C2.97 2.57 2 3.47 2 4.66V16.74C2 17.7 2.78 18.6 3.74 18.72L4.03 18.76C6.2 19.05 9.55 20.15 11.47 21.2L11.51 21.22C11.78 21.37 12.21 21.37 12.47 21.22C14.39 20.16 17.75 19.05 19.93 18.76L20.26 18.72C21.22 18.6 22 17.7 22 16.74Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 5.49V20.49" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7.75 8.49H5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.5 11.49H5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Academics</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">

                {{-- Curriculum --}}
                @if($canViewAny($curriculumRoutes))
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.academic-master.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.academic-master.*') ? 'active' : '' }}">
                        Curriculum
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.registrar-menu.academic-master.program-file'))<a href="{{ route('registrar.registrar-menu.academic-master.program-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.program-file') ? 'active' : '' }}">Program File</a>@endif
                        @if($canView('registrar.registrar-menu.academic-master.subject-file'))<a href="{{ route('registrar.registrar-menu.academic-master.subject-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.subject-file') ? 'active' : '' }}">Course File</a>@endif
                        @if($canView('registrar.registrar-menu.academic-master.curriculum-file'))<a href="{{ route('registrar.registrar-menu.academic-master.curriculum-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.curriculum-file') ? 'active' : '' }}">Curriculum File</a>@endif
                        @if($canView('registrar.registrar-menu.academic-master.curriculum-year-tracking'))<a href="{{ route('registrar.registrar-menu.academic-master.curriculum-year-tracking') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.curriculum-year-tracking') ? 'active' : '' }}">Curriculum Year Tracking</a>@endif
                        @if($canView('registrar.registrar-menu.academic-master.pre-requisites'))<a href="{{ route('registrar.registrar-menu.academic-master.pre-requisites') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.pre-requisites') ? 'active' : '' }}">Pre-requisites</a>@endif
                    </div>
                </div>
                @endif

                {{-- Scheduling --}}
                @if($canViewAny($schedulingRoutes))
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.scheduling.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.scheduling.*') ? 'active' : '' }}">
                        Term Setup & Scheduling
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <div class="sidebar-section-label">Term Process</div>
                        @if($canView('registrar.registrar-menu.scheduling.academic-term-lifecycle'))<a href="{{ route('registrar.registrar-menu.scheduling.academic-term-lifecycle') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.academic-term-lifecycle*') ? 'active' : '' }}">1. Close / Open Term</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.promotion-readiness'))<a href="{{ route('registrar.registrar-menu.scheduling.promotion-readiness') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.promotion-readiness*') ? 'active' : '' }}">2. Promotion Readiness</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.academic-setup-automation'))<a href="{{ route('registrar.registrar-menu.scheduling.academic-setup-automation') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.academic-setup-automation*') ? 'active' : '' }}">3. Generate Academic Setup</a>@endif

                        <div class="sidebar-section-divider"></div>
                        <div class="sidebar-section-label">Rooms</div>
                        @if($canView('registrar.registrar-menu.scheduling.room-file'))<a href="{{ route('registrar.registrar-menu.scheduling.room-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.room-file') ? 'active' : '' }}">Room File</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.room-generation-assignment'))<a href="{{ route('registrar.registrar-menu.scheduling.room-generation-assignment') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.room-generation-assignment*') ? 'active' : '' }}">Generate & Assign Rooms</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.teacher-generation-assignment'))<a href="{{ route('registrar.registrar-menu.scheduling.teacher-generation-assignment') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.teacher-generation-assignment*') ? 'active' : '' }}">Generate & Assign Teachers</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.room-section-offering-management'))<a href="{{ route('registrar.registrar-menu.scheduling.room-section-offering-management') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.room-section-offering-management') ? 'active' : '' }}">Room & Section Offering</a>@endif

                        <div class="sidebar-section-divider"></div>
                        <div class="sidebar-section-label">Class Scheduling</div>
                        @if($canView('registrar.registrar-menu.scheduling.section-offering'))<a href="{{ route('registrar.registrar-menu.scheduling.section-offering') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.section-offering') ? 'active' : '' }}">Section Offering</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.class-schedule-preparation'))<a href="{{ route('registrar.registrar-menu.scheduling.class-schedule-preparation') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.class-schedule-preparation') ? 'active' : '' }}">Class Schedule Preparation</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.slot-monitoring'))<a href="{{ route('registrar.registrar-menu.scheduling.slot-monitoring') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.slot-monitoring') ? 'active' : '' }}">Slot Monitoring & Editing</a>@endif
                        @if($canView('registrar.registrar-menu.scheduling.section-merging'))<a href="{{ route('registrar.registrar-menu.scheduling.section-merging') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.section-merging') ? 'active' : '' }}">Section Merging</a>@endif

                        <div class="sidebar-section-divider"></div>
                        <div class="sidebar-section-label">Coordination</div>
                        @if($canView('registrar.registrar-menu.scheduling.coordination-deans-faculty'))<a href="{{ route('registrar.registrar-menu.scheduling.coordination-deans-faculty') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.coordination-deans-faculty') ? 'active' : '' }}">Deans & Faculty Coordination</a>@endif
                    </div>
                </div>
                @endif

                {{-- Classroom --}}
                @if($canViewAny($classroomRoutes))
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.services.classroom-faculty.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.services.classroom-faculty.*') ? 'active' : '' }}">
                        Classroom
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.services.classroom-faculty.class-list'))<a href="{{ route('registrar.services.classroom-faculty.class-list') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.classroom-faculty.class-list') ? 'active' : '' }}">Class List</a>@endif
                        @if($canView('registrar.services.classroom-faculty.attendance'))<a href="{{ route('registrar.services.classroom-faculty.attendance') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.classroom-faculty.attendance') ? 'active' : '' }}">Attendance</a>@endif
                        @if($canView('registrar.services.classroom-faculty.faculty-loads.index'))<a href="{{ route('registrar.services.classroom-faculty.faculty-loads.index') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.classroom-faculty.faculty-loads.*') ? 'active' : '' }}">Faculty Loads</a>@endif
                    </div>
                </div>
                @endif

                {{-- Grading Setup --}}
                @if($canViewAny($gradingSetupRoutes))
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.services.grading-academic.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.services.grading-academic.*') ? 'active' : '' }}">
                        Grading Setup
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.services.grading-academic.grading-system'))<a href="{{ route('registrar.services.grading-academic.grading-system') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.grading-system') ? 'active' : '' }}">Grading System</a>@endif
                        @if($canView('registrar.services.grading-academic.grading-periods'))<a href="{{ route('registrar.services.grading-academic.grading-periods') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.grading-periods') ? 'active' : '' }}">Grading Periods</a>@endif
                        @if($canView('registrar.services.grading-academic.grading-components'))<a href="{{ route('registrar.services.grading-academic.grading-components') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.grading-components') ? 'active' : '' }}">Grading Components</a>@endif
                        @if($canView('registrar.services.grading-academic.transmutation'))<a href="{{ route('registrar.services.grading-academic.transmutation') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.transmutation') ? 'active' : '' }}">Transmutation Table</a>@endif
                        @if($canView('registrar.services.grading-academic.incomplete-failing'))
                        @php $incompleteFailingBadge = \App\Http\Controllers\Registrar\Services\GradingAcademicController::incompleteFailingBadgeCount(); @endphp
                        <a href="{{ route('registrar.services.grading-academic.incomplete-failing') }}" class="sidebar-sublink sidebar-nested-sublink sidebar-sublink-with-badge {{ request()->routeIs('registrar.services.grading-academic.incomplete-failing') ? 'active' : '' }}">
                            <span>Incomplete &amp; Failing</span>
                            @if($incompleteFailingBadge > 0)<span class="sidebar-alert-badge">{{ $incompleteFailingBadge > 99 ? '99+' : $incompleteFailingBadge }}</span>@endif
                        </a>
                        @endif
                        @if($canView('registrar.services.grading-academic.deficiency'))<a href="{{ route('registrar.services.grading-academic.deficiency') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.deficiency') ? 'active' : '' }}">Deficiency</a>@endif
                        @if($canView('registrar.services.grading-academic.scholastic-comments'))<a href="{{ route('registrar.services.grading-academic.scholastic-comments') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.scholastic-comments') ? 'active' : '' }}">Scholastic Comments</a>@endif
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- FACULTY                                --}}
        {{-- ══════════════════════════════════════ --}}
        @if($canViewAny(['registrar.registrar-menu.faculty-mgmt.faculty-list', 'registrar.registrar-menu.faculty-mgmt.departments', 'registrar.registrar-menu.faculty-mgmt.faculty-create', 'registrar.registrar-menu.faculty-mgmt.grading-sheet', 'registrar.registrar-menu.faculty-mgmt.upload-grades', 'registrar.registrar-menu.faculty-mgmt.evaluation']))
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.*') ? 'active' : '' }}">
                {{-- teacher icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M10.05 2.53004L4.03 6.46004C2.1 7.48004 2.1 10.54 4.03 11.56L10.05 15.49C11.13 16.17 12.91 16.17 13.99 15.49L19.98 11.56C21.9 10.54 21.9 7.49004 19.98 6.47004L13.99 2.54004C12.91 1.86004 11.13 1.86004 10.05 2.53004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.63 13.08L5.62 17.77C5.62 18.79 6.35 19.9 7.26 20.22L10.69 21.39C11.38 21.63 12.52 21.63 13.22 21.39L16.65 20.22C17.56 19.9 18.29 18.79 18.29 17.77V13.13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21.4 15V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Faculty</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                @if($canView('registrar.registrar-menu.faculty-mgmt.faculty-list'))<a href="{{ route('registrar.registrar-menu.faculty-mgmt.faculty-list') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.faculty-list*') ? 'active' : '' }}">Faculty Profiles</a>@endif
                @if($canView('registrar.registrar-menu.faculty-mgmt.departments'))<a href="{{ route('registrar.registrar-menu.faculty-mgmt.departments') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.departments*') ? 'active' : '' }}">Departments</a>@endif
                @if($canView('registrar.registrar-menu.faculty-mgmt.faculty-create'))<a href="{{ route('registrar.registrar-menu.faculty-mgmt.faculty-create') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.faculty-create') ? 'active' : '' }}">Create Faculty</a>@endif
                @if($canView('registrar.registrar-menu.faculty-mgmt.grading-sheet'))<a href="{{ route('registrar.registrar-menu.faculty-mgmt.grading-sheet') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.grading-sheet') ? 'active' : '' }}">Grading Sheets</a>@endif
                @if($canView('registrar.registrar-menu.faculty-mgmt.upload-grades'))<a href="{{ route('registrar.registrar-menu.faculty-mgmt.upload-grades') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.upload-grades') ? 'active' : '' }}">Upload Grades</a>@endif
                @if($canView('registrar.registrar-menu.faculty-mgmt.evaluation'))<a href="{{ route('registrar.registrar-menu.faculty-mgmt.evaluation') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.evaluation') ? 'active' : '' }}">Faculty Evaluation</a>@endif
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- DOCUMENTS & FORMS                      --}}
        {{-- ══════════════════════════════════════ --}}
        @php
            $certificateRoutes = ['registrar.registrar-menu.forms.certificates.certificate-gwa', 'registrar.registrar-menu.forms.certificates.deans-honors', 'registrar.registrar-menu.forms.certificates.presidents-honors', 'registrar.registrar-menu.forms.certificates.certificate-graduation-8c2', 'registrar.registrar-menu.forms.certificates.certificate-honor-8d2'];
            $documentsFormsRoutes = array_merge(['registrar.registrar-menu.forms.diploma', 'registrar.registrar-menu.forms.cog.copy-of-grades', 'registrar.registrar-menu.forms.cor.certificate-of-registration', 'registrar.registrar-menu.forms.official-grade-report', 'registrar.registrar-menu.forms.honorable-dismissal', 'registrar.registrar-menu.forms.application-leave-of-absence-enrolled', 'registrar.registrar-menu.forms.application-leave-of-absence-non-enrolled', 'registrar.registrar-menu.forms.permission-cross-enroll', 'registrar.registrar-menu.forms.request-form-f-137a', 'registrar.registrar-menu.forms.graduation-clearance', 'registrar.registrar-menu.forms.waiver-cancellation', 'registrar.registrar-menu.forms.citizens-charter'], $certificateRoutes);
        @endphp
        @if($canViewAny($documentsFormsRoutes))
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.registrar-menu.forms.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.registrar-menu.forms.*') ? 'active' : '' }}">
                {{-- document-text icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 12.2H15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 16.2H12.38" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 6H14C16 6 16 5 16 4C16 2 15 2 14 2H10C9 2 8 2 8 4C8 6 9 6 10 6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 4.02002C19.33 4.20002 21 5.43002 21 10V16C21 20 20 22 15 22H9C4 22 3 20 3 16V10C3 5.44002 4.67 4.20002 8 4.02002" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Documents &amp; Forms</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <div class="sidebar-section-label">Official Documents</div>
                @if($canView('registrar.registrar-menu.forms.diploma'))<a href="{{ route('registrar.registrar-menu.forms.diploma') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.diploma') ? 'active' : '' }}">Diploma</a>@endif
                @if($canView('registrar.registrar-menu.forms.cog.copy-of-grades'))<a href="{{ route('registrar.registrar-menu.forms.cog.copy-of-grades') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.cog.*') ? 'active' : '' }}">Copy of Grades (COG)</a>@endif
                @if($canView('registrar.registrar-menu.forms.cor.certificate-of-registration'))<a href="{{ route('registrar.registrar-menu.forms.cor.certificate-of-registration') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.cor.*') ? 'active' : '' }}">Certificate of Registration (COR)</a>@endif
                @if($canView('registrar.registrar-menu.forms.official-grade-report'))<a href="{{ route('registrar.registrar-menu.forms.official-grade-report') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.official-grade-report') ? 'active' : '' }}">Official Grade Report</a>@endif
                @if($canView('registrar.registrar-menu.forms.honorable-dismissal'))<a href="{{ route('registrar.registrar-menu.forms.honorable-dismissal') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.honorable-dismissal') ? 'active' : '' }}">Honorable Dismissal</a>@endif

                <div class="sidebar-section-divider"></div>
                @if($canViewAny($certificateRoutes))
                <div class="sidebar-section-label">Certificates</div>
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.forms.certificates.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.forms.certificates.*') ? 'active' : '' }}">
                        Certificates
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.registrar-menu.forms.certificates.deans-honors'))<a href="{{ route('registrar.registrar-menu.forms.certificates.deans-honors') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.certificates.deans-honors*') ? 'active' : '' }}">Dean's Honors</a>@endif
                        @if($canView('registrar.registrar-menu.forms.certificates.presidents-honors'))<a href="{{ route('registrar.registrar-menu.forms.certificates.presidents-honors') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.certificates.presidents-honors*') ? 'active' : '' }}">President's Honors</a>@endif
                        @if($canView('registrar.registrar-menu.forms.certificates.certificate-graduation-8c2'))<a href="{{ route('registrar.registrar-menu.forms.certificates.certificate-graduation-8c2') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.certificates.certificate-graduation-8c2') ? 'active' : '' }}">Form 8C-2 (Graduation)</a>@endif
                        @if($canView('registrar.registrar-menu.forms.certificates.certificate-honor-8d2'))<a href="{{ route('registrar.registrar-menu.forms.certificates.certificate-honor-8d2') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.certificates.certificate-honor-8d2') ? 'active' : '' }}">Form 8D-2 (Honor)</a>@endif
                    </div>
                </div>
                @endif

                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section-label">Student Requests</div>
                @if($canView('registrar.registrar-menu.forms.application-leave-of-absence-enrolled'))<a href="{{ route('registrar.registrar-menu.forms.application-leave-of-absence-enrolled') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.application-leave-of-absence-enrolled') ? 'active' : '' }}">Leave of Absence (Enrolled)</a>@endif
                @if($canView('registrar.registrar-menu.forms.application-leave-of-absence-non-enrolled'))<a href="{{ route('registrar.registrar-menu.forms.application-leave-of-absence-non-enrolled') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.application-leave-of-absence-non-enrolled') ? 'active' : '' }}">Leave of Absence (Non-Enrolled)</a>@endif
                @if($canView('registrar.registrar-menu.forms.late-application-leave-of-absence'))<a href="{{ route('registrar.registrar-menu.forms.late-application-leave-of-absence') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.late-application-leave-of-absence*') ? 'active' : '' }}">Late Application of Leave of Absence</a>@endif
                @if($canView('registrar.registrar-menu.forms.permission-cross-enroll'))<a href="{{ route('registrar.registrar-menu.forms.permission-cross-enroll') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.permission-cross-enroll') ? 'active' : '' }}">Permission to Cross-Enroll</a>@endif
                @if($canView('registrar.registrar-menu.forms.request-form-f-137a'))<a href="{{ route('registrar.registrar-menu.forms.request-form-f-137a') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.request-form-f-137a') ? 'active' : '' }}">Request Form F137A</a>@endif
                @if($canView('registrar.registrar-menu.forms.graduation-clearance'))<a href="{{ route('registrar.registrar-menu.forms.graduation-clearance') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.graduation-clearance') ? 'active' : '' }}">Graduation Clearance</a>@endif
                @if($canView('registrar.registrar-menu.forms.waiver-cancellation'))<a href="{{ route('registrar.registrar-menu.forms.waiver-cancellation') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.waiver-cancellation') ? 'active' : '' }}">Waiver Cancellation</a>@endif
                @if($canView('registrar.registrar-menu.forms.citizens-charter'))<a href="{{ route('registrar.registrar-menu.forms.citizens-charter') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.forms.citizens-charter') ? 'active' : '' }}">Citizen's Charter</a>@endif
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- REPORTS                                --}}
        {{-- ══════════════════════════════════════ --}}
        @php
            $reportsActive = request()->routeIs('registrar.services.reports-admin.*');
        @endphp
        @if($canViewAny(['registrar.services.reports-admin.academic-reports', 'registrar.services.reports-admin.gwa-report', 'registrar.services.reports-admin.cwa-report', 'registrar.services.reports-admin.certifications', 'registrar.services.reports-admin.tagging-of-graduates', 'registrar.services.reports-admin.guidance-reports', 'registrar.services.reports-admin.batch-print']))
        <div class="sidebar-dropdown {{ $reportsActive ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ $reportsActive ? 'active' : '' }}">
                {{-- chart / analytics icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M2 2V19C2 20.66 3.34 22 5 22H22" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 17L9.59 11.64C10.35 10.76 11.7 10.7 12.52 11.53L13.47 12.47C14.29 13.3 15.64 13.24 16.4 12.36L21 7" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Reports</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <div class="sidebar-section-label">Academic</div>
                @if($canView('registrar.services.reports-admin.academic-reports'))<a href="{{ route('registrar.services.reports-admin.academic-reports') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.academic-reports') ? 'active' : '' }}">Academic Reports</a>@endif
                @if($canView('registrar.services.reports-admin.gwa-report'))<a href="{{ route('registrar.services.reports-admin.gwa-report') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.gwa-report') ? 'active' : '' }}">GWA Report</a>@endif
                @if($canView('registrar.services.reports-admin.cwa-report'))<a href="{{ route('registrar.services.reports-admin.cwa-report') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.cwa-report') ? 'active' : '' }}">CWA Report</a>@endif
                @if($canView('registrar.services.reports-admin.certifications'))<a href="{{ route('registrar.services.reports-admin.certifications') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.certifications') ? 'active' : '' }}">Certifications</a>@endif
                @if($canView('registrar.services.reports-admin.tagging-of-graduates'))<a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.tagging-of-graduates') ? 'active' : '' }}">Graduation Tagging</a>@endif
                @if($canView('registrar.services.reports-admin.batch-print'))<a href="{{ route('registrar.services.reports-admin.batch-print') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.batch-print') ? 'active' : '' }}">Batch Print (COR/TOR)</a>@endif

                <div class="sidebar-section-divider"></div>
                <div class="sidebar-section-label">Student Affairs</div>
                @if($canView('registrar.services.reports-admin.guidance-reports'))<a href="{{ route('registrar.services.reports-admin.guidance-reports') }}" class="sidebar-sublink {{ request()->routeIs('registrar.services.reports-admin.guidance-reports') ? 'active' : '' }}">Guidance Reports</a>@endif
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- SYSTEM                                 --}}
        {{-- ══════════════════════════════════════ --}}
        @php
            $systemConfigRoutes = ['registrar.admin-tools.system-config.configuration', 'registrar.admin-tools.system-config.academic-calendar', 'registrar.admin-tools.system-config.announcement', 'registrar.admin-tools.system-config.document-templates'];
            $accessManagementRoutes = ['registrar.admin-tools.access-management.user-accounts', 'registrar.admin-tools.access-management.report-access'];
            $masterFilesRoutes = ['registrar.admin-tools.master-files.faculty-file', 'registrar.admin-tools.master-files.student-profile', 'registrar.admin-tools.master-files.student-grade-file', 'registrar.admin-tools.student-maintenance.student-update'];
            $systemRoutes = array_merge($systemConfigRoutes, $accessManagementRoutes, $masterFilesRoutes, ['registrar.admin-tools.audit-trail', 'registrar.admin-tools.data-imports.index']);
        @endphp
        @if($canViewAny($systemRoutes) || (auth()->check() && strtolower(trim((string)(auth()->user()->module ?? ''))) === 'admin'))
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.admin-tools.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.admin-tools.*') ? 'active' : '' }}">
                {{-- settings/gear icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12.88V11.12C2 10.08 2.85 9.22 3.9 9.22C5.71 9.22 6.45 7.94 5.54 6.37C5.02 5.47 5.33 4.3 6.24 3.78L7.97 2.79C8.76 2.32 9.78 2.6 10.25 3.39L10.36 3.58C11.26 5.15 12.74 5.15 13.65 3.58L13.76 3.39C14.23 2.6 15.25 2.32 16.04 2.79L17.77 3.78C18.68 4.3 18.99 5.47 18.47 6.37C17.56 7.94 18.3 9.22 20.11 9.22C21.15 9.22 22.01 10.07 22.01 11.12V12.88C22.01 13.92 21.16 14.78 20.11 14.78C18.3 14.78 17.56 16.06 18.47 17.63C18.99 18.54 18.68 19.7 17.77 20.22L16.04 21.21C15.25 21.68 14.23 21.4 13.76 20.61L13.65 20.42C12.75 18.85 11.27 18.85 10.36 20.42L10.25 20.61C9.78 21.4 8.76 21.68 7.97 21.21L6.24 20.22C5.33 19.7 5.02 18.53 5.54 17.63C6.45 16.06 5.71 14.78 3.9 14.78C2.85 14.78 2 13.92 2 12.88Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>System</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">

                {{-- System Config --}}
                @if($canViewAny($systemConfigRoutes))
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.admin-tools.system-config.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.admin-tools.system-config.*') ? 'active' : '' }}">
                        System Config
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.admin-tools.system-config.configuration'))<a href="{{ route('registrar.admin-tools.system-config.configuration') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.configuration') ? 'active' : '' }}">Configuration</a>@endif
                        @if($canView('registrar.admin-tools.system-config.academic-calendar'))<a href="{{ route('registrar.admin-tools.system-config.academic-calendar') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.academic-calendar') ? 'active' : '' }}">Academic Calendar</a>@endif
                        @if($canView('registrar.admin-tools.system-config.announcement'))<a href="{{ route('registrar.admin-tools.system-config.announcement') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.announcement') ? 'active' : '' }}">Announcements</a>@endif
                        @if($canView('registrar.admin-tools.system-config.document-templates'))<a href="{{ route('registrar.admin-tools.system-config.document-templates') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.document-templates') ? 'active' : '' }}">Document Templates</a>@endif
                    </div>
                </div>
                @endif

                {{-- Access Management --}}
                @if($canViewAny($accessManagementRoutes))
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.admin-tools.access-management.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.admin-tools.access-management.*') ? 'active' : '' }}">
                        Access Management
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.admin-tools.access-management.user-accounts'))<a href="{{ route('registrar.admin-tools.access-management.user-accounts') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.access-management.user-accounts') ? 'active' : '' }}">User Accounts</a>@endif
                        @if($canView('registrar.admin-tools.access-management.report-access'))<a href="{{ route('registrar.admin-tools.access-management.report-access') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.access-management.report-access') ? 'active' : '' }}">Report Access</a>@endif
                    </div>
                </div>
                @endif

                {{-- Master Files --}}
                @if($canViewAny($masterFilesRoutes))
                <div class="sidebar-nested-dropdown {{ (request()->routeIs('registrar.admin-tools.master-files.*') || request()->routeIs('registrar.admin-tools.student-maintenance.*')) ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ (request()->routeIs('registrar.admin-tools.master-files.*') || request()->routeIs('registrar.admin-tools.student-maintenance.*')) ? 'active' : '' }}">
                        Master Files
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        @if($canView('registrar.admin-tools.master-files.faculty-file'))<a href="{{ route('registrar.admin-tools.master-files.faculty-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.master-files.faculty-file') ? 'active' : '' }}">Faculty File</a>@endif
                        @if($canView('registrar.admin-tools.master-files.student-profile'))<a href="{{ route('registrar.admin-tools.master-files.student-profile') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.master-files.student-profile') ? 'active' : '' }}">Student Profile</a>@endif
                        @if($canView('registrar.admin-tools.master-files.student-grade-file'))<a href="{{ route('registrar.admin-tools.master-files.student-grade-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.master-files.student-grade-file') ? 'active' : '' }}">Student Grade File</a>@endif
                        @if($canView('registrar.admin-tools.student-maintenance.student-update'))<a href="{{ route('registrar.admin-tools.student-maintenance.student-update') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.student-maintenance.student-update') ? 'active' : '' }}">Student Update</a>@endif
                    </div>
                </div>
                @endif

                @if($canView('registrar.admin-tools.data-imports.index'))
                <a href="{{ route('registrar.admin-tools.data-imports.index') }}" class="sidebar-sublink {{ request()->routeIs('registrar.admin-tools.data-imports.*') ? 'active' : '' }}">
                    Data Imports
                </a>
                @endif

                @if(auth()->check() && strtolower(trim((string)(auth()->user()->module ?? ''))) === 'admin')
                <a href="{{ route('registrar.admin-tools.grade-override.index') }}" class="sidebar-sublink {{ request()->routeIs('registrar.admin-tools.grade-override.*') ? 'active' : '' }}">
                    Grade Override
                </a>
                @endif

                @if($canView('registrar.admin-tools.audit-trail'))
                <a href="{{ route('registrar.admin-tools.audit-trail') }}" class="sidebar-sublink {{ request()->routeIs('registrar.admin-tools.audit-trail') ? 'active' : '' }}">
                    Audit Trail
                </a>
                @endif

            </div>
        </div>
        @endif

    </nav>

    <style>
        .sidebar-section-label {
            padding: 3px 18px 1px;
            font-size: 9px;
            line-height: 1.2;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6b7280;
            pointer-events: none;
            user-select: none;
        }
        .sidebar-section-divider {
            margin: 3px 12px 1px;
            border-top: 1px solid #e5e7eb;
        }
        .sidebar-sublink-with-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .sidebar-alert-badge {
            min-width: 20px;
            height: 18px;
            padding: 0 6px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            line-height: 18px;
            text-align: center;
        }
    </style>

    {{-- Log Out --}}
    <div class="sidebar-logout">
        <a href="{{ route('logout') }}" class="sidebar-link logout-link js-registrar-logout">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="registrar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</aside>
