@php($blankRows = max(0, 20 - $printRows->count()))
@php($schoolName = trim((string) config('app.school_name', 'University of Pasig City')) ?: 'University of Pasig City')

<div class="rfl-strength-sheet rfl-assignment-sheet">
    <div class="rfl-strength-header text-center">
        <div class="rfl-strength-school">{{ $schoolName }}</div>
        <div class="rfl-strength-title">FACULTY ASSIGNMENT FORM</div>
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
                <span class="rfl-strength-line-label">CLASSIFICATION:</span>
                <span class="rfl-strength-line-fill">{{ strtoupper($printClassification !== '' ? $printClassification : 'N/A') }}</span>
            </div>
            <div class="rfl-strength-line-row">
                <span class="rfl-strength-line-label">Generated:</span>
                <span class="rfl-strength-line-fill">{{ $printGeneratedAt->format('m/d/Y h:i A') }}</span>
            </div>
        </div>
    </div>

    <div class="student-table-wrapper rfl-strength-table-wrap">
        <table class="student-table rfl-strength-table rfl-assignment-table">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Description</th>
                    <th>LEC</th>
                    <th>LAB</th>
                    <th>Units</th>
                    <th>Credited Units</th>
                    <th>Section</th>
                    <th>Schedule</th>
                    <th>Type</th>
                    <th>Added By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($printRows as $row)
                    <tr>
                        <td>{{ strtoupper($row['code']) }}</td>
                        <td>{{ strtoupper($row['subject']) }}</td>
                        <td>{{ (int) $row['lec'] }}</td>
                        <td>{{ (int) $row['lab'] }}</td>
                        <td>{{ number_format((float) $row['units'], 1) }}</td>
                        <td>{{ is_null($row['credited_tuition_units']) ? '-' : number_format((float) $row['credited_tuition_units'], 2) }}</td>
                        <td>{{ $row['section'] !== '' ? $row['section'] : '-' }}</td>
                        <td>{{ trim($row['days'] . ' ' . $row['time'] . ($row['room'] !== '' ? ' / ' . $row['room'] : '')) }}</td>
                        <td>{{ strtoupper($row['type']) }}</td>
                        <td>{{ strtoupper($row['added_by']) }}</td>
                    </tr>
                @endforeach

                @for($i = 0; $i < $blankRows; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="rfl-strength-total-label">TOTAL</td>
                    <td>{{ (int) ($printTotals['lec'] ?? 0) }}</td>
                    <td>{{ (int) ($printTotals['lab'] ?? 0) }}</td>
                    <td>{{ number_format((float) ($printTotals['units'] ?? 0), 1) }}</td>
                    <td>{{ number_format((float) ($printTotals['credited_tuition_units'] ?? 0), 2) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="rfl-strength-signatures">
        <div class="rfl-strength-sign-block">
            <div class="rfl-strength-sign-label">Prepared By:</div>
            <div class="rfl-strength-sign-line"></div>
            <div class="rfl-strength-sign-role">REGISTRAR STAFF</div>
        </div>
        <div class="rfl-strength-sign-block">
            <div class="rfl-strength-sign-label">Approved By:</div>
            <div class="rfl-strength-sign-line"></div>
            <div class="rfl-strength-sign-role">REGISTRAR</div>
        </div>
        <div class="rfl-strength-sign-block">
            <div class="rfl-strength-sign-label">Noted By:</div>
            <div class="rfl-strength-sign-line"></div>
            <div class="rfl-strength-sign-role">DEAN</div>
        </div>
    </div>
</div>
