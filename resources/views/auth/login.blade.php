@extends('layouts.login')

@section('title', 'PLP - ' . ucfirst($module) . ' Login')

@section('content')
<div class="login-card-wrapper">
    <div class="login-card">
        {{-- Module Title --}}
        <h2 class="login-card-title">{{ strtoupper($module) }} LOGIN</h2>

        <form method="POST" action="{{ $module === 'student' ? route('student.login.submit') : ($module === 'applicant' ? route('applicant.login.submit') : ($module === 'parent' ? route('parent.login.submit') : (in_array($module, ['registrar', 'faculty']) ? route('module.login.submit') : route('demo.login'))) ) }}">
            @csrf

            {{-- Pass the module through so we redirect to the right pages --}}
            <input type="hidden" name="module" value="{{ $module }}">

            {{-- Username / Student Number / Applicant Number / Parent Number --}}
            <div class="login-field-group">
                @if($module === 'applicant')
                    <label for="username" class="login-label">APPLICANT NUMBER</label>
                @elseif($module === 'parent')
                    <label for="username" class="login-label">PARENT NUMBER / USERNAME</label>
                @elseif($module === 'student')
                    <label for="username" class="login-label">STUDENT NUMBER / USERNAME</label>
                @else
                    <label for="username" class="login-label">USERNAME</label>
                @endif
                <input
                    id="username"
                    type="text"
                    class="login-input{{ $errors->has('username') ? ' is-invalid' : '' }}"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="{{ $module === 'applicant' ? 'Enter Applicant Number' : ($module === 'student' ? 'Enter Student Number or Username' : ($module === 'parent' ? 'Enter Parent Number or Username' : 'Enter Username')) }}"
                    required
                    autofocus
                >
                @if ($errors->has('username'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('username') }}</strong>
                    </span>
                @endif
            </div>

            {{-- Password --}}
            <div class="login-field-group">
                <label for="password" class="login-label">PASSWORD</label>
                <div class="login-password-wrapper">
                    <input
                        id="password"
                        type="password"
                        class="login-input{{ $errors->has('password') ? ' is-invalid' : '' }}"
                        name="password"
                        placeholder="••••••••"
                        required
                    >
                    <button type="button" class="password-toggle-btn" onclick="togglePassword()" tabindex="-1">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16">
                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                        </svg>
                        <svg id="eye-slash-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#aaa" viewBox="0 0 16 16" style="display:none;">
                            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299l.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
                            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884l-12-12 .708-.708 12 12-.708.708z"/>
                        </svg>
                    </button>
                </div>
                @if ($errors->has('password'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                @endif
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="login-options">
                <label class="setup-checkbox-label login-remember-check" for="remember">
                    <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <div class="login-forgot">
                        Forgot Password? <a href="{{ route('password.request') }}">Click HERE</a>
                    </div>
                @endif
            </div>

            {{-- Sign In Button --}}
            <div class="text-center mt-4 {{ $module === 'parent' ? 'login-parent-actions' : '' }}">
                <button type="submit" class="login-submit-btn {{ $module === 'parent' ? 'login-submit-btn--block' : '' }}">Sign In</button>

                @if($module === 'parent')
                    <p class="login-parent-caption">No account yet?
                        <button type="button" id="parentCreateAccountTrigger" class="login-parent-create-link login-parent-create-link-btn">Click here to create account</button>
                    </p>
                @endif
            </div>
        </form>
    </div>
</div>

@if($module === 'parent')
<div class="login-parent-create-modal" id="parentCreateAccountModal" tabindex="-1" aria-labelledby="parentCreateAccountModalLabel" aria-hidden="true">
    <div class="login-parent-create-modal-dialog" role="dialog" aria-modal="true">
        <div class="modal-content login-parent-create-modal-content">
            <div class="modal-body">
                <button type="button" class="login-parent-create-close" id="parentCreateAccountClose" aria-label="Close">&times;</button>
                <h3 class="login-parent-create-title" id="parentCreateAccountModalLabel">APPLY NOW!</h3>

                <form
                    class="login-parent-create-form"
                    id="parentCreateAccountForm"
                    data-redirect-url="{{ route('parent.grades') }}"
                    novalidate
                >
                    <div class="login-parent-create-grid">
                        <div class="login-parent-create-field">
                            <label>FIRST NAME <span>*</span></label>
                            <input type="text" class="form-control" name="parent_first_name" autocomplete="given-name" placeholder="First Name" />
                        </div>
                        <div class="login-parent-create-field">
                            <label>MIDDLE NAME <span>*</span></label>
                            <input type="text" class="form-control" name="parent_middle_name" autocomplete="additional-name" placeholder="Middle Name" />
                        </div>
                        <div class="login-parent-create-field">
                            <label>LAST NAME <span>*</span></label>
                            <input type="text" class="form-control" name="parent_last_name" autocomplete="family-name" placeholder="Last Name" />
                        </div>

                        <div class="login-parent-create-field">
                            <label>SOCIAL HONORIFICS <span>*</span></label>
                            <select class="form-control" name="parent_honorific">
                                <option value="">Select Honorifics</option>
                                <option>Ms.</option>
                                <option>Mrs.</option>
                                <option>Mr.</option>
                            </select>
                        </div>
                        <div class="login-parent-create-field">
                            <label>EMAIL ADDRESS <span>*</span></label>
                            <input type="email" class="form-control" name="parent_email" autocomplete="email" placeholder="Email" />
                        </div>
                        <div class="login-parent-create-field">
                            <label>RELATIONSHIP <span>*</span></label>
                            <select class="form-control" name="parent_relationship">
                                <option value="">Select relationship</option>
                                <option>Father</option>
                                <option>Mother</option>
                                <option>Guardian</option>
                            </select>
                        </div>

                        <div class="login-parent-create-field">
                            <label>CHILD'S STUDENT NO. <span>*</span></label>
                            <input type="text" class="form-control" name="child_student_no" autocomplete="off" placeholder="Student No." />
                        </div>
                        <div class="login-parent-create-field">
                            <label>CHILD'S BIRTHDATE <span>*</span></label>
                            <input type="date" class="form-control" name="child_birthdate" placeholder="Child's Birthdate" />
                        </div>
                        <div class="login-parent-create-field">
                            <label>USERNAME <span>*</span></label>
                            <input type="text" class="form-control" name="parent_username" autocomplete="username" placeholder="Username" />
                        </div>
                        <div class="login-parent-create-field">
                            <label>PASSWORD <span>*</span></label>
                            <input type="password" class="form-control" name="parent_password" autocomplete="new-password" placeholder="Type Password" />
                        </div>
                        <div class="login-parent-create-field">
                            <label>CONFIRM PASSWORD <span>*</span></label>
                            <input type="password" class="form-control" name="parent_password_confirmation" autocomplete="new-password" placeholder="Confirm password" />
                        </div>
                    </div>

                    <div class="login-parent-create-actions">
                        <button type="submit" class="btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="{{ asset('js/login.js') }}"></script>
@endpush
