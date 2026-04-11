@extends('layouts.registrar')

@section('title', 'PLP - Curriculum File')
@section('page-title', 'CURRICULUM FILE')

@section('content')
<div class="pf-page">
    <div
        class="cf-page"
        id="curriculumFilePage"
        data-course-years='@json($courseYearMap)'
        data-selected-course-id="{{ $selectedCourseId ?: '' }}"
        data-selected-curriculum-year="{{ $selectedCurriculumYear }}"
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
                <div class="cf-panel-form">
                    <div class="cf-field-wide">
                        <label class="req-modal-label" for="cfCopyTo">Copy To</label>
                        <input id="cfCopyTo" type="text" class="req-modal-input" placeholder="Copy destination">
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopyCourse">Course</label>
                        <select id="cfCopyCourse" class="req-modal-input">
                            @forelse($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name ?: $course->description }}</option>
                            @empty
                                <option value="">No Course Available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopyCurriculumYear">Curriculum Year</label>
                        <select id="cfCopyCurriculumYear" class="req-modal-input">
                            <option value="">Curriculum Year</option>
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopySemester">Semester</label>
                        <select id="cfCopySemester" class="req-modal-input">
                            <option value="">All Semester</option>
                            <option>First</option>
                            <option>Second</option>
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfCopyYearLevel">Year</label>
                        <select id="cfCopyYearLevel" class="req-modal-input">
                            <option value="">All Year Levels</option>
                            <option>First Year</option>
                            <option>Second Year</option>
                            <option>Third Year</option>
                            <option>Fourth Year</option>
                        </select>
                    </div>

                    <div class="cf-actions">
                        <button type="button" class="pf-btn-new">Copy Curriculum</button>
                    </div>
                </div>
            </section>

            <section class="cfg-card cf-panel-card">
                <h3 class="cf-panel-title">Setup Curriculum</h3>
                <div class="cf-panel-form">
                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupCurriculumYear">Curriculum Year</label>
                        <select id="cfSetupCurriculumYear" class="req-modal-input">
                            <option value="">Curriculum Year</option>
                        </select>
                    </div>

                    <div class="cf-field-row">
                        <label class="req-modal-label" for="cfSetupProgram">Program</label>
                        <select id="cfSetupProgram" class="req-modal-input">
                            @forelse($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name ?: $course->description }}</option>
                            @empty
                                <option value="">No Course Available</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="cf-subtitle">Adding of Courses Per Year Level</div>

                    <div class="cf-field-split">
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupTerm">Term</label>
                            <select id="cfSetupTerm" class="req-modal-input">
                                <option value="">Term</option>
                                <option>First</option>
                                <option>Second</option>
                            </select>
                        </div>
                        <div class="cf-field-row">
                            <label class="req-modal-label" for="cfSetupYearLevel">Yr Level</label>
                            <select id="cfSetupYearLevel" class="req-modal-input">
                                <option value="">Year Level</option>
                                <option>First Year</option>
                                <option>Second Year</option>
                                <option>Third Year</option>
                                <option>Fourth Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="cf-actions">
                        <button type="button" class="pf-btn-new">Save Setup</button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/curriculum-file.js') }}"></script>
@endpush
@endsection
