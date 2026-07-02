@php
    $selectedCourses = $program ? $program->applicable_course_ids_array : [];
@endphp
<div class="sch-form">
    <section class="sch-form-section">
        <h3 class="sch-form-section-title">Program Details</h3>
        <div class="sch-grid">
            <div class="sch-field wide">
                <label>Scholarship Name</label>
                <input name="name" value="{{ old('name', optional($program)->name) }}" required>
            </div>
            <div class="sch-field">
                <label>Category</label>
                <select name="category" required>
                    @foreach($categories as $item)
                        <option value="{{ $item }}" {{ old('category', optional($program)->category) === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sch-field">
                <label>Status</label>
                <select name="status" required>
                    @foreach($statuses as $item)
                        <option value="{{ $item }}" {{ old('status', optional($program)->status ?: 'Open') === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sch-field full">
                <label>Description / Notes</label>
                <textarea name="description">{{ old('description', optional($program)->description) }}</textarea>
            </div>
        </div>
    </section>

    <section class="sch-form-section">
        <h3 class="sch-form-section-title">Coverage & Term</h3>
        <div class="sch-grid">
            <div class="sch-field">
                <label>Coverage Type</label>
                <select name="coverage_type" required>
                    @foreach($coverageTypes as $item)
                        <option value="{{ $item }}" {{ old('coverage_type', optional($program)->coverage_type) === $item ? 'selected' : '' }}>{{ $item }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sch-field">
                <label>Amount / Percent</label>
                <input type="number" step="0.01" min="0" name="coverage_value" value="{{ old('coverage_value', optional($program)->coverage_value) }}">
            </div>
            <div class="sch-field">
                <label>School Year</label>
                <input name="school_year" value="{{ old('school_year', optional($program)->school_year) }}" placeholder="2026-2027">
            </div>
            <div class="sch-field">
                <label>Semester</label>
                <input name="semester" value="{{ old('semester', optional($program)->semester) }}" placeholder="First Semester">
            </div>
        </div>
    </section>

    <section class="sch-form-section">
        <h3 class="sch-form-section-title">Eligibility & Capacity</h3>
        <div class="sch-grid">
            <div class="sch-field">
                <label>Available Slots</label>
                <input type="number" min="0" name="available_slots" value="{{ old('available_slots', optional($program)->available_slots) }}">
            </div>
            <div class="sch-field">
                <label>Maintaining GWA</label>
                <input type="number" step="0.01" min="1" max="5" name="maintaining_gwa" value="{{ old('maintaining_gwa', optional($program)->maintaining_gwa) }}">
            </div>
            <div class="sch-field wide">
                <label>Year Level Eligibility</label>
                <input name="year_level_eligibility" value="{{ old('year_level_eligibility', optional($program)->year_level_eligibility) }}" placeholder="1st Year, 2nd Year, All">
            </div>
            <div class="sch-field full">
                <label>Applicable Program / Course</label>
                <select name="applicable_course_ids[]" multiple size="4">
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ in_array((string) $course->id, array_map('strval', $selectedCourses), true) ? 'selected' : '' }}>{{ $course->code }} - {{ $course->name }}</option>
                    @endforeach
                </select>
                <div class="sch-help">Hold Ctrl or Shift to select multiple programs.</div>
            </div>
        </div>
    </section>
</div>
