@extends('layouts.registrar')

@section('title', 'PLP - Batch Upload Image')
@section('page-title', 'BATCH UPLOAD IMAGE')

@section('content')
<div class="student-page-container">
    <div class="batch-upload-wrapper">
        @if (session('batchUploadReport'))
            @php
                $report = session('batchUploadReport');
                $uploadedCount = count($report['uploaded']);
                $skippedCount = count($report['skipped']);
            @endphp
            <div class="alert {{ $uploadedCount > 0 ? 'alert-success' : 'alert-warning' }} batch-upload-alert" role="alert">
                <strong>Upload Summary:</strong>
                {{ $uploadedCount }} uploaded, {{ $skippedCount }} skipped ({{ $report['total'] }} file(s) processed).

                @if ($skippedCount > 0)
                    <ul class="batch-upload-report-list">
                        @foreach ($report['skipped'] as $skip)
                            <li>{{ $skip['filename'] }} - {{ $skip['reason'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger batch-upload-alert" role="alert">
                <strong>Upload failed.</strong>
                <ul class="batch-upload-report-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="batchUploadForm" action="{{ route('registrar.process.batch-upload.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="batch-dropzone" id="batchDropzone">
                <p class="dropzone-text">Drop JPG files here or click to upload</p>
                <input type="file" id="batchFileInput" name="images[]" accept=".jpg,.jpeg,image/jpeg,image/jpg" multiple hidden>
            </div>

            <div class="batch-upload-actions">
                <p class="batch-file-count" id="batchFileCount">No files selected.</p>
                <button type="submit" id="batchUploadButton" class="batch-upload-btn" disabled>Upload Selected Files</button>
            </div>
        </form>

        {{-- Note --}}
        <div class="batch-upload-note">
            <p><strong>Note:</strong></p>
            <p>Upload image files (jpg/jpeg), each with a maximum size of 1MB.</p>
            <p>The filename must be the Student Number (example: 2023A0001.jpg).</p>
        </div>

        <div class="batch-upload-results">
            <h5 class="batch-upload-results-title">Uploaded Images</h5>
            <div class="student-table-wrapper table-responsive">
                <table class="student-table registrar-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Number</th>
                            <th>Student Name</th>
                            <th>Original Filename</th>
                            <th>Size</th>
                            <th>Uploaded At</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentUploads as $upload)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $upload['student_no'] }}</td>
                                <td>{{ $upload['student_name'] }}</td>
                                <td>{{ $upload['original_filename'] }}</td>
                                <td>{{ number_format((float) $upload['size_kb'], 2) }} KB</td>
                                <td>{{ $upload['uploaded_at'] }}</td>
                                <td>
                                    @if (!empty($upload['image_url']))
                                        <button
                                            type="button"
                                            class="btn btn-link p-0 batch-preview-btn"
                                            data-batch-preview="1"
                                            data-image-url="{{ $upload['image_url'] }}"
                                            data-student-no="{{ $upload['student_no'] }}"
                                        >
                                            View
                                        </button>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No uploaded images yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="batchPreviewModal" tabindex="-1" aria-labelledby="batchPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchPreviewModalLabel">Student Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-2"><strong>Student ID:</strong> <span id="batchPreviewStudentNo">-</span></p>
                <img id="batchPreviewImage" src="" alt="Student preview" class="img-fluid rounded border" style="max-height: 65vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/batch-upload.js') }}?v={{ file_exists(public_path('js/batch-upload.js')) ? filemtime(public_path('js/batch-upload.js')) : time() }}"></script>
@endpush
