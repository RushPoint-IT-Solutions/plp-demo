<aside class="plp-sidebar">
    {{-- Logo + School Name --}}
    <div class="sidebar-brand">
        <img src="{{ asset('img/logobg.png') }}" alt="PLP Logo" class="sidebar-logo">
        <img src="{{ asset('img/plptextlogo.png') }}" alt="PLP Text" class="sidebar-text-logo">
    </div>

    {{-- Navigation Links --}}
    <nav class="sidebar-nav">

        {{-- Dashboard (same home icon as student) --}}
        <a href="{{ route('registrar.dashboard') }}" class="sidebar-link {{ request()->routeIs('registrar.dashboard') ? 'active' : '' }}">
            {{-- Vuesax linear/home-2 --}}
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M9.02 2.84004L3.63 7.04004C2.73 7.74004 2 9.23004 2 10.36V17.77C2 20.09 3.89 21.99 6.21 21.99H17.79C20.11 21.99 22 20.09 22 17.78V10.5C22 9.29004 21.19 7.74004 20.2 7.05004L14.02 2.72004C12.62 1.74004 10.37 1.79004 9.02 2.84004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 17.99V14.99" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- Process (Dropdown) - Vuesax linear/teacher icon --}}
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.process.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.process.*') ? 'active' : '' }}">
                {{-- Vuesax linear/teacher --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M10.05 2.53004L4.03 6.46004C2.1 7.48004 2.1 10.54 4.03 11.56L10.05 15.49C11.13 16.17 12.91 16.17 13.99 15.49L19.98 11.56C21.9 10.54 21.9 7.49004 19.98 6.47004L13.99 2.54004C12.91 1.86004 11.13 1.86004 10.05 2.53004Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.63 13.08L5.62 17.77C5.62 18.79 6.35 19.9 7.26 20.22L10.69 21.39C11.38 21.63 12.52 21.63 13.22 21.39L16.65 20.22C17.56 19.9 18.29 18.79 18.29 17.77V13.13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21.4 15V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Process</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <a href="{{ route('registrar.process.application') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.application') ? 'active' : '' }}">Application</a>
                <a href="{{ route('registrar.process.citizenship') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.citizenship') ? 'active' : '' }}">Citizenship</a>
                <a href="{{ route('registrar.process.religion') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.religion') ? 'active' : '' }}">Religion</a>
                <a href="{{ route('registrar.process.approval-status') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.approval-status') ? 'active' : '' }}">Approval Status</a>
                <a href="{{ route('registrar.process.batch-upload') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.batch-upload') ? 'active' : '' }}">Batch Upload Image</a>
                <a href="{{ route('registrar.process.document-list') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.document-list') ? 'active' : '' }}">Document List</a>
                <a href="{{ route('registrar.process.requirements') }}" class="sidebar-sublink {{ request()->routeIs('registrar.process.requirements') ? 'active' : '' }}">Requirements</a>
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.process.reports') || request()->routeIs('registrar.process.reports.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.process.reports') || request()->routeIs('registrar.process.reports.*') ? 'active' : '' }}">
                        Reports
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.process.reports') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.process.reports') ? 'active' : '' }}">Overview</a>
                        <a href="{{ route('registrar.process.reports.unifast') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.process.reports.unifast') ? 'active' : '' }}">UNIFAST</a>
                        <a href="{{ route('registrar.process.reports.oss-nstp-form') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.process.reports.oss-nstp-form') ? 'active' : '' }}">OSS - NSTP Form</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Registrar (Dropdown) - Vuesax linear/calendar --}}
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.registrar-menu.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.registrar-menu.*') ? 'active' : '' }}">
                {{-- Vuesax linear/calendar --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.5 9.09009H20.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9955 13.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.29431 13.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.29431 16.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Registrar</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                {{-- Academic Master (nested sub-dropdown) --}}
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.academic-master.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.academic-master.*') ? 'active' : '' }}">
                        Academic Master
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.registrar-menu.academic-master.program-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.program-file') ? 'active' : '' }}">Program File</a>
                        <a href="{{ route('registrar.registrar-menu.academic-master.subject-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.subject-file') ? 'active' : '' }}">Subject File</a>
                        <a href="{{ route('registrar.registrar-menu.academic-master.pre-requisites') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.pre-requisites') ? 'active' : '' }}">Pre-requisites</a>
                        <a href="{{ route('registrar.registrar-menu.academic-master.letter-grade') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.academic-master.letter-grade') ? 'active' : '' }}">Letter Grade Setup</a>
                    </div>
                </div>

                {{-- Scheduling --}}
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.scheduling.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.scheduling.*') ? 'active' : '' }}">
                        Scheduling
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.registrar-menu.scheduling.room-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.room-file') ? 'active' : '' }}">Room File</a>
                        <a href="{{ route('registrar.registrar-menu.scheduling.section-offering') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.section-offering') ? 'active' : '' }}">Section Offering</a>
                        <a href="{{ route('registrar.registrar-menu.scheduling.slot-monitoring') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.slot-monitoring') ? 'active' : '' }}">Slot Monitoring</a>
                        <a href="{{ route('registrar.registrar-menu.scheduling.section-merging') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.scheduling.section-merging') ? 'active' : '' }}">Section Merging</a>
                    </div>
                </div>

                {{-- Student Management --}}
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.student-mgmt.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.student-mgmt.*') ? 'active' : '' }}">
                        Student Management
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.registrar-menu.student-mgmt.student-enrollment') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.student-mgmt.student-enrollment') ? 'active' : '' }}">Student Enrollment</a>
                        <a href="{{ route('registrar.registrar-menu.student-mgmt.clinic-record') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.student-mgmt.clinic-record') ? 'active' : '' }}">Clinic Record</a>
                    </div>
                </div>

                {{-- Faculty Management --}}
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.*') ? 'active' : '' }}">
                        Faculty Management
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.registrar-menu.faculty-mgmt.faculty-create') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.faculty-create') ? 'active' : '' }}">Add New Faculty</a>
                        <a href="{{ route('registrar.registrar-menu.faculty-mgmt.grading-sheet') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.grading-sheet') ? 'active' : '' }}">Grading Sheet</a>
                        <a href="{{ route('registrar.registrar-menu.faculty-mgmt.evaluation') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.faculty-mgmt.evaluation') ? 'active' : '' }}">Evaluation</a>
                    </div>
                </div>

                    {{-- Forms removed from Registrar submenu (moved to top-level) --}}

                {{-- Alumni Tracker --}}
                <a href="{{ route('registrar.registrar-menu.alumni.tracker') }}" class="sidebar-sublink {{ request()->routeIs('registrar.registrar-menu.alumni.tracker') ? 'active' : '' }}">
                    Alumni Tracker
                </a>
            </div>
        </div>

        {{-- Services (Dropdown) - Vuesax linear/clipboard-text --}}
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.services.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.services.*') ? 'active' : '' }}">
                {{-- Vuesax linear/clipboard-text --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 12.2H15" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 16.2H12.38" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10 6H14C16 6 16 5 16 4C16 2 15 2 14 2H10C9 2 8 2 8 4C8 6 9 6 10 6Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 4.02002C19.33 4.20002 21 5.43002 21 10V16C21 20 20 22 15 22H9C4 22 3 20 3 16V10C3 5.44002 4.67 4.20002 8 4.02002" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Services</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.services.classroom-faculty.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.services.classroom-faculty.*') ? 'active' : '' }}">
                        Classroom &amp; Faculty
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.services.classroom-faculty.class-list') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.classroom-faculty.class-list') ? 'active' : '' }}">Class List</a>
                        <a href="{{ route('registrar.services.classroom-faculty.attendance') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.classroom-faculty.attendance') ? 'active' : '' }}">Attendance</a>
                        <a href="{{ route('registrar.services.classroom-faculty.faculty-loads.index') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.classroom-faculty.faculty-loads.*') ? 'active' : '' }}">Faculty Loads</a>
                    </div>
                </div>

                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.services.grading-academic.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.services.grading-academic.*') ? 'active' : '' }}">
                        Grading &amp; Academic
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.services.grading-academic.grading-system') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.grading-system') ? 'active' : '' }}">Grading System</a>
                        <a href="{{ route('registrar.services.grading-academic.grading-periods') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.grading-periods') ? 'active' : '' }}">Grading Periods</a>
                        <a href="{{ route('registrar.services.grading-academic.grading-components') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.grading-components') ? 'active' : '' }}">Grading Components</a>
                        <a href="{{ route('registrar.services.grading-academic.transmutation') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.transmutation') ? 'active' : '' }}">Transmutation</a>
                        <a href="{{ route('registrar.services.grading-academic.deficiency') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.grading-academic.deficiency') ? 'active' : '' }}">Deficiency</a>
                    </div>
                </div>

                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.services.reports-admin.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.services.reports-admin.*') ? 'active' : '' }}">
                        Reports &amp; Admin
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.services.reports-admin.academic-reports') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.reports-admin.academic-reports') ? 'active' : '' }}">Academic Reports</a>
                        <a href="{{ route('registrar.services.reports-admin.guidance-reports') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.reports-admin.guidance-reports') ? 'active' : '' }}">Guidance Reports</a>
                        <a href="{{ route('registrar.services.reports-admin.certifications') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.reports-admin.certifications') ? 'active' : '' }}">Certifications</a>
                        <a href="{{ route('registrar.services.reports-admin.tagging-of-graduates') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.reports-admin.tagging-of-graduates') ? 'active' : '' }}">Tagging of Graduates</a>
                    </div>
                </div>

                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.services.student-account.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.services.student-account.*') ? 'active' : '' }}">
                        Student &amp; Account
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.services.student-account.student-discipline') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.student-account.student-discipline') ? 'active' : '' }}">Student Discipline</a>
                        <a href="{{ route('registrar.services.student-account.family') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.student-account.family') ? 'active' : '' }}">Student Family</a>
                        <a href="{{ route('registrar.services.student-account.change-password') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.services.student-account.change-password') ? 'active' : '' }}">Change Password</a>
                    </div>
                </div>
            </div>
            </div>

            {{-- Forms (Top-level) --}}
            <div class="sidebar-dropdown {{ request()->routeIs('registrar.registrar-menu.forms.*') ? 'open' : '' }}">
                <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.registrar-menu.forms.*') ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.5 9.09009H20.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Forms</span>
                    <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </a>
                <div class="sidebar-dropdown-menu">
                    <a href="{{ route('registrar.registrar-menu.forms.tor') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.tor') ? 'active' : '' }}">TOR</a>
                    <a href="{{ route('registrar.registrar-menu.forms.diploma') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.diploma') ? 'active' : '' }}">Diploma</a>
                    <a href="{{ route('registrar.registrar-menu.forms.graduation-clearance') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.graduation-clearance') ? 'active' : '' }}">Graduation Clearance</a>
                    <a href="{{ route('registrar.registrar-menu.forms.honorable-dismissal') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.honorable-dismissal') ? 'active' : '' }}">Honorable Dismissal</a>
                    <a href="{{ route('registrar.registrar-menu.forms.official-grade-report') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.official-grade-report') ? 'active' : '' }}">Official Grade Report</a>
                    <a href="{{ route('registrar.registrar-menu.forms.permission-cross-enroll') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.permission-cross-enroll') ? 'active' : '' }}">Permission to Cross-Enroll</a>
                    <a href="{{ route('registrar.registrar-menu.forms.waiver-cancellation') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.registrar-menu.forms.waiver-cancellation') ? 'active' : '' }}">Waiver Cancellation</a>
                </div>
            </div>

            {{-- Admin Tools (Dropdown) --}}
        <div class="sidebar-dropdown {{ request()->routeIs('registrar.admin-tools.*') ? 'open' : '' }}">
            <a href="#" class="sidebar-link sidebar-dropdown-toggle {{ request()->routeIs('registrar.admin-tools.*') ? 'active' : '' }}">
                {{-- Vuesax calendar with number 8 --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.5 9.09009H20.5" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="12" y="18.5" text-anchor="middle" font-size="9" font-weight="700" fill="currentColor" stroke="none" font-family="Poppins, sans-serif">8</text>
                </svg>
                <span>Admin Tools</span>
                <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </a>
            <div class="sidebar-dropdown-menu">
                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.admin-tools.system-config.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.admin-tools.system-config.*') ? 'active' : '' }}">
                        System Config
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.admin-tools.system-config.configuration') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.configuration') ? 'active' : '' }}">Configuration</a>
                        {{-- <a href="{{ route('registrar.admin-tools.system-config.admission-config') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.admission-config') ? 'active' : '' }}">Admission Config</a> --}}
                        <a href="{{ route('registrar.admin-tools.system-config.academic-calendar') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.academic-calendar') ? 'active' : '' }}">Academic Calendar</a>
                        <a href="{{ route('registrar.admin-tools.system-config.announcement') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.system-config.announcement') ? 'active' : '' }}">Announcement</a>
                    </div>
                </div>

                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.admin-tools.access-management.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.admin-tools.access-management.*') ? 'active' : '' }}">
                        Access Management
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.admin-tools.access-management.user-accounts') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.access-management.user-accounts') ? 'active' : '' }}">User Accounts</a>
                        <a href="{{ route('registrar.admin-tools.access-management.report-access') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.access-management.report-access') ? 'active' : '' }}">Report Access</a>
                    </div>
                </div>

                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.admin-tools.master-files.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.admin-tools.master-files.*') ? 'active' : '' }}">
                        Master Files
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.admin-tools.master-files.faculty-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.master-files.faculty-file') ? 'active' : '' }}">Faculty File</a>
                        <a href="{{ route('registrar.admin-tools.master-files.student-profile') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.master-files.student-profile') ? 'active' : '' }}">Student Profile</a>
                        <a href="{{ route('registrar.admin-tools.master-files.student-grade-file') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.master-files.student-grade-file') ? 'active' : '' }}">Student Grade File</a>
                    </div>
                </div>

                <div class="sidebar-nested-dropdown {{ request()->routeIs('registrar.admin-tools.student-maintenance.*') ? 'open' : '' }}">
                    <a href="#" class="sidebar-sublink sidebar-nested-toggle {{ request()->routeIs('registrar.admin-tools.student-maintenance.*') ? 'active' : '' }}">
                        Student Maintenance
                        <svg class="sidebar-chevron" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </a>
                    <div class="sidebar-nested-menu">
                        <a href="{{ route('registrar.admin-tools.student-maintenance.bed-student-status') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.student-maintenance.bed-student-status') ? 'active' : '' }}">BED Student Status</a>
                        <a href="{{ route('registrar.admin-tools.student-maintenance.bed-days') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.student-maintenance.bed-days') ? 'active' : '' }}">BED Days</a>
                        <a href="{{ route('registrar.admin-tools.student-maintenance.student-update') }}" class="sidebar-sublink sidebar-nested-sublink {{ request()->routeIs('registrar.admin-tools.student-maintenance.student-update') ? 'active' : '' }}">Student Update</a>
                    </div>
                </div>
            </div>
        </div>

    </nav>

    {{-- Log Out --}}
    <div class="sidebar-logout">
        <a href="{{ route('logout') }}" class="sidebar-link logout-link"
           onclick="event.preventDefault(); document.getElementById('registrar-logout-form').submit();">
            <span>Log Out</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/>
            </svg>
        </a>
        <form id="registrar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</aside>
