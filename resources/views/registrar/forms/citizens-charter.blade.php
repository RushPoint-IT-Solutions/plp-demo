@extends('layouts.registrar')

@section('title', "PLP - Citizen's Charter")
@section('page-title', "CITIZEN'S CHARTER")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/citizens-charter.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="cc-page" id="citizens-charter-page">
    <nav class="cc-pagination-wrap d-print-none" aria-label="Citizen's Charter page navigation">
        <button type="button" class="cc-pagination-arrow btn btn-light" id="cc-prev" aria-label="Previous page">
            <span aria-hidden="true">&lsaquo;</span>
        </button>

        <ul class="pagination pagination-sm cc-pagination mb-0" id="cc-page-numbers"></ul>

        <button type="button" class="cc-pagination-arrow btn btn-light" id="cc-next" aria-label="Next page">
            <span aria-hidden="true">&rsaquo;</span>
        </button>
    </nav>

    <div class="cc-stage" id="cc-stage">
        @php
            $cc_render = function($s) {
                $text = str_replace('\\n', "\n", (string) $s);
                $escaped = e($text);
                $unescaped = str_replace([
                    '&lt;b&gt;', '&lt;/b&gt;', '&lt;strong&gt;', '&lt;/strong&gt;'
                ], [
                    '<b>', '</b>', '<strong>', '</strong>'
                ], $escaped);
                return nl2br($unescaped);
            };
        @endphp
        <article class="cc-sheet cc-cover-page" data-page-index="1">
            <div class="cc-cover-body">
                <img src="{{ asset('img/plplogoo.png') }}?v=1999" alt="Pamantasan ng Lungsod ng Pasig 1999 logo" class="cc-cover-logo">

                <h1 class="cc-cover-school">{{ $coverData['institution'] }}</h1>
                <p class="cc-cover-school-sub">{{ $coverData['institution_sub'] }}</p>

                <h2 class="cc-cover-title">{{ $coverData['document_title'] }}</h2>
                <p class="cc-cover-edition">{{ $coverData['edition'] }}</p>

                <h3 class="cc-cover-office">{{ $coverData['office'] }}</h3>
            </div>
        </article>

        @foreach($charterPages as $pageIndex => $page)
            @if($page['type'] === 'transaction')
                <article class="cc-sheet {{ (isset($page['title']) && (strtoupper($page['title']) === 'CHANGE OF PERSONAL DATA' || strtoupper($page['title']) === 'REQUEST FOR STUDENT RECORDS')) ? 'cc-person-middle' : '' }}" data-page-index="{{ $pageIndex + 2 }}">
                    <header class="cc-doc-header">
                        <h2>CITIZEN'S CHARTER</h2>
                        <p>PLP REGISTRAR'S OFFICE</p>
                    </header>

                    <section class="cc-section">
                        <h3 class="cc-transaction-title">{{ $page['title'] }}</h3>
                        <p class="cc-lead">{!! $cc_render(isset($page['lead']) ? $page['lead'] : '') !!}</p>
                    </section>

                    <table class="cc-meta-table">
                        <tbody>
                            @foreach($page['meta'] as $label => $value)
                                <tr>
                                    <th>{{ $label }}:</th>
                                    <td>{!! $cc_render($value) !!}</td>
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
                                <tr @if(empty($row['no'])) class="cc-sub-row" @endif>
                                    <td class="cc-text-center">{{ $row['no'] }}</td>
                                    <td>{!! $cc_render(isset($row['requirement']) ? $row['requirement'] : '') !!}</td>
                                    <td>{!! $cc_render(isset($row['where']) ? $row['where'] : '') !!}</td>
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
                            @php
                                $mergePersonRows = (isset($page['title']) && in_array(strtoupper($page['title']), [
                                    'CHANGE OF PERSONAL DATA',
                                    'REQUEST FOR STUDENT RECORDS',
                                ], true));
                                $skipPersonCells = 0;
                            @endphp

                            @foreach($page['steps'] as $stepIndex => $row)
                                <tr>
                                    <td class="cc-text-center">{{ $row['no'] }}</td>
                                    <td>{!! $cc_render(isset($row['client']) ? $row['client'] : '') !!}</td>
                                    <td>{!! $cc_render(isset($row['office']) ? $row['office'] : '') !!}</td>
                                    <td class="cc-text-center">{!! $cc_render(isset($row['fees']) ? $row['fees'] : '') !!}</td>
                                    <td class="cc-text-center">{!! $cc_render(isset($row['time']) ? $row['time'] : '') !!}</td>

                                    @if($mergePersonRows)
                                        @php
                                            $currentPerson = trim((string) (isset($row['person']) ? $row['person'] : ''));
                                        @endphp

                                        @if($skipPersonCells > 0)
                                            @php $skipPersonCells--; @endphp
                                        @elseif($currentPerson !== '')
                                            @php
                                                $personRowSpan = 1;
                                                $stepCount = count($page['steps']);
                                                for ($i = $stepIndex + 1; $i < $stepCount; $i++) {
                                                    $nextPerson = trim((string) (isset($page['steps'][$i]['person']) ? $page['steps'][$i]['person'] : ''));
                                                    if ($nextPerson !== '') {
                                                        break;
                                                    }
                                                    $personRowSpan++;
                                                }
                                                $skipPersonCells = $personRowSpan - 1;
                                            @endphp
                                            <td @if($personRowSpan > 1) rowspan="{{ $personRowSpan }}" @endif>{!! $cc_render(isset($row['person']) ? $row['person'] : '') !!}</td>
                                        @else
                                            <td></td>
                                        @endif
                                    @else
                                        <td>{!! $cc_render(isset($row['person']) ? $row['person'] : '') !!}</td>
                                    @endif
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
                        <h3 class="cc-transaction-title">{{ isset($page['heading']) ? $page['heading'] : $page['title'] }}</h3>
                    </section>

                    <table class="cc-grid-table cc-grid-table--feedback">
                        <tbody>
                            <tr>
                                <th colspan="2" class="cc-feedback-head">{{ $page['title'] }}</th>
                            </tr>
                            @foreach($page['rows'] as $row)
                                <tr>
                                    <td class="cc-col-feedback-label cc-feedback-label">{{ $row['label'] }}</td>
                                    <td class="cc-feedback-value">{!! $cc_render(isset($row['value']) ? $row['value'] : '') !!}</td>
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
