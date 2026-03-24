@extends('layouts.registrar')

@section('title', 'PLP - Forms')
@section('page-title', 'FORMS')

@section('content')
<div class="pf-page">
    <div class="rep-dashboard">
        <div class="rep-top-row">
            <div class="rep-sys-card rep-sys-card--compact">
                <div class="rep-sys-title">{{ $formTitle }}</div>
                <p style="font-size:0.82rem; color:#4b5563; margin: 6px 0 0;">
                    Upload files to <code>public/forms/{{ $category }}</code> so they appear in this list.
                </p>
            </div>
        </div>

        <div class="rep-group-card" style="margin-top: 10px;">
            <div class="rep-group-title" style="color: #1e3a5f; font-size: 1.2rem;">Available Files</div>

            @if($forms->isEmpty())
                <div class="app-table-wrap" style="margin-top:10px;">
                    <table class="app-table">
                        <tbody>
                            <tr>
                                <td style="text-align:center; color:#6b7280;">No files available yet for {{ $formTitle }}.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="app-table-wrap" style="margin-top:10px;">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th style="text-align:center; width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forms as $form)
                                <tr>
                                    <td>{{ $form }}</td>
                                    <td style="text-align:center;">
                                        <button
                                            type="button"
                                            class="req-btn-save"
                                            style="height:32px; min-width: 84px;"
                                            onclick="openRegistrarFormPreview('{{ asset('forms/' . $category . '/' . $form) }}', '{{ addslashes($form) }}')"
                                        >
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="req-modal-overlay" id="registrarFormPreviewModal" style="display:none;" onclick="if(event.target===this) closeRegistrarFormPreview()">
    <div class="req-modal-box" style="width:min(1000px, 95vw); max-height:90vh;">
        <h3 class="req-modal-title" id="registrarFormPreviewTitle">FORM PREVIEW</h3>
        <div style="height:70vh; border:1px solid #d1d5db; border-radius:8px; overflow:hidden; background:#fff;">
            <iframe
                id="registrarFormPreviewFrame"
                title="Form Preview"
                src=""
                style="width:100%; height:100%; border:0;"
            ></iframe>
        </div>
        <div class="req-modal-actions" style="margin-top:14px; justify-content:flex-end;">
            <button type="button" class="req-btn-cancel" onclick="closeRegistrarFormPreview()">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openRegistrarFormPreview(url, fileName) {
        var modal = document.getElementById('registrarFormPreviewModal');
        var frame = document.getElementById('registrarFormPreviewFrame');
        var title = document.getElementById('registrarFormPreviewTitle');

        if (!modal || !frame || !title) return;

        title.textContent = String(fileName || 'Form Preview').toUpperCase();
        frame.src = url || '';
        modal.style.display = 'flex';
    }

    function closeRegistrarFormPreview() {
        var modal = document.getElementById('registrarFormPreviewModal');
        var frame = document.getElementById('registrarFormPreviewFrame');

        if (frame) frame.src = '';
        if (modal) modal.style.display = 'none';
    }
</script>
@endpush
