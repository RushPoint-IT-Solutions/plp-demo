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
<script src="{{ asset('js/batch-upload.js') }}"></script>
@endpush
