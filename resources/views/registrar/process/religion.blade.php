@extends('layouts.registrar')

@section('title', 'PLP - Religion')
@section('page-title', 'RELIGION')

@section('content')
<div class="student-page-container">

    {{-- Code/Description Input Row + Add Button --}}
    <div class="registrar-form-row">
        <div class="registrar-form-group" style="flex: 2;">
            <label class="registrar-form-label">DESCRIPTION</label>
            <input type="text" class="registrar-form-input" placeholder="Description" name="description">
        </div>
        <div class="registrar-form-group registrar-form-btn-group">
            <button type="button" class="btn-registrar-save" onclick="saveReligion()">+Add</button>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 50px; text-align: center;"><input type="checkbox"></td>
                    <td style="text-align: left;">Roman Catholic</td>
                </tr>
                <tr>
                    <td style="width: 50px; text-align: center;"><input type="checkbox"></td>
                    <td style="text-align: left;">Iglesia Ni Cristo</td>
                </tr>
                <tr>
                    <td style="width: 50px; text-align: center;"><input type="checkbox"></td>
                    <td style="text-align: left;">Islam</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
@push('scripts')
<script>
function saveReligion() {
    var desc = document.querySelector('input[name="description"]').value.trim();
    if (!desc) {
        showRegistrarToast('Please fill in the description.', 'warning');
        return;
    }
    showRegistrarToast('Religion saved successfully.', 'success');
}
</script>
@endpush
@endsection
