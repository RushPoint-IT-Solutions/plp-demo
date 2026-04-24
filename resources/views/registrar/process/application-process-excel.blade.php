<table border="1">
    <thead>
    <tr>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Applicant ID</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Last Name</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">First Name</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Middle Name</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Program</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Date Applied</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Date Last Update</th>
        <th style="background-color: #ffffff; color: #000000; font-weight: bold;">Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($applicants as $applicant)
        @php
            $preference = optional($applicant->applicationPreference);
            $preferredCourse = optional($preference->course);
            $programLabel = 'N/A';
            if ($preference->apply_program === 'college') {
                $programLabel = $preferredCourse->name ?: ($preferredCourse->code ?: 'College');
            } elseif ($preference->apply_program === 'senior_high') {
                $programLabel = $preference->apply_strand ?: 'Senior High';
            }

            $rawStatus = strtolower(trim((string) ($applicant->application_status ?: 'in process')));
            $statusText = 'In Process';
            if ($rawStatus === 'submitted' || $rawStatus === 'document submitted') {
                $statusText = 'Document Submitted';
            } elseif ($rawStatus === 'on probation' || $rawStatus === 'on_probation') {
                $statusText = 'On Probation';
            } elseif ($rawStatus === 'rejected') {
                $statusText = 'Rejected';
            } elseif ($rawStatus === 'incomplete' || $rawStatus === 'draft') {
                $statusText = 'Incomplete';
            } elseif ($rawStatus === 'accepted') {
                $statusText = 'Accepted';
            }

            $dateApplied = $applicant->application_submitted_at ?: $applicant->created_at;
        @endphp
        <tr>
            <td style="background-color: #ffffff; color: #000000;">{{ $applicant->applicant_id }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ strtoupper((string) $applicant->last_name) }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ strtoupper((string) $applicant->first_name) }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ strtoupper((string) $applicant->middle_name) }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ $programLabel }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ optional($dateApplied)->format('Y-m-d') ?: 'N/A' }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ optional($applicant->updated_at)->format('Y-m-d') ?: 'N/A' }}</td>
            <td style="background-color: #ffffff; color: #000000;">{{ strtoupper($statusText) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
