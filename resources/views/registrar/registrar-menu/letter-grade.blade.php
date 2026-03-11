@extends('layouts.registrar')

@section('title', 'PLP - Letter Grade Setup')
@section('page-title', 'LETTER GRADE SETUP')

@section('content')
<div class="pf-page">

    {{-- Toolbar --}}
    <div class="pf-toolbar">
        <div class="pf-search-wrap">
            <svg class="pf-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="pf-search-input" placeholder="Search grades..." id="lgSearch">
        </div>
        <button type="button" class="pf-btn-new">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Grade
        </button>
    </div>

    {{-- Table --}}
    <div class="pf-table-wrap">
        <table class="pf-table" id="lgTable">
            <thead>
                <tr>
                    <th>Letter Grade</th>
                    <th>Grade Point</th>
                    <th>Percentage Range</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="text-align:center;color:#999;padding:32px;">No letter grades configured yet.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
