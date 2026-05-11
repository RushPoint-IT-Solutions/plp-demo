(function () {
    var dropzone = document.getElementById('batchDropzone');
    var fileInput = document.getElementById('batchFileInput');
    var fileCount = document.getElementById('batchFileCount');
    var uploadButton = document.getElementById('batchUploadButton');
    var previewModalEl = document.getElementById('batchPreviewModal');
    var previewImage = document.getElementById('batchPreviewImage');
    var previewStudentNo = document.getElementById('batchPreviewStudentNo');
    var previewGrid = document.getElementById('batchPreviewGrid');
    var previewGridItems = document.getElementById('batchPreviewGridItems');

    if (!dropzone || !fileInput) {
        return;
    }

    function renderPreviews(files) {
        if (!previewGrid || !previewGridItems) return;
        previewGridItems.innerHTML = '';

        if (!files || files.length === 0) {
            previewGrid.style.display = 'none';
            return;
        }

        previewGrid.style.display = 'block';

        for (var i = 0; i < files.length; i++) {
            (function (file) {
                var wrapper = document.createElement('div');
                wrapper.style.cssText = 'width:100px; text-align:center; border:1px solid #e2e8f0; border-radius:8px; padding:6px; background:#f8fafc; position:relative;';

                var img = document.createElement('img');
                img.style.cssText = 'width:80px; height:80px; object-fit:cover; border-radius:4px; display:block; margin:0 auto 6px;';
                img.alt = file.name;

                var reader = new FileReader();
                reader.onload = function (e) { img.src = e.target.result; };
                reader.readAsDataURL(file);

                var nameEl = document.createElement('p');
                var baseName = file.name.replace(/\.[^.]+$/, '');
                nameEl.textContent = baseName.length > 12 ? baseName.slice(0, 12) + '…' : baseName;
                nameEl.title = file.name;
                nameEl.style.cssText = 'font-size:0.7rem; font-weight:700; color:#334155; margin:0; word-break:break-all;';

                wrapper.appendChild(img);
                wrapper.appendChild(nameEl);
                previewGridItems.appendChild(wrapper);
            })(files[i]);
        }
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

        renderPreviews(fileInput.files);
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
