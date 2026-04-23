@extends('layouts.registrar')

@section('title', 'PLP - Curriculum File')
@section('page-title', 'CURRICULUM FILE')

@section('content')
<div class="pf-page">
    @if(session('curriculum_file_success'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('curriculum_file_success') }}
        </div>
    @endif

    @if(session('curriculum_file_error'))
        <div class="alert alert-danger mb-3" role="alert">
            {{ session('curriculum_file_error') }}
        </div>
    @endif

    <div
        class="cf-page"
        id="curriculumFilePage"
        data-course-years='@json($courseYearMap)'
        data-selected-course-id="{{ $selectedCourseId ?: '' }}"
        data-selected-curriculum-year="{{ $selectedCurriculumYear }}"
        data-success="{{ session('curriculum_file_success', '') }}"
        data-error="{{ session('curriculum_file_error', '') }}"
        data-pre-requisites-url="{{ route('registrar.registrar-menu.academic-master.pre-requisites') }}"
    >
        <section class="cfg-card cf-toolbar-card">
            <div class="cf-toolbar-grid">
                <div class="cf-filter-field">
                    <label class="req-modal-label" for="cfProgram">Program</label>
                    <select id="cfProgram" class="req-modal-input">
                        @forelse($courses as $course)
                            <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                                {{ $course->name ?: $course->description }}
                            </option>
                        @empty
                            <option value="">No Course Available</option>
                        @endforelse
                    </select>
                </div>
                <div class="cf-filter-field">
                    <label class="req-modal-label" for="cfCurriculumYear">Curriculum Year</label>
                    <select id="cfCurriculumYear" class="req-modal-input">
                        <option value="">Curriculum Year</option>
                    </select>
                </div>
                <div class="cf-toolbar-action">
                    <button type="button" class="pf-btn-new" id="cfViewListBtn">View List</button>
                    <button type="button" class="pf-btn-new" id="cfOpenPrerequisitesBtn">Open Pre-Requisites</button>
                </div>
            </div>
        </section>

        <div class="cf-grid-two">
            <section class="cfg-card cf-panel-card">
                <h3 class="cf-panel-title">Copy Curriculum</h3>
                <form class="cf-panel-form" id="cfCopyForm" method="POST" action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.copy') }}">
                    @csrf

                    @if($errors->has('copy_source_course_id') || $errors->has('copy_source_curriculum_year') || $errors->has('copy_course_id') || $errors->has('copy_curriculum_year') || $errors->has('copy_to') || $errors->has('copy_semester') || $errors->has('copy_year_level'))
                        <div class="pf-form-error-box">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <input type="hidden" name="copy_source_course_id" id="cfCopySourceCourseId" value="{{ old('copy_source_course_id', $selectedCourseId ?: '') }}">
                    <input type="hidden" name="copy_source_curriculum_year" id="cfCopySourceCurriculumYear" value="{{ old('copy_source_curriculum_year', $selectedCurriculumYear) }}">

                    <div class="cf-field-wide">
                        <label class="req-modal-label" for="cfCopyTo">Copy To</label>
                        <input id="cfCopyTo" name="copy_to" type="text" class="req-modal-input" placeholder="Copy destination" value="{{ old('copy_to') }}">
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopyCourse">Course</label>
                        <select id="cfCopyCourse" name="copy_course_id" class="req-modal-input">
                            @forelse($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) old('copy_course_id', $selectedCourseId ?: '') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->name ?: $course->description }}
                                </option>
                            @empty
                                <option value="">No Course Available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopyCurriculumYear">Destination Curriculum Year</label>
                        <input id="cfCopyCurriculumYear" name="copy_curriculum_year" type="text" class="req-modal-input" placeholder="New curriculum year" value="{{ old('copy_curriculum_year') }}">
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopySemester">Semester</label>
                        <select id="cfCopySemester" name="copy_semester" class="req-modal-input">
                            <option value="">All Semester</option>
                            <option value="First" {{ old('copy_semester') === 'First' ? 'selected' : '' }}>First</option>
                            <option value="Second" {{ old('copy_semester') === 'Second' ? 'selected' : '' }}>Second</option>
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopyYearLevel">Year</label>
                        <select id="cfCopyYearLevel" name="copy_year_level" class="req-modal-input">
                            <option value="">All Year Levels</option>
                            <option value="First Year" {{ old('copy_year_level') === 'First Year' ? 'selected' : '' }}>First Year</option>
                            <option value="Second Year" {{ old('copy_year_level') === 'Second Year' ? 'selected' : '' }}>Second Year</option>
                            <option value="Third Year" {{ old('copy_year_level') === 'Third Year' ? 'selected' : '' }}>Third Year</option>
                            <option value="Fourth Year" {{ old('copy_year_level') === 'Fourth Year' ? 'selected' : '' }}>Fourth Year</option>
                        </select>
                    </div>

                    <div class="cf-actions">
                        <button type="submit" class="pf-btn-new" id="cfCopySubmitBtn">Copy Curriculum</button>
                    </div>
                </form>
            </section>

            <section class="cfg-card cf-panel-card">
                <h3 class="cf-panel-title">Setup Curriculum</h3>
                <form class="cf-panel-form" id="cfSetupForm" method="POST" action="{{ route('registrar.registrar-menu.academic-master.curriculum-file.setup') }}">
                    @csrf

                    @if($errors->has('setup_course_id') || $errors->has('setup_curriculum_year') || $errors->has('setup_term') || $errors->has('setup_year_level'))
                        <div class="pf-form-error-box">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupCurriculumYear">Curriculum Year</label>
                        <select id="cfSetupCurriculumYear" name="setup_curriculum_year" class="req-modal-input" data-initial-year="{{ old('setup_curriculum_year', $selectedCurriculumYear) }}">
                            <option value="">Curriculum Year</option>
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupProgram">Program</label>
                        <select id="cfSetupProgram" name="setup_course_id" class="req-modal-input">
                            @forelse($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) old('setup_course_id', $selectedCourseId ?: '') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->name ?: $course->description }}
                                </option>
                            @empty
                                <option value="">No Course Available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="cf-subtitle">Adding of Courses Per Year Level</div>

                    <div class="cf-field-split">
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupTerm">Term</label>
                            <select id="cfSetupTerm" name="setup_term" class="req-modal-input">
                                <option value="">Term</option>
                                <option value="First" {{ old('setup_term') === 'First' ? 'selected' : '' }}>First</option>
                                <option value="Second" {{ old('setup_term') === 'Second' ? 'selected' : '' }}>Second</option>
                            </select>
                        </div>
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupYearLevel">Yr Level</label>
                            <select id="cfSetupYearLevel" name="setup_year_level" class="req-modal-input">
                                <option value="">Year Level</option>
                                <option value="First Year" {{ old('setup_year_level') === 'First Year' ? 'selected' : '' }}>First Year</option>
                                <option value="Second Year" {{ old('setup_year_level') === 'Second Year' ? 'selected' : '' }}>Second Year</option>
                                <option value="Third Year" {{ old('setup_year_level') === 'Third Year' ? 'selected' : '' }}>Third Year</option>
                                <option value="Fourth Year" {{ old('setup_year_level') === 'Fourth Year' ? 'selected' : '' }}>Fourth Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="cf-actions">
                        <button type="submit" class="pf-btn-new" id="cfSaveSetupBtn">Save Setup</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/curriculum-file.js') }}"></script>
@endpush
@endsection
