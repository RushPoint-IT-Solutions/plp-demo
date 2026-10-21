@if(\Illuminate\Support\Facades\Schema::hasTable('student_master_sources'))
    @php($masterSource = $student->masterSource)
    @if($masterSource)
        <details class="card m-3 p-3">
            <summary style="cursor:pointer;font-weight:600;">StudentMaster source details &middot;
                {{ $masterSource->is_active ? 'Active' : 'Inactive' }} in source</summary>
            <p class="text-muted mt-2">Original row {{ $masterSource->source_row }}.
                Location and student-type codes are retained as supplied. Full family names and additional contacts
                are shown here without guessing how to split names or translate codes.</p>
            <div class="table-responsive"><table class="table table-sm table-bordered">
                <thead><tr><th>Column</th><th>Source field</th><th>Original value</th></tr></thead>
                <tbody>@foreach($masterSource->source_data as $field)
                    <tr><td>{{ $field['column'] }}</td><th scope="row">{{ $field['heading'] }}</th>
                        <td style="white-space:pre-wrap;overflow-wrap:anywhere;">{{ $field['value'] }}</td></tr>
                @endforeach</tbody>
            </table></div>
        </details>
    @endif
@endif
