@extends('layouts.registrar')

@section('title', "PLP - Citizen's Charter")
@section('page-title', "CITIZEN'S CHARTER")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/citizens-charter.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="cc-page" id="citizens-charter-page">
    <div class="cc-pagination d-print-none" aria-label="Citizen's Charter page navigation">
        <button type="button" class="cc-page-btn cc-page-btn--nav" id="cc-prev" aria-label="Previous page">Prev</button>
        <div class="cc-page-numbers" id="cc-page-numbers"></div>
        <button type="button" class="cc-page-btn cc-page-btn--nav" id="cc-next" aria-label="Next page">Next</button>
        <span class="cc-page-indicator" id="cc-page-indicator" aria-live="polite"></span>
    </div>

    <div class="cc-stage" id="cc-stage">
        <article class="cc-sheet cc-cover-page" data-page-index="1">
            <div class="cc-cover-body">
                <img src="{{ asset('img/logo.svg') }}" alt="PLP Logo" class="cc-cover-logo">

                <h1 class="cc-cover-school">{{ $coverData['institution'] }}</h1>
                <p class="cc-cover-school-sub">{{ $coverData['institution_sub'] }}</p>

                <h2 class="cc-cover-title">{{ $coverData['document_title'] }}</h2>
                <p class="cc-cover-edition">{{ $coverData['edition'] }}</p>

                <h3 class="cc-cover-office">{{ $coverData['office'] }}</h3>
            </div>
        </article>

        @foreach($charterPages as $pageIndex => $page)
            @if($page['type'] === 'transaction')
                <article class="cc-sheet" data-page-index="{{ $pageIndex + 2 }}">
                    <header class="cc-doc-header">
                        <h2>CITIZEN'S CHARTER</h2>
                        <p>PLP REGISTRAR'S OFFICE</p>
                    </header>

                    <section class="cc-section">
                        <h3 class="cc-transaction-title">{{ $page['title'] }}</h3>
                        <p class="cc-lead">{{ $page['lead'] }}</p>
                    </section>

                    <table class="cc-meta-table">
                        <tbody>
                            @foreach($page['meta'] as $label => $value)
                                <tr>
                                    <th>{{ $label }}:</th>
                                    <td>{{ $value }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="cc-grid-table cc-grid-table--checklist">
                        <thead>
                            <tr>
                                <th class="cc-col-no">#</th>
                                <th>CHECKLIST OF REQUIREMENTS</th>
                                <th>WHERE TO SECURE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($page['checklist'] as $row)
                                <tr>
                                    <td class="cc-text-center">{{ $row['no'] }}</td>
                                    <td>{!! nl2br(e($row['requirement'])) !!}</td>
                                    <td>{!! nl2br(e(isset($row['where']) ? $row['where'] : '')) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <table class="cc-grid-table cc-grid-table--steps">
                        <thead>
                            <tr>
                                <th class="cc-col-no">#</th>
                                <th>CLIENT STEPS</th>
                                <th>OFFICE ACTIONS</th>
                                <th class="cc-col-fees">FEES TO BE PAID</th>
                                <th class="cc-col-time">PROCESSING TIME</th>
                                <th class="cc-col-person">PERSON RESPONSIBLE</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($page['steps'] as $row)
                                <tr>
                                    <td class="cc-text-center">{{ $row['no'] }}</td>
                                    <td>{!! nl2br(e($row['client'])) !!}</td>
                                    <td>{!! nl2br(e($row['office'])) !!}</td>
                                    <td class="cc-text-center">{!! nl2br(e($row['fees'])) !!}</td>
                                    <td class="cc-text-center">{!! nl2br(e($row['time'])) !!}</td>
                                    <td>{!! nl2br(e($row['person'])) !!}</td>
                                </tr>
                            @endforeach
                            <tr class="cc-total-row">
                                <td colspan="3" class="cc-text-right">TOTAL:</td>
                                <td class="cc-text-center">{{ $page['totals']['fees'] }}</td>
                                <td class="cc-text-center">{{ $page['totals']['time'] }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </article>
            @elseif($page['type'] === 'feedback')
                <article class="cc-sheet" data-page-index="{{ $pageIndex + 2 }}">
                    <header class="cc-doc-header">
                        <h2>CITIZEN'S CHARTER</h2>
                        <p>PLP REGISTRAR'S OFFICE</p>
                    </header>

                    <section class="cc-section">
                        <h3 class="cc-transaction-title">{{ $page['title'] }}</h3>
                    </section>

                    <table class="cc-grid-table cc-grid-table--feedback">
                        <thead>
                            <tr>
                                <th class="cc-col-feedback-label">TOPIC</th>
                                <th>DETAILS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($page['rows'] as $row)
                                <tr>
                                    <td class="cc-feedback-label">{{ $row['label'] }}</td>
                                    <td>{!! nl2br(e($row['value'])) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </article>
            @endif
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/registrar-citizens-charter.js') }}?v={{ time() }}"></script>
@endpush
