@php($schoolName = trim((string) config('app.school_name', 'University of Pasig City')) ?: 'University of Pasig City')

<div class="rfl-strength-sheet rfl-assignment-sheet rfl-plotted-sheet">
    <div class="rfl-strength-header text-center">
        <div class="rfl-strength-school">{{ $schoolName }}</div>
        <div class="rfl-strength-title">FACULTY ASSIGNMENT FORM - PLOTTED</div>
        <div class="rfl-strength-subtitle">{{ $printTermLabel }}</div>
    </div>

    <div class="rfl-strength-meta-row">
        <div class="rfl-strength-meta-left">
            <div class="rfl-strength-line-row">
                <span class="rfl-strength-line-label">NAME OF INSTRUCTOR:</span>
                <span class="rfl-strength-line-fill">{{ strtoupper($faculty->name) }}</span>
            </div>
            <div class="rfl-strength-line-row">
                <span class="rfl-strength-line-label">Faculty ID:</span>
                <span class="rfl-strength-line-fill">{{ strtoupper($faculty->code) }}</span>
            </div>
        </div>

        <div class="rfl-strength-meta-right">
            <div class="rfl-strength-line-row">
                <span class="rfl-strength-line-label">Generated:</span>
                <span class="rfl-strength-line-fill">{{ $printGeneratedAt->format('m/d/Y h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="student-table-wrapper rfl-strength-table-wrap">
        <table class="student-table rfl-strength-table rfl-plotted-table">
            <thead>
                <tr>
                    @foreach($weekDays as $dayName)
                        <th>{{ strtoupper($dayName) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach($weekDays as $dayName)
                        <td>
                            @forelse($scheduleByDay[$dayName] as $entry)
                                <div class="rfl-plotted-item">
                                    <div>{{ $entry['time'] }}</div>
                                    <div>{{ $entry['code'] }}</div>
                                    <div>{{ $entry['section'] !== '' ? $entry['section'] : '-' }}</div>
                                    <div>{{ $entry['room'] !== '' ? $entry['room'] : '-' }}</div>
                                </div>
                            @empty
                                <div class="rfl-plotted-empty">-</div>
                            @endforelse
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <div class="rfl-strength-footer-note">Generated {{ $printGeneratedAt->format('m/d/Y h:i A') }}</div>
</div>
