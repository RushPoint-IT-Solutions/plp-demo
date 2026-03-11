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
