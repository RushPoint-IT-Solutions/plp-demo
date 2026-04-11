(function () {
    var dropzone = document.getElementById('batchDropzone');
    var fileInput = document.getElementById('batchFileInput');
    var fileCount = document.getElementById('batchFileCount');
    var uploadButton = document.getElementById('batchUploadButton');
    var previewModalEl = document.getElementById('batchPreviewModal');
    var previewImage = document.getElementById('batchPreviewImage');
    var previewStudentNo = document.getElementById('batchPreviewStudentNo');

    if (!dropzone || !fileInput) {
        return;
    }

    function syncSelectionState() {
        var total = fileInput.files ? fileInput.files.length : 0;

        if (fileCount) {
            if (total > 0) {
                fileCount.textContent = total + ' file(s) selected.';
            } else {
                fileCount.textContent = 'No files selected.';
            }
        }

        if (uploadButton) {
            uploadButton.disabled = total === 0;
        }
    }

    function setFiles(files) {
        if (!files || !files.length) {
            return;
        }

        var transfer = new DataTransfer();
        var index;

        for (index = 0; index < files.length; index += 1) {
            transfer.items.add(files[index]);
        }

        fileInput.files = transfer.files;
        syncSelectionState();
    }

    dropzone.addEventListener('click', function () {
        fileInput.click();
    });

    dropzone.addEventListener('dragover', function (event) {
        event.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        dropzone.classList.remove('dragover');
        setFiles(event.dataTransfer.files);
    });

    fileInput.addEventListener('change', syncSelectionState);

    if (previewModalEl && window.bootstrap && previewImage) {
        var previewButtons = document.querySelectorAll('[data-batch-preview]');
        var previewModal = new window.bootstrap.Modal(previewModalEl);

        previewButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var imageUrl = button.getAttribute('data-image-url') || '';
                var studentNo = button.getAttribute('data-student-no') || '-';

                previewImage.setAttribute('src', imageUrl);
                previewImage.setAttribute('alt', 'Student image ' + studentNo);

                if (previewStudentNo) {
                    previewStudentNo.textContent = studentNo;
                }

                previewModal.show();
            });
        });

        previewModalEl.addEventListener('hidden.bs.modal', function () {
            previewImage.setAttribute('src', '');

            if (previewStudentNo) {
                previewStudentNo.textContent = '-';
            }
        });
    }

    syncSelectionState();
})();
