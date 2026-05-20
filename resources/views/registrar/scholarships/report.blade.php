@extends('layouts.registrar')

@section('title', 'Scholar Report')

@push('styles')
<style>
.shr-page { padding:24px 28px; }
.shr-header { display:flex; justify-content:space-between; gap:16px; align-items:flex-start; margin-bottom:18px; }
.shr-title { margin:0; color:#0f172a; font-size:24px; font-weight:800; }
.shr-muted { color:#64748b; font-size:13px; margin-top:4px; }
.shr-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 1px 4px rgba(15,23,42,.06); margin-bottom:16px; overflow:hidden; }
.shr-card-head { padding:14px 16px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; gap:12px; align-items:center; flex-wrap:wrap; }
.shr-card-head h2 { margin:0; color:#1e293b; font-size:15px; font-weight:800; }
.shr-card-body { padding:16px; }
.shr-filter { display:flex; gap:8px; flex-wrap:wrap; align-items:end; }
.shr-field label { display:block; font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px; }
.shr-field select { min-width:180px; border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font-size:13px; color:#1e293b; }
.shr-btn { border:0; border-radius:8px; padding:8px 14px; font-size:13px; font-weight:700; text-decoration:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
.shr-btn.primary { background:#004d27; color:#fff; }
.shr-btn.soft { background:#f1f5f9; color:#334155; border:1px solid #e2e8f0; }
.shr-stats { display:flex; gap:10px; flex-wrap:wrap; }
.shr-pill { background:#f0fdf4; color:#166534; border-radius:999px; padding:6px 12px; font-size:12px; font-weight:800; }
.shr-table { width:100%; border-collapse:collapse; font-size:13px; }
.shr-table th { background:#f8fafc; color:#64748b; font-size:11px; text-transform:uppercase; letter-spacing:.05em; text-align:left; padding:10px; border-bottom:1px solid #e2e8f0; }
.shr-table td { padding:10px; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:top; }
.shr-name { color:#0f172a; font-weight:800; }
.shr-meta { color:#64748b; font-size:12px; }
.shr-badge { display:inline-block; border-radius:999px; padding:2px 9px; font-size:11px; font-weight:800; background:#dbeafe; color:#1d4ed8; }
</style>
@endpush

@section('content')
<div class="shr-page">
    <div class="shr-header">
        <div>
            <h1 class="shr-title">Scholar Report</h1>
            <div class="shr-muted">List of all scholars grouped by scholarship type.</div>
        </div>
        <a class="shr-btn soft" href="{{ route('registrar.registrar-menu.scholarships.index') }}">Program Setup</a>
    </div>

    <div class="shr-card">
        <div class="shr-card-head">
            <form class="shr-filter" method="GET" action="{{ route('registrar.registrar-menu.scholarships.report') }}">
                <div class="shr-field"><label>Scholarship Type</label><select name="category"><option value="">All types</option>@foreach($categories as $item)<option value="{{ $item }}" {{ $category === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                <div class="shr-field"><label>School Year</label><select name="school_year"><option value="">All years</option>@foreach($schoolYears as $item)<option value="{{ $item }}" {{ $schoolYear === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                <div class="shr-field"><label>Award Status</label><select name="award_status"><option value="">All statuses</option>@foreach(['Active','For Renewal','Renewed','Suspended','Ended'] as $item)<option value="{{ $item }}" {{ $awardStatus === $item ? 'selected' : '' }}>{{ $item }}</option>@endforeach</select></div>
                <button class="shr-btn primary" type="submit">Filter</button>
            </form>
            <div class="shr-stats">
                <span class="shr-pill">{{ $totalScholars }} unique scholars</span>
                <span class="shr-pill">{{ $totalTags }} scholarship tags</span>
            </div>
        </div>
    </div>

    @forelse($scholarsByType as $type => $rows)
        <div class="shr-card">
            <div class="shr-card-head">
                <h2>{{ $type }}</h2>
                <span class="shr-pill">{{ $rows->pluck('student_id')->unique()->count() }} scholars</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="shr-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Scholarship</th>
                            <th>Term</th>
                            <th>Monitoring</th>
                            <th>Financial Posting</th>
                            <th>Award</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $tag)
                            @php
                                $student = $tag->student;
                                $profile = optional($student)->profile;
                                $studentName = $profile ? trim($profile->first_name.' '.$profile->last_name) : optional($student)->name;
                            @endphp
                            <tr>
                                <td>
                                    <div class="shr-name">{{ $studentName ?: 'Student #' . $tag->student_id }}</div>
                                    <div class="shr-meta">{{ optional($student)->student_no }}</div>
                                </td>
                                <td>{{ optional(optional($student)->canonicalCourse)->code ?: optional($student)->program ?: 'N/A' }}</td>
                                <td>{{ optional($tag->program)->name }}</td>
                                <td>SY {{ $tag->school_year ?: 'N/A' }}<br>{{ $tag->semester ?: 'N/A' }}</td>
                                <td>
                                    <span class="shr-badge">{{ $tag->monitoring_status ?: 'Not set' }}</span><br>
                                    <small>GWA: {{ $tag->current_gwa ?: 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="shr-badge">{{ $tag->financial_posting_status }}</span><br>
                                    <small>Amount: {{ $tag->posted_amount ? number_format((float) $tag->posted_amount, 2) : 'N/A' }}</small>
                                </td>
                                <td>{{ $tag->award_status }}<br><small>Renewal: {{ $tag->renewal_status ?: 'N/A' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="shr-card"><div class="shr-card-body" style="text-align:center;color:#94a3b8;padding:32px;">No scholar records found.</div></div>
    @endforelse
</div>
@endsection
