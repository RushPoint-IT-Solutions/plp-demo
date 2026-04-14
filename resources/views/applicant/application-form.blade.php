@extends(!empty($applicationFormEmbedded) ? 'layouts.applicant-embedded' : 'layouts.applicant')

@section('title', 'PLP - Application Form')
@section('page-title', 'APPLICATION FORM')

@section('content')
<div class="profile-page application-form-page">
    @php($app = $applicant ?? null)
    @php($edu = optional($app)->educationalBackground)
    @php($family = optional($app)->familyBackground)
    @php($pref = optional($app)->applicationPreference)
    @php($defaultDraftStep = max(1, min(4, (int) optional($app)->application_draft_step)))
    @php($defaultActiveStep = !empty($applicationFormEmbedded) ? 1 : $defaultDraftStep)
    @php($activeStep = (int) old('active_step', $defaultActiveStep))
    @php($portalStage = (int) old('portal_stage', optional($app)->application_portal_stage))
    @php($fallbackScheduleDate = now()->copy()->addWeek()->setTime(9, 0))
    @php($calendarScheduleDate = optional($app)->exam_date ?: $fallbackScheduleDate)
    @php($selectedCalendarDate = $calendarScheduleDate->format('Y-m-d'))
    @php($startYear = now()->year)
    @php($defaultSchoolYear = $startYear . '-' . ($startYear + 1))
    @php($routeParams = isset($formRouteParams) && is_array($formRouteParams) ? $formRouteParams : [])
    @php($saveRouteName = isset($formRouteNames['save']) ? $formRouteNames['save'] : 'applicant.application-form.save')
    @php($step1RouteName = isset($formRouteNames['step1']) ? $formRouteNames['step1'] : 'applicant.application-form.step-1.save')
    @php($step2RouteName = isset($formRouteNames['step2']) ? $formRouteNames['step2'] : 'applicant.application-form.step-2.save')
    @php($step3RouteName = isset($formRouteNames['step3']) ? $formRouteNames['step3'] : 'applicant.application-form.step-3.save')
    @php($step4RouteName = isset($formRouteNames['step4']) ? $formRouteNames['step4'] : 'applicant.application-form.step-4.save')
    @php($continueRouteName = isset($formRouteNames['continue']) ? $formRouteNames['continue'] : 'applicant.application-form.continue')
    @php($resetRouteName = isset($formRouteNames['reset']) ? $formRouteNames['reset'] : 'applicant.application-form.reset-progress')
    @php($showResetButton = isset($showResetButton) ? (bool) $showResetButton : true)
    @php($forceEditable = isset($forceEditable) ? (bool) $forceEditable : false)
    @php($skipPreviewStepValidation = !empty($previewPortalMode))
    @php($hideRequiredIndicators = !empty($applicationFormEmbedded))

    @if(session('success'))
    <div class="applicant-alert applicant-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="applicant-alert applicant-alert-error">
        <ul class="applicant-error-list">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($showResetButton && (app()->environment('local') || config('app.debug')) && !empty($resetRouteName))
    <div class="d-flex justify-content-end mb-2">
        <form action="{{ route($resetRouteName, $routeParams) }}" method="POST" id="resetApplicationProgressForm" data-confirm-message="Reset application progress and step data for testing?">
            @csrf
            <button type="submit" class="btn btn-warning btn-sm">Temporary Reset Test Data</button>
        </form>
    </div>
    @endif

    @if(optional($app)->application_status === 'submitted' && !$forceEditable)
        @if($portalStage < 1)
        <div class="setup-form-container submitted-intro-card">
            <div class="setup-section-header submitted-intro-title-row">
                <h3 class="setup-section-title submitted-intro-title">Welcome, Applicant!</h3>
            </div>

            <p class="submitted-intro-text">
                Your application will be evaluated. Please proceed to the Office of Admission for validation of your application.
            </p>

            <ul class="submitted-intro-list">
                <li>Save your login credentials in a secure place.</li>
                <li>Use these credentials to view your applicant portal updates.</li>
                <li>Change your password after your first successful sign-in.</li>
            </ul>

            <div class="submitted-credentials-box">
                @php($isPreviewPortalMode = !empty($previewPortalMode))
                @php($lastNameToken = strtolower((string) optional($app)->last_name))
                @php($previewPasswordHint = 'plp123' . $lastNameToken)
                <h4 class="submitted-credentials-title">Your Account Credentials</h4>
                <ul class="submitted-credentials-list">
                    <li>
                        <span class="submitted-credentials-label">Username</span>
                        <strong class="submitted-credentials-value">{{ optional($app)->applicant_id }}</strong>
                        <small class="submitted-credentials-note">Use your Applicant ID when logging in.</small>
                    </li>
                    <li>
                        <span class="submitted-credentials-label">Default Password</span>
                        <strong class="submitted-credentials-value">{{ $isPreviewPortalMode ? $previewPasswordHint : strtoupper(optional($app)->last_name) }}</strong>
                        <small class="submitted-credentials-note">{{ $isPreviewPortalMode ? 'Format: plp123 + your last name.' : 'Format: your last name.' }}</small>
                    </li>
                </ul>
            </div>

            <p class="submitted-intro-note submitted-intro-note--bottom">
                You can view the status of your application at any time by logging in using your username and password.
            </p>

            <form action="{{ route($continueRouteName, $routeParams) }}" method="POST" class="submitted-continue-form">
                @csrf
                <button type="submit" class="btn-setup-next submitted-continue-btn">Proceed To Applicant Portal</button>
            </form>
        </div>
        @else
        <div class="submitted-status-shell" id="applicationStatusCalendar" data-selected-date="{{ $selectedCalendarDate }}">
            <div class="submitted-header-row">
                <div class="submitted-header-field">
                    <label class="setup-label">Applicant ID</label>
                    <input type="text" class="setup-input" readonly value="{{ optional($app)->applicant_id }}">
                </div>
                <div class="submitted-header-field submitted-header-field--name">
                    <label class="setup-label">Applicant Name</label>
                    <input type="text" class="setup-input" readonly value="{{ trim(optional($app)->first_name . ' ' . optional($app)->last_name) }}">
                </div>
            </div>

            <div class="setup-form-container submitted-status-card">
                <div class="submitted-status-grid">
                    <div class="submitted-calendar-panel">
                        <h3 class="submitted-panel-title">APPLICATION STATUS</h3>

                        <div class="submitted-calendar-toolbar">
                            <button type="button" class="submitted-calendar-nav" data-calendar-nav="-1">&#8249;</button>
                            <select id="statusCalendarMonth" class="submitted-calendar-select"></select>
                            <select id="statusCalendarYear" class="submitted-calendar-select"></select>
                            <button type="button" class="submitted-calendar-nav" data-calendar-nav="1">&#8250;</button>
                        </div>

                        <div class="submitted-calendar-weekdays">
                            <span>Su</span>
                            <span>Mo</span>
                            <span>Tu</span>
                            <span>We</span>
                            <span>Th</span>
                            <span>Fr</span>
                            <span>Sa</span>
                        </div>

                        <div id="statusCalendarDays" class="submitted-calendar-days"></div>

                        <p class="submitted-calendar-current">Highlighted Schedule: {{ $calendarScheduleDate->format('F j, Y') }}</p>
                    </div>

                    <div class="submitted-assessment-panel">
                        <h3 class="submitted-panel-title">ASSESSMENT SCHEDULE</h3>
                        <div class="submitted-assessment-box">
                            <div class="submitted-assessment-item">
                                <span>Date:</span>
                                <strong>{{ $calendarScheduleDate->format('F d, Y') }}</strong>
                            </div>
                            <div class="submitted-assessment-item">
                                <span>Time:</span>
                                <strong>{{ $calendarScheduleDate->format('h:i A') }}</strong>
                            </div>
                            <div class="submitted-assessment-item">
                                <span>Venue:</span>
                                <strong>{{ optional($app)->exam_room ?: 'To be announced' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else

    <div id="stepSaveFeedback" class="applicant-alert applicant-alert-success step-save-feedback step-hidden"></div>

    <form
        action="{{ route($saveRouteName, $routeParams) }}"
        method="POST"
        enctype="multipart/form-data"
        id="applicationForm"
        data-active-step="{{ $activeStep }}"
        data-preview-skip-validation="{{ $skipPreviewStepValidation ? '1' : '0' }}"
        data-hide-required-indicators="{{ $hideRequiredIndicators ? '1' : '0' }}"
        data-step1-url="{{ route($step1RouteName, $routeParams) }}"
        data-step2-url="{{ route($step2RouteName, $routeParams) }}"
        data-step3-url="{{ route($step3RouteName, $routeParams) }}"
        data-step4-url="{{ route($step4RouteName, $routeParams) }}"
        @if($skipPreviewStepValidation) novalidate @endif
    >
        @csrf

        <input type="hidden" name="active_step" id="activeStepInput" value="{{ $activeStep }}">
        <input type="hidden" id="applicantAddressDraft" value="{{ e(json_encode([
            'present_region' => old('present_region', optional($app)->present_region),
            'present_province' => old('present_province', optional($app)->present_province),
            'present_municipality' => old('present_municipality', optional($app)->present_municipality),
            'permanent_region' => old('permanent_region', optional($app)->permanent_region),
            'permanent_province' => old('permanent_province', optional($app)->permanent_province),
            'permanent_municipality' => old('permanent_municipality', optional($app)->permanent_municipality),
        ])) }}">

        <div class="setup-steps">
            <div class="step-item {{ $activeStep === 1 ? 'active' : ($activeStep > 1 ? 'completed' : '') }}" data-step="1">
                <div class="step-pill">Step 1</div>
            </div>
            <div class="step-line {{ $activeStep > 1 ? 'active' : '' }}"></div>
            <div class="step-item {{ $activeStep === 2 ? 'active' : ($activeStep > 2 ? 'completed' : '') }}" data-step="2">
                <div class="step-pill">Step 2</div>
            </div>
            <div class="step-line {{ $activeStep > 2 ? 'active' : '' }}"></div>
            <div class="step-item {{ $activeStep === 3 ? 'active' : ($activeStep > 3 ? 'completed' : '') }}" data-step="3">
                <div class="step-pill">Step 3</div>
            </div>
            <div class="step-line {{ $activeStep > 3 ? 'active' : '' }}"></div>
            <div class="step-item {{ $activeStep === 4 ? 'active' : '' }}" data-step="4">
                <div class="step-pill">Step 4</div>
            </div>
        </div>

        @if(!$hideRequiredIndicators)
        <div class="setup-required-legend">
            <span class="setup-required">*</span> Required fields
        </div>
        @endif

        <div class="setup-form-container step-panel{{ $activeStep !== 1 ? ' step-hidden' : '' }}" id="step-1">
            <div class="setup-section">
                <div class="setup-personal-top">
                    <div class="setup-personal-left">
                        <div class="setup-section-header">
                            <h3 class="setup-section-title">Personal Information</h3>
                        </div>
                    </div>

                    <div class="setup-profile-photo">
                        <div class="profile-photo-square" id="profilePhotoPreview">
                            @if(!empty(optional($app)->photo))
                            <img id="photoImg" src="{{ asset('storage/' . optional($app)->photo) }}" alt="Photo" class="setup-photo-img">
                            @else
                            <svg class="profile-photo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="12" cy="10" r="3"/>
                                <path d="M6 21v-1a6 6 0 0 1 12 0v1"/>
                            </svg>
                            <img id="photoImg" src="" alt="" class="setup-photo-img setup-photo-img--hidden">
                            @endif
                        </div>
                        <label class="profile-photo-btn" for="photoInput">Upload Photo</label>
                        <input type="file" id="photoInput" name="photo" accept="image/*" class="d-none">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Lastname</label>
                        <input type="text" class="setup-input" placeholder="Last Name" name="last_name" value="{{ old('last_name', optional($app)->last_name) }}" required>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">First Name</label>
                        <input type="text" class="setup-input" placeholder="First Name" name="first_name" value="{{ old('first_name', optional($app)->first_name) }}" required>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Middle Name</label>
                        <input type="text" class="setup-input" placeholder="Middle Name" name="middle_name" value="{{ old('middle_name', optional($app)->middle_name) }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Suffix</label>
                        <input type="text" class="setup-input" placeholder="Suffix" name="suffix" value="{{ old('suffix', optional($app)->suffix) }}">
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Nickname</label>
                        <input type="text" class="setup-input" placeholder="Nickname" name="nickname" value="{{ old('nickname', optional($app)->nickname) }}">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Gender</label>
                        <div class="setup-radio-group">
                            <label class="setup-radio"><input type="radio" name="gender" value="Male" {{ old('gender', optional($app)->gender) === 'Male' ? 'checked' : '' }} required> Male</label>
                            <label class="setup-radio"><input type="radio" name="gender" value="Female" {{ old('gender', optional($app)->gender) === 'Female' ? 'checked' : '' }} required> Female</label>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Nationality</label>
                        @include('components.applicant-select', [
                            'id' => 'nationalitySelect',
                            'name' => 'nationality',
                            'options' => ['Filipino','American','Japanese','Korean','Chinese','Other'],
                            'selected' => old('nationality', optional($app)->nationality),
                            'placeholder' => 'Select Nationality',
                        ])
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Religion</label>
                        @include('components.applicant-select', [
                            'id' => 'religionSelect',
                            'name' => 'religion',
                            'options' => ['Roman Catholic','Born Again Christian','Islam','Iglesia ni Cristo','Baptist','Seventh Day Adventist','Other'],
                            'selected' => old('religion', optional($app)->religion),
                            'placeholder' => 'Select Religion',
                        ])
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="setup-input" id="dobField" value="{{ old('date_of_birth', optional(optional($app)->date_of_birth)->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" class="setup-input" placeholder="Place of Birth" value="{{ old('place_of_birth', optional($app)->place_of_birth) }}">
                    </div>
                    <div class="setup-col setup-col-sm">
                        <label class="setup-label">Age</label>
                        <input type="number" name="age" class="setup-input" id="ageField" placeholder="Age" value="{{ old('age', optional($app)->age) }}" readonly tabindex="-1">
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Civil Status</label>
                        @include('components.applicant-select', [
                            'id' => 'civilStatusSelect',
                            'name' => 'civil_status',
                            'options' => ['Single','Married','Widowed'],
                            'selected' => old('civil_status', optional($app)->civil_status),
                            'placeholder' => 'Select Status',
                        ])
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Mobile Number</label>
                        <input type="tel" name="mobile_number" class="setup-input" placeholder="Mobile Number" value="{{ old('mobile_number', optional($app)->mobile_number) }}" maxlength="11" inputmode="numeric" pattern="\d{11}" title="Must be exactly 11 digits" required>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Email Address</label>
                        <input type="email" name="email_address" class="setup-input" placeholder="Email Address" value="{{ old('email_address', optional($app)->email_address) }}" required>
                    </div>
                </div>
            </div>

            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Residence Information</h3>
                </div>

                <h4 class="setup-subsection-title">Present Address</h4>

                <div class="setup-row">
                    <div class="setup-col setup-col--flex-3">
                        <label class="setup-label">Street</label>
                        <input type="text" name="present_street" id="present_street" class="setup-input" placeholder="Street" value="{{ old('present_street', optional($app)->present_street) }}" required>
                    </div>
                    <div class="setup-col setup-col--flex-2">
                        <label class="setup-label">Barangay</label>
                        <input type="text" name="present_barangay" id="present_barangay" class="setup-input" placeholder="Barangay" value="{{ old('present_barangay', optional($app)->present_barangay) }}" required>
                    </div>
                    <div class="setup-col setup-col--flex-1">
                        <label class="setup-label">Zipcode</label>
                        <input type="text" name="present_zipcode" id="present_zipcode" class="setup-input" placeholder="Zipcode" value="{{ old('present_zipcode', optional($app)->present_zipcode) }}" required>
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Region</label>
                        <div class="applicant-select-wrap" data-applicant-select>
                            <select name="present_region" id="present_region" class="applicant-select-native" required>
                                <option value="" disabled selected>Choose Region</option>
                            </select>
                            <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span class="applicant-select-trigger-text" data-select-current>Choose Region</span>
                                <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
                            </button>
                            <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Province</label>
                        <div class="applicant-select-wrap" data-applicant-select>
                            <select name="present_province" id="present_province" class="applicant-select-native" required>
                                <option value="" disabled selected>Choose Province</option>
                            </select>
                            <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span class="applicant-select-trigger-text" data-select-current>Choose Province</span>
                                <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
                            </button>
                            <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Municipality/City</label>
                        <div class="applicant-select-wrap" data-applicant-select>
                            <select name="present_municipality" id="present_municipality" class="applicant-select-native" required>
                                <option value="" disabled selected>Choose City/Municipality</option>
                            </select>
                            <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span class="applicant-select-trigger-text" data-select-current>Choose City/Municipality</span>
                                <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
                            </button>
                            <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                        </div>
                    </div>
                </div>

                <div class="setup-same-address">
                    <label class="setup-checkbox-label" for="sameAsPresent">
                        <input type="checkbox" id="sameAsPresent" name="same_as_present" value="1" {{ old('same_as_present', optional($app)->same_as_present) ? 'checked' : '' }}>
                        Same as Present Address
                    </label>
                </div>

                <h4 class="setup-subsection-title">Permanent Address</h4>

                <div class="setup-row" id="permanentAddressFields">
                    <div class="setup-col setup-col--flex-3">
                        <label class="setup-label">Street</label>
                        <input type="text" name="permanent_street" id="permanent_street" class="setup-input" placeholder="Street" value="{{ old('permanent_street', optional($app)->permanent_street) }}">
                    </div>
                    <div class="setup-col setup-col--flex-2">
                        <label class="setup-label">Barangay</label>
                        <input type="text" name="permanent_barangay" id="permanent_barangay" class="setup-input" placeholder="Barangay" value="{{ old('permanent_barangay', optional($app)->permanent_barangay) }}">
                    </div>
                    <div class="setup-col setup-col--flex-1">
                        <label class="setup-label">Zipcode</label>
                        <input type="text" name="permanent_zipcode" id="permanent_zipcode" class="setup-input" placeholder="Zipcode" value="{{ old('permanent_zipcode', optional($app)->permanent_zipcode) }}">
                    </div>
                </div>

                <div class="setup-row" id="permanentSelectFields">
                    <div class="setup-col">
                        <label class="setup-label">Region</label>
                        <div class="applicant-select-wrap" data-applicant-select>
                            <select name="permanent_region" id="permanent_region" class="applicant-select-native">
                                <option value="" disabled selected>Choose Region</option>
                            </select>
                            <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span class="applicant-select-trigger-text" data-select-current>Choose Region</span>
                                <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
                            </button>
                            <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Province</label>
                        <div class="applicant-select-wrap" data-applicant-select>
                            <select name="permanent_province" id="permanent_province" class="applicant-select-native">
                                <option value="" disabled selected>Choose Province</option>
                            </select>
                            <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span class="applicant-select-trigger-text" data-select-current>Choose Province</span>
                                <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
                            </button>
                            <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                        </div>
                    </div>
                    <div class="setup-col">
                        <label class="setup-label">Municipality/City</label>
                        <div class="applicant-select-wrap" data-applicant-select>
                            <select name="permanent_municipality" id="permanent_municipality" class="applicant-select-native">
                                <option value="" disabled selected>Choose City/Municipality</option>
                            </select>
                            <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span class="applicant-select-trigger-text" data-select-current>Choose City/Municipality</span>
                                <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
                            </button>
                            <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <div></div>
                <button type="button" class="btn-setup-next" data-save-step="1" data-go-step="2">Next</button>
            </div>
        </div>

        <div class="setup-form-container step-panel{{ $activeStep !== 2 ? ' step-hidden' : '' }}" id="step-2">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Educational Information</h3>
                </div>

                <div class="setup-row setup-row--stacked">
                    <div class="setup-col setup-col--full">
                        <label class="setup-label">Junior School</label>
                        <input type="text" name="junior_school" class="setup-input" placeholder="Junior High" value="{{ old('junior_school', optional($edu)->junior_school) }}" required>
                    </div>
                </div>

                <div class="setup-row setup-row--stacked">
                    <div class="setup-col setup-col--full">
                        <label class="setup-label">Senior School</label>
                        <input type="text" name="senior_school" id="seniorSchoolField" class="setup-input" placeholder="Senior High" value="{{ old('senior_school', optional($edu)->senior_school) }}" required>
                        <p class="setup-helper setup-helper--tight">If not applicable, use the same information as Junior High School</p>
                    </div>
                </div>

                <div class="setup-row setup-row--stacked">
                    <div class="setup-col setup-col--full">
                        <label class="setup-label">SHS Track Strand</label>
                        <input type="text" name="shs_track_strand" class="setup-input" placeholder="SHS Strand" value="{{ old('shs_track_strand', optional($edu)->shs_track_strand) }}" required>
                    </div>
                </div>

                <div class="setup-row setup-row--stacked">
                    <div class="setup-col setup-col--full">
                        <label class="setup-checkbox-label setup-checkbox-label--inline-note" for="noK12Toggle">
                            <input type="checkbox" id="noK12Toggle" name="no_k12" value="1" {{ old('no_k12', optional($edu)->no_k12) ? 'checked' : '' }}>
                            <span>Select this toggle if the student did not go through the K-12 Basic Education Curriculum implemented starting 2012 (e.g. old curriculum graduates, foreign students, ALS completers without LRN) or studied before implementation.</span>
                        </label>
                    </div>
                </div>

                <div class="setup-row setup-row--stacked">
                    <div class="setup-col setup-col--full">
                        <label class="setup-label">Learner's Reference Number (LRN)</label>
                        <input type="text" name="learner_reference_number" class="setup-input" placeholder="Must be exactly 12 digits (e.g. 123456789012)" value="{{ old('learner_reference_number', optional($edu)->learner_reference_number ?? optional($app)->lrn) }}" maxlength="12" inputmode="numeric" pattern="\d{12}" title="Must be exactly 12 digits" required>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-go-step="1">Previous</button>
                <button type="button" class="btn-setup-next" data-save-step="2" data-go-step="3">Next</button>
            </div>
        </div>

        <div class="setup-form-container step-panel{{ $activeStep !== 3 ? ' step-hidden' : '' }}" id="step-3">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Family Background</h3>
                </div>

                <h4 class="setup-subsection-title">Mother/Guardian</h4>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Lastname</label><input type="text" class="setup-input" name="mother_last_name" placeholder="Last Name" value="{{ old('mother_last_name', optional($family)->mother_last_name) }}" required></div>
                    <div class="setup-col"><label class="setup-label">First Name</label><input type="text" class="setup-input" name="mother_first_name" placeholder="First Name" value="{{ old('mother_first_name', optional($family)->mother_first_name) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Middle Name</label><input type="text" class="setup-input" name="mother_middle_name" placeholder="Middle Name" value="{{ old('mother_middle_name', optional($family)->mother_middle_name) }}" required></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Nationality</label>@include('components.applicant-select', ['id' => 'motherNationalitySelect', 'name' => 'mother_nationality', 'options' => ['Filipino','American','Japanese','Korean','Chinese','Other'], 'selected' => old('mother_nationality', optional($family)->mother_nationality), 'placeholder' => 'Select Nationality', 'required' => true])</div>
                    <div class="setup-col"><label class="setup-label">Religion</label>@include('components.applicant-select', ['id' => 'motherReligionSelect', 'name' => 'mother_religion', 'options' => ['Roman Catholic','Born Again Christian','Islam','Iglesia ni Cristo','Baptist','Seventh Day Adventist','Other'], 'selected' => old('mother_religion', optional($family)->mother_religion), 'placeholder' => 'Select Religion', 'required' => true])</div>
                    <div class="setup-col"><label class="setup-label">Date of Birth</label><input type="date" class="setup-input" name="mother_date_of_birth" value="{{ old('mother_date_of_birth', optional(optional($family)->mother_date_of_birth)->format('Y-m-d')) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Mobile Number</label><input type="tel" class="setup-input" name="mother_mobile_number" placeholder="Mobile Number" value="{{ old('mother_mobile_number', optional($family)->mother_mobile_number) }}" maxlength="11" inputmode="numeric" pattern="\d{11}" title="Must be exactly 11 digits" required></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Occupation</label><input type="text" class="setup-input" name="mother_occupation" placeholder="Occupation" value="{{ old('mother_occupation', optional($family)->mother_occupation) }}" required></div>
                    <div class="setup-col setup-col--flex-14"><label class="setup-label">Company Address</label><input type="text" class="setup-input" name="mother_company_address" placeholder="Company Address" value="{{ old('mother_company_address', optional($family)->mother_company_address) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Estimated Monthly Income</label>@include('components.applicant-select', ['id' => 'motherIncomeSelect', 'name' => 'mother_estimated_monthly_income', 'options' => ['Below 10,000','10,000 - 19,999','20,000 - 29,999','30,000 - 49,999','50,000 - 99,999','100,000 and above','N/A'], 'selected' => old('mother_estimated_monthly_income', optional($family)->mother_estimated_monthly_income), 'placeholder' => 'Select Income Range', 'required' => true])</div>
                </div>
                <div class="setup-row">
                    <div class="setup-col setup-col--flex-16"><label class="setup-label">Residence Address</label><input type="text" class="setup-input" name="mother_residence_address" placeholder="Residence Address" value="{{ old('mother_residence_address', optional($family)->mother_residence_address) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Email Address</label><input type="email" class="setup-input" name="mother_email_address" placeholder="Email Address" value="{{ old('mother_email_address', optional($family)->mother_email_address) }}"></div>
                </div>

                <h4 class="setup-subsection-title setup-subsection-title--mt18">Father/Guardian</h4>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Lastname</label><input type="text" class="setup-input" name="father_last_name" placeholder="Last Name" value="{{ old('father_last_name', optional($family)->father_last_name) }}" required></div>
                    <div class="setup-col"><label class="setup-label">First Name</label><input type="text" class="setup-input" name="father_first_name" placeholder="First Name" value="{{ old('father_first_name', optional($family)->father_first_name) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Middle Name</label><input type="text" class="setup-input" name="father_middle_name" placeholder="Middle Name" value="{{ old('father_middle_name', optional($family)->father_middle_name) }}" required></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Nationality</label>@include('components.applicant-select', ['id' => 'fatherNationalitySelect', 'name' => 'father_nationality', 'options' => ['Filipino','American','Japanese','Korean','Chinese','Other'], 'selected' => old('father_nationality', optional($family)->father_nationality), 'placeholder' => 'Select Nationality', 'required' => true])</div>
                    <div class="setup-col"><label class="setup-label">Religion</label>@include('components.applicant-select', ['id' => 'fatherReligionSelect', 'name' => 'father_religion', 'options' => ['Roman Catholic','Born Again Christian','Islam','Iglesia ni Cristo','Baptist','Seventh Day Adventist','Other'], 'selected' => old('father_religion', optional($family)->father_religion), 'placeholder' => 'Select Religion', 'required' => true])</div>
                    <div class="setup-col"><label class="setup-label">Date of Birth</label><input type="date" class="setup-input" name="father_date_of_birth" value="{{ old('father_date_of_birth', optional(optional($family)->father_date_of_birth)->format('Y-m-d')) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Mobile Number</label><input type="tel" class="setup-input" name="father_mobile_number" placeholder="Mobile Number" value="{{ old('father_mobile_number', optional($family)->father_mobile_number) }}" maxlength="11" inputmode="numeric" pattern="\d{11}" title="Must be exactly 11 digits" required></div>
                </div>
                <div class="setup-row">
                    <div class="setup-col"><label class="setup-label">Occupation</label><input type="text" class="setup-input" name="father_occupation" placeholder="Occupation" value="{{ old('father_occupation', optional($family)->father_occupation) }}" required></div>
                    <div class="setup-col setup-col--flex-14"><label class="setup-label">Company Address</label><input type="text" class="setup-input" name="father_company_address" placeholder="Company Address" value="{{ old('father_company_address', optional($family)->father_company_address) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Estimated Monthly Income</label>@include('components.applicant-select', ['id' => 'fatherIncomeSelect', 'name' => 'father_estimated_monthly_income', 'options' => ['Below 10,000','10,000 - 19,999','20,000 - 29,999','30,000 - 49,999','50,000 - 99,999','100,000 and above','N/A'], 'selected' => old('father_estimated_monthly_income', optional($family)->father_estimated_monthly_income), 'placeholder' => 'Select Income Range', 'required' => true])</div>
                </div>
                <div class="setup-row">
                    <div class="setup-col setup-col--flex-16"><label class="setup-label">Residence Address</label><input type="text" class="setup-input" name="father_residence_address" placeholder="Residence Address" value="{{ old('father_residence_address', optional($family)->father_residence_address) }}" required></div>
                    <div class="setup-col"><label class="setup-label">Email Address</label><input type="email" class="setup-input" name="father_email_address" placeholder="Email Address" value="{{ old('father_email_address', optional($family)->father_email_address) }}"></div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-go-step="2">Previous</button>
                <button type="button" class="btn-setup-next" data-save-step="3" data-go-step="4">Next</button>
            </div>
        </div>

        <div class="setup-form-container step-panel{{ $activeStep !== 4 ? ' step-hidden' : '' }}" id="step-4">
            <div class="setup-section">
                <div class="setup-section-header">
                    <h3 class="setup-section-title">Applying For (College)</h3>
                </div>

                <div class="setup-row setup-row--apply-program">
                    <div class="setup-col setup-col--flex-12 setup-col--w-180">
                        <label class="setup-label">Program Type</label>
                        <input type="hidden" name="apply_program" value="college">
                        <input type="text" class="setup-input" value="College" readonly>
                    </div>
                    <div class="setup-col setup-col--w-220 setup-col--apply-choice">
                        <label class="setup-label">Course</label>
                        @include('components.applicant-select', [
                            'id' => 'applyCourseSelect',
                            'name' => 'apply_course_id',
                            'options' => $collegeCourses->map(function($course) {
                                return [
                                    'value' => $course->id,
                                    'label' => $course->code . ' - ' . $course->name
                                ];
                            })->toArray(),
                            'selected' => old('apply_course_id', optional($pref)->apply_course_id),
                            'placeholder' => 'Select Course',
                        ])
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-col">
                        <label class="setup-label">Entry Classification</label>
                        @include('components.applicant-select', [
                            'id' => 'entryClassificationSelect',
                            'name' => 'entry_classification',
                            'options' => ['Regular Freshman', 'Transferee', 'Second Courser', 'Returnee'],
                            'selected' => old('entry_classification', optional($pref)->entry_classification),
                            'placeholder' => 'Select Entry Classification',
                            'required' => true,
                        ])
                    </div>
                    <div class="setup-col setup-col-sm setup-col--w-130">
                        <label class="setup-label">Year Level</label>
                        @include('components.applicant-select', [
                            'id' => 'yearLevelSelect',
                            'name' => 'year_level',
                            'options' => ['1st Year', '2nd Year', '3rd Year', '4th Year'],
                            'selected' => old('year_level', optional($pref)->year_level),
                            'placeholder' => 'Year Level',
                            'required' => true,
                        ])
                    </div>
                    <div class="setup-col setup-col-sm setup-col--w-130">
                        <label class="setup-label">Semester</label>
                        @include('components.applicant-select', [
                            'id' => 'semesterSelect',
                            'name' => 'semester',
                            'options' => ['First Semester', 'Second Semester', 'Summer'],
                            'selected' => old('semester', optional($pref)->semester),
                            'placeholder' => 'Select Semester',
                            'required' => true,
                        ])
                    </div>
                    <div class="setup-col setup-col-sm setup-col--w-130">
                        <label class="setup-label">School Year</label>
                        <input type="text" name="school_year" class="setup-input" value="{{ old('school_year', optional($pref)->school_year ?: $defaultSchoolYear) }}" required>
                    </div>
                </div>

                <div class="setup-row setup-row--app-meta">
                    <div class="setup-col setup-col-sm setup-col--w-220">
                        <label class="setup-label">Application Date</label>
                        <input type="date" name="application_date" class="setup-input" value="{{ old('application_date', optional(optional($pref)->application_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="setup-col setup-col-sm setup-col--w-160">
                        <label class="setup-label">Campus</label>
                        <input type="text" name="campus" class="setup-input" value="{{ old('campus', optional($pref)->campus ?: 'Pasig') }}" required>
                    </div>
                </div>
            </div>

            <div class="setup-nav">
                <button type="button" class="btn-setup-prev" data-go-step="3">Previous</button>
                <button type="submit" class="btn-setup-next" data-save-step="4">Save</button>
            </div>
        </div>

    </form>
    @endif
</div>

@push('scripts')
<script src="{{ asset('js/applicant-form.js') }}?v={{ time() }}"></script>
@endpush
@endsection
