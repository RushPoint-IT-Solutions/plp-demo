@forelse($applicants as $index => $applicant)
@php
    $displayName = trim((string) $applicant->first_name . ' ' . (string) $applicant->last_name);
    $preference = optional($applicant->applicationPreference);
    $preferredCourse = optional($preference->course);
    $programLabel = 'N/A';
    if ($preference->apply_program === 'college') {
        $programLabel = $preferredCourse->name ?: ($preferredCourse->code ?: 'College');
    } elseif ($preference->apply_program === 'senior_high') {
        $programLabel = $preference->apply_strand ?: 'Senior High';
    }

    $rawApplicationStatus = strtolower(trim((string) ($applicant->application_status ?: 'in process')));
    $statusRaw = 'In Process';
    if ($rawApplicationStatus === 'submitted' || $rawApplicationStatus === 'document submitted') {
        $statusRaw = 'Document Submitted';
    } elseif ($rawApplicationStatus === 'on probation' || $rawApplicationStatus === 'on_probation') {
        $statusRaw = 'On Probation';
    } elseif ($rawApplicationStatus === 'in process' || $rawApplicationStatus === 'in_process') {
        $statusRaw = 'In Process';
    } elseif ($rawApplicationStatus === 'rejected') {
        $statusRaw = 'Rejected';
    } elseif ($rawApplicationStatus === 'incomplete' || $rawApplicationStatus === 'draft') {
        $statusRaw = 'Incomplete';
    } elseif ($rawApplicationStatus === 'accepted') {
        $statusRaw = 'Accepted';
    }

    $statusClass = 'app-status-pending';
    if ($statusRaw === 'Accepted' || $statusRaw === 'Document Submitted' || $statusRaw === 'In Process') {
        $statusClass = 'app-status-accepted';
    }
    if ($statusRaw === 'Rejected') {
        $statusClass = 'app-status-rejected';
    }

    $dateApplied = $applicant->application_submitted_at ?: $applicant->created_at;
@endphp
<tr
    data-pk="{{ $applicant->id }}"
    data-id="{{ $applicant->applicant_id }}"
    data-name="{{ e($displayName) }}"
    data-program="{{ e($programLabel) }}"
    data-application-status="{{ e($statusRaw) }}"
    data-exam-date="{{ optional($applicant->exam_date)->format('Y-m-d') }}"
    data-exam-time="{{ optional($applicant->exam_date)->format('H:i') }}"
    data-exam-room="{{ e((string) ($applicant->exam_room ?? '')) }}"
    data-exam-result-status="{{ e((string) ($applicant->exam_result_status ?: 'Pending')) }}"
    data-exam-score="{{ $applicant->exam_score !== null ? $applicant->exam_score : '' }}"
>
    <td>{{ ($applicants->firstItem() ?? 1) + $index }}</td>
    <td>{{ $applicant->applicant_id }}</td>
    <td>{{ $displayName ?: 'N/A' }}</td>
    <td>{{ $programLabel }}</td>
    <td>{{ optional($dateApplied)->format('M d, Y') ?: 'N/A' }}</td>
    <td>{{ optional($applicant->updated_at)->format('M d, Y') ?: 'N/A' }}</td>
    <td class="js-application-status"><span class="{{ $statusClass }}"><span class="app-status-dot"></span>{{ strtoupper($statusRaw) }}</span></td>
</tr>
@empty
<tr>
    <td colspan="7">No applicants found.</td>
</tr>
@endforelse
