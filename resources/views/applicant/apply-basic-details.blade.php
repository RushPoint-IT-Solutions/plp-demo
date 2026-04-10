@extends('layouts.login')

@section('title', 'PLP - Applicant Basic Details')

@section('content')
<div class="app-apply-shell">
    <div class="app-apply-card app-apply-basic-card app-apply-basic-card-minimal">
        <h2 class="app-apply-title">Profile Verification</h2>
        <p class="app-apply-basic-subtitle app-apply-basic-subtitle-minimal">Provide only the basic details needed to continue.</p>

        <form action="{{ route('applicant.apply.basic-details.store') }}" method="POST" class="app-apply-basic-form">
            @csrf

            <input type="hidden" name="email_address" value="{{ old('email_address', 'preview@plp.test') }}">
            <input type="hidden" name="mobile_number" value="{{ old('mobile_number', '09123456789') }}">
            <input type="hidden" name="application_track" value="{{ old('application_track', 'college') }}">

            @if($errors->any())
            <div class="app-apply-errors">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="app-apply-section-line"></div>

            <div class="app-apply-grid app-apply-grid-compact">
                <div class="app-apply-field">
                    <label for="last_name">Last Name</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="LAST NAME">
                </div>

                <div class="app-apply-field">
                    <label for="first_name">First Name</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="FIRST NAME">
                </div>

                <div class="app-apply-field">
                    <label for="middle_name">Middle Name</label>
                    <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}" placeholder="MIDDLE NAME">
                    <label class="app-apply-inline-check">
                        <input type="checkbox" name="has_no_middle_name" value="1" {{ old('has_no_middle_name') ? 'checked' : '' }}>
                        <span>No Middle Name Applicable</span>
                    </label>
                </div>

                <div class="app-apply-field">
                    <label for="date_of_birth">Birthdate</label>
                    <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}">
                </div>

                <div class="app-apply-field">
                    <label for="nationality">Nationality</label>
                    <div class="app-apply-selectbox" data-custom-select>
                        <select id="nationality" name="nationality" class="app-apply-native-select js-app-apply-enhance" data-placeholder="- nationality -">
                            <option value="">- nationality -</option>
                            <option value="Filipino" {{ old('nationality') === 'Filipino' ? 'selected' : '' }}>Filipino</option>
                            <option value="Other" {{ old('nationality') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <button type="button" class="app-apply-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span class="app-apply-select-trigger-text" data-select-current>- nationality -</span>
                            <span class="app-apply-select-trigger-caret" aria-hidden="true"></span>
                        </button>
                        <ul class="app-apply-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                    </div>
                </div>

                <div class="app-apply-field">
                    <label for="religion">Religion</label>
                    <div class="app-apply-selectbox" data-custom-select>
                        <select id="religion" name="religion" class="app-apply-native-select js-app-apply-enhance" data-placeholder="- religion -">
                            <option value="">- religion -</option>
                            <option value="Roman Catholic" {{ old('religion') === 'Roman Catholic' ? 'selected' : '' }}>Roman Catholic</option>
                            <option value="Christian" {{ old('religion') === 'Christian' ? 'selected' : '' }}>Christian</option>
                            <option value="Seventh Day Adventist" {{ old('religion') === 'Seventh Day Adventist' ? 'selected' : '' }}>Seventh Day Adventist</option>
                            <option value="Others" {{ old('religion') === 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        <button type="button" class="app-apply-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span class="app-apply-select-trigger-text" data-select-current>- religion -</span>
                            <span class="app-apply-select-trigger-caret" aria-hidden="true"></span>
                        </button>
                        <ul class="app-apply-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
                    </div>
                </div>
            </div>

            <div class="app-apply-actions app-apply-actions-spread app-apply-actions-sticky app-apply-actions-minimal">
                <button type="reset" class="app-apply-link-btn app-apply-clear-btn">Clear Entries</button>
                <button type="submit" class="pf-btn-new app-apply-primary-btn">Continue</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ mix('js/apply-basic-details.js') }}"></script>
@endpush
