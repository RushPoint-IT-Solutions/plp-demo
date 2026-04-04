@extends('layouts.registrar')

@section('title', 'PLP - Faculty Loads')
@section('page-title', 'FACULTY LOADS')
@section('body-class', 'page-services-faculty-loads')

@push('scripts')
    <script src="{{ asset('js/registrar-faculty-loads.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="rfl-wrap">
    <div class="rfl-topbar">
        <div class="rfl-search">
            <div class="app-filter-label">Search</div>
            <form method="GET" action="{{ route('registrar.services.classroom-faculty.faculty-loads.index') }}" class="rfl-search-form">
                <input type="text" name="q" class="form-control rfl-search-input" placeholder="Search Faculty Code / Name" value="{{ $search }}">
                <button class="rfl-search-btn" type="submit" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>

        <div class="rfl-meta">
            {{ $faculties->firstItem() ?? 0 }} - {{ $faculties->lastItem() ?? 0 }} of {{ $faculties->total() }}
        </div>
    </div>

    <div class="app-table-wrap rfl-table-wrap">
        <table class="app-table rfl-table" id="rflFacultyTable" data-no-auto-pager="1">
            <thead>
                <tr>
                    <th style="width:70px">#</th>
                    <th>Code</th>
                    <th>Faculty Name</th>
                </tr>
            </thead>
            <tbody>
                @forelse($faculties as $i => $faculty)
                    <tr class="rfl-row" data-href="{{ route('registrar.services.classroom-faculty.faculty-loads.show', $faculty->id) }}">
                        <td>{{ ($faculties->firstItem() ?? 0) + $i }}</td>
                        <td class="td-code">{{ $faculty->code }}</td>
                        <td>
                            <a class="rfl-name-link" href="{{ route('registrar.services.classroom-faculty.faculty-loads.show', $faculty->id) }}">
                                {{ $faculty->name }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No faculty found.</td>
                    </tr>
                @endforelse
            <tr class="rfl-list-total-row">
                <td colspan="3" class="rfl-list-total-cell" style="color:#006837 !important;background:#f8fcf9 !important;text-align:left !important;font-weight:700 !important;">Total Faculty: <strong>{{ $faculties->total() }}</strong></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="rfl-footer" style="display:flex;justify-content:flex-end;">
    <div class="rfl-pagination">{{ $faculties->links() }}</div>
</div>
</div>
@endsection
