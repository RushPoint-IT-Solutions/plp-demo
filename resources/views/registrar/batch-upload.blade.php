@extends('layouts.registrar')

@section('title', 'PLP - Batch Upload Image')
@section('page-title', 'BATCH UPLOAD IMAGE')

@section('content')
<div class="student-page-container">
    <div class="batch-upload-wrapper">
        {{-- Drop Zone --}}
        <div class="batch-dropzone" id="batchDropzone">
            <p class="dropzone-text">Drop files here to Upload</p>
            <input type="file" id="batchFileInput" accept="image/jpeg,image/jpg" multiple hidden>
        </div>

        {{-- Note --}}
        <div class="batch-upload-note">
            <p><strong>Note:</strong></p>
            <p>Upload image files(jpg), each having maximum size of 1MB.</p>
            <p>The filename of the image should be the Student Number.</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const dropzone = document.getElementById('batchDropzone');
    const fileInput = document.getElementById('batchFileInput');

    // Click to open file picker
    dropzone.addEventListener('click', () => fileInput.click());

    // Drag events
    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        // Demo only — no actual upload
        const files = e.dataTransfer.files;
        if (files.length) {
            alert('Selected ' + files.length + ' file(s). (Demo only)');
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            alert('Selected ' + fileInput.files.length + ' file(s). (Demo only)');
        }
    });
</script>
@endpush
