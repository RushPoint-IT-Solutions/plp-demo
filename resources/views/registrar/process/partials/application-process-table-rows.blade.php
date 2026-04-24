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

    // New Badge System
    $statusBadgeClass = 'status-badge-incomplete';
    if ($statusRaw === 'Accepted') $statusBadgeClass = 'status-badge-approved';
    elseif ($rawApplicationStatus === 'rejected') $statusBadgeClass = 'status-badge-rejected';
    elseif ($statusRaw === 'Document Submitted' || $statusRaw === 'In Process' || $statusRaw === 'On Probation') $statusBadgeClass = 'status-badge-pending';

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
    <td style="text-align: center;">
        <input type="checkbox" class="applicant-row-checkbox app-apply-check-input" value="{{ $applicant->id }}" onclick="event.stopPropagation()">
    </td>
    <td>
        <div class="applicant-id-stack">
            <span class="applicant-name-main">{{ $displayName ?: 'N/A' }}</span>
            <span style="font-size: 0.75rem; color: #006837; font-weight: 600;">{{ $applicant->applicant_id }}</span>
        </div>
    </td>
    <td style="padding: 12px 8px !important;">
        <div style="font-size: 0.85rem; font-weight: 600; color: #1e293b; white-space: normal; word-break: break-word; line-height: 1.3; max-width: 350px;">
            {{ $programLabel }}
        </div>
    </td>
    <td style="font-size: 0.85rem; font-weight: 600; color: #1e293b; white-space: nowrap;">{{ optional($dateApplied)->format('M d, Y') ?: 'N/A' }}</td>
    <td style="font-size: 0.85rem; font-weight: 600; color: #1e293b; white-space: nowrap;">{{ optional($applicant->updated_at)->format('M d, Y') ?: 'N/A' }}</td>
    <td style="text-align: center;">
        @php
            $statusBadgeClass = 'status-badge-in-process'; // Yellow/Amber (In Process)
            if ($statusRaw === 'Accepted') {
                $statusBadgeClass = 'status-badge-approved'; // Green
            } elseif ($statusRaw === 'Document Submitted') {
                $statusBadgeClass = 'status-badge-submitted'; // Blue
            } elseif ($statusRaw === 'Rejected' || $statusRaw === 'Incomplete') {
                $statusBadgeClass = 'status-badge-incomplete'; // Red
            }
        @endphp
        <span class="status-badge {{ $statusBadgeClass }}">{{ strtoupper($statusRaw) }}</span>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" style="text-align: center; padding: 40px !important; color: #94a3b8; font-weight: 500;">
        <div class="mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        No applicants found matching your filters.
    </td>
</tr>
@endforelse
