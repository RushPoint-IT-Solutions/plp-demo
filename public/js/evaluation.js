/* ── Evaluation Page ── */

var EVAL_CURRENT_STEP = 'define';
var EVAL_DEFINE_DONE = false;
var EVAL_BUILD_DONE = false;
var EVAL_SAVED = false;
var EVAL_PREVIEW_DONE = false;

/* ── Sample blocks for the builder ── */
var EVAL_BLOCKS = [
    {
        id: 1,
        letter: 'A',
        title: 'Commitment',
        questions: [
            { id: 1, text: 'The instructor starts and ends classes on time.' }
        ]
    },
    {
        id: 2,
        letter: 'B',
        title: 'Knowledge of Subject Matter',
        questions: [
            { id: 1, text: 'The instructor demonstrates mastery of the topic.' }
        ]
    }
];
var EVAL_NEXT_BLOCK_ID = 3;

/* ── Library / Dashboard data ── */
var EVAL_LIBRARY = [
    { id: 1, name: 'SAM 125', status: 'Published', responses: 10 },
    { id: 2, name: 'OOP 113', status: 'Draft', responses: 0 },
    { id: 3, name: 'SPI 128', status: 'Closed', responses: 50 },
    { id: 4, name: 'UTS 12', status: 'Closed', responses: 50 }
];

/* ════════════════════════════════════
   STEP NAVIGATION
   ════════════════════════════════════ */
function goToStep(step) {
    /* Validate define before allowing Build */
    if (step === 'build' && EVAL_CURRENT_STEP === 'define') {
        var subj = document.getElementById('evalSubject').value;
        var fac = document.getElementById('evalFaculty').value;
        if (!subj || !fac) {
            showRegistrarToast('Please fill in Subject and Faculty.', 'warning');
            return;
        }
        EVAL_DEFINE_DONE = true;
        updateBuildInfo();
    }

    if (step === 'preview' && EVAL_CURRENT_STEP === 'build') {
        if (EVAL_BLOCKS.length === 0) {
            showRegistrarToast('Please add at least one block.', 'warning');
            return;
        }
        EVAL_BUILD_DONE = true;
        renderPreview();
    }

    EVAL_CURRENT_STEP = step;

    /* Hide all panels */
    document.getElementById('stepDefine').style.display = 'none';
    document.getElementById('stepBuild').style.display = 'none';
    document.getElementById('stepPreview').style.display = 'none';

    /* Show target */
    if (step === 'define') document.getElementById('stepDefine').style.display = 'block';
    if (step === 'build') document.getElementById('stepBuild').style.display = 'block';
    if (step === 'preview') document.getElementById('stepPreview').style.display = 'block';

    /* Update tabs */
    var tabs = ['stepDefineTab', 'stepBuildTab', 'stepPreviewTab'];
    tabs.forEach(function(t) {
        document.getElementById(t).classList.remove('active');
        document.getElementById(t).classList.remove('completed');
    });

    if (step === 'define') document.getElementById('stepDefineTab').classList.add('active');
    if (step === 'build') {
        document.getElementById('stepBuildTab').classList.add('active');
        if (EVAL_DEFINE_DONE) document.getElementById('stepDefineTab').classList.add('completed');
    }
    if (step === 'preview') {
        document.getElementById('stepPreviewTab').classList.add('active');
        if (EVAL_DEFINE_DONE) document.getElementById('stepDefineTab').classList.add('completed');
        if (EVAL_BUILD_DONE) document.getElementById('stepBuildTab').classList.add('completed');
    }

    /* Show checkmarks on completed steps */
    updateStepCheckmarks();
    /* Preview gets checkmark only when on that step or after save/publish */
    var prevCheck = document.querySelector('#stepPreviewTab .eval-step-check');
    if (step === 'preview' && !EVAL_PREVIEW_DONE) prevCheck.style.display = 'none';
}

function updateStepCheckmarks() {
    var defCheck = document.querySelector('#stepDefineTab .eval-step-check');
    var bldCheck = document.querySelector('#stepBuildTab .eval-step-check');
    var prevCheck = document.querySelector('#stepPreviewTab .eval-step-check');
    defCheck.style.display = EVAL_DEFINE_DONE ? 'inline' : 'none';
    bldCheck.style.display = EVAL_BUILD_DONE ? 'inline' : 'none';
    prevCheck.style.display = EVAL_PREVIEW_DONE ? 'inline' : 'none';
    if (EVAL_PREVIEW_DONE) document.getElementById('stepPreviewTab').classList.add('completed');
}

/* ════════════════════════════════════
   BUILD STEP
   ════════════════════════════════════ */
function updateBuildInfo() {
    var prog = document.getElementById('evalProgram');
    var subj = document.getElementById('evalSubject');
    var progText = prog.options[prog.selectedIndex] ? prog.options[prog.selectedIndex].value : '';
    var subjText = subj.options[subj.selectedIndex] ? subj.options[subj.selectedIndex].text : '';
    document.getElementById('evalBuildInfo').textContent = (progText ? progText + ' 4-A' : '') + ' | ' + subjText;
    renderBlocks();
}

function renderBlocks() {
    var container = document.getElementById('evalBlocksContainer');
    var html = '';
    for (var i = 0; i < EVAL_BLOCKS.length; i++) {
        var b = EVAL_BLOCKS[i];
        html += '<div class="eval-block" data-id="' + b.id + '">';
        html += '<div class="eval-block-header">';
        html += '<span class="eval-block-title" ondblclick="editBlockTitle(' + b.id + ', this)">' + b.letter + '. ' + b.title + '</span>';
        html += '<button type="button" class="eval-q-icon" title="Edit Block Title" onclick="editBlockTitle(' + b.id + ', this.previousElementSibling)">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>';
        html += '</div>';

        for (var q = 0; q < b.questions.length; q++) {
            var quest = b.questions[q];
            html += '<div class="eval-question" data-block="' + b.id + '" data-q="' + quest.id + '">';
            html += '<span class="eval-q-num">' + (q + 1) + '.</span>';
            html += '<span class="eval-q-text" ondblclick="editQuestion(' + b.id + ',' + quest.id + ', this)">' + quest.text + '</span>';
            html += '<div class="eval-q-actions">';
            html += '<button type="button" class="eval-q-icon" title="Edit" onclick="editQuestion(' + b.id + ',' + quest.id + ', this.parentElement.previousElementSibling)">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>';
            html += '<button type="button" class="eval-q-icon" title="Delete" onclick="deleteQuestion(' + b.id + ',' + quest.id + ')">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg></button>';
            html += '<button type="button" class="eval-q-icon" title="Add Question" onclick="addQuestion(' + b.id + ')">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>';
            html += '</div>';
            html += '</div>';

            /* Likert scale row */
            html += '<div class="eval-likert" data-block="' + b.id + '" data-q="' + quest.id + '">';
            var labels = ['Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree'];
            for (var l = 5; l >= 1; l--) {
                html += '<div class="eval-likert-item" data-value="' + l + '" onclick="selectLikert(this)">';
                html += '<span class="eval-likert-num">' + l + '</span>';
                html += '<span class="eval-likert-label">' + labels[5 - l] + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }
        html += '</div>';
    }
    container.innerHTML = html;
}

/* ── Block & Question CRUD (inline) ── */
var _crudBlockId = null;   /* block being acted on */
var _crudQuestionId = null; /* question being acted on */

/* ── Likert click selection ── */
function selectLikert(el) {
    var row = el.parentElement;
    var items = row.querySelectorAll('.eval-likert-item');
    items.forEach(function(item) { item.classList.remove('selected'); });
    el.classList.add('selected');
}

/* ─── Add block (auto-add + inline edit title) ─── */
function addNewBlock() {
    var letter = String.fromCharCode(65 + EVAL_BLOCKS.length);
    var newBlock = {
        id: EVAL_NEXT_BLOCK_ID++,
        letter: letter,
        title: 'New Block',
        questions: [{ id: 1, text: 'New question — click edit to change.' }]
    };
    EVAL_BLOCKS.push(newBlock);
    renderBlocks();
    /* Auto-trigger inline edit on the new block title */
    var blockEl = document.querySelector('.eval-block[data-id="' + newBlock.id + '"]');
    if (blockEl) {
        var titleSpan = blockEl.querySelector('.eval-block-title');
        if (titleSpan) editBlockTitle(newBlock.id, titleSpan);
    }
}

/* ─── Edit block title inline ─── */
function editBlockTitle(blockId, spanEl) {
    if (spanEl.querySelector('.eval-inline-input')) return; /* already editing */
    var block = EVAL_BLOCKS.find(function(b) { return b.id === blockId; });
    if (!block) return;
    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'eval-inline-input';
    input.value = block.title;
    input.setAttribute('data-block', blockId);

    function commitBlockTitle() {
        var val = input.value.trim();
        if (val) block.title = val;
        renderBlocks();
    }
    input.addEventListener('blur', commitBlockTitle);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.value = block.title; input.blur(); }
    });

    spanEl.textContent = '';
    spanEl.appendChild(input);
    input.focus();
    input.select();
}

/* ─── Add question (auto-add + inline edit) ─── */
function addQuestion(blockId) {
    var block = EVAL_BLOCKS.find(function(b) { return b.id === blockId; });
    if (!block) return;
    var newId = block.questions.length ? Math.max.apply(null, block.questions.map(function(q) { return q.id; })) + 1 : 1;
    block.questions.push({ id: newId, text: 'New question — click edit to change.' });
    renderBlocks();
    /* Auto-trigger inline edit on the new question */
    var row = document.querySelector('.eval-question[data-block="' + blockId + '"][data-q="' + newId + '"]');
    if (row) {
        var textSpan = row.querySelector('.eval-q-text');
        if (textSpan) editQuestion(blockId, newId, textSpan);
    }
}

/* ─── Edit question inline ─── */
function editQuestion(blockId, qId, spanEl) {
    if (spanEl.querySelector('.eval-inline-input')) return; /* already editing */
    var block = EVAL_BLOCKS.find(function(b) { return b.id === blockId; });
    var q = block ? block.questions.find(function(x) { return x.id === qId; }) : null;
    if (!q) return;
    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'eval-inline-input';
    input.value = q.text;

    function commitQuestion() {
        var val = input.value.trim();
        if (val) q.text = val;
        renderBlocks();
    }
    input.addEventListener('blur', commitQuestion);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.value = q.text; input.blur(); }
    });

    spanEl.textContent = '';
    spanEl.appendChild(input);
    input.focus();
    input.select();
}

/* ─── Delete modal ─── */
function deleteQuestion(blockId, qId) {
    _crudBlockId = blockId;
    _crudQuestionId = qId;
    var block = EVAL_BLOCKS.find(function(b) { return b.id === blockId; });
    var q = block ? block.questions.find(function(x) { return x.id === qId; }) : null;
    var msg = 'Are you sure you want to delete this question?';
    if (block && block.questions.length === 1) {
        msg = 'This is the last question in block "' + block.title + '". Deleting it will also remove the entire block.';
    }
    document.getElementById('evalDeleteMsg').textContent = msg;
    document.getElementById('evalDeleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('evalDeleteModal').style.display = 'none';
}
function confirmDeleteModal() {
    var block = EVAL_BLOCKS.find(function(b) { return b.id === _crudBlockId; });
    if (!block) { closeDeleteModal(); return; }
    block.questions = block.questions.filter(function(x) { return x.id !== _crudQuestionId; });
    if (block.questions.length === 0) {
        EVAL_BLOCKS = EVAL_BLOCKS.filter(function(b) { return b.id !== _crudBlockId; });
        for (var i = 0; i < EVAL_BLOCKS.length; i++) {
            EVAL_BLOCKS[i].letter = String.fromCharCode(65 + i);
        }
    }
    renderBlocks();
    closeDeleteModal();
}

/* ════════════════════════════════════
   PREVIEW STEP
   ════════════════════════════════════ */
function renderPreview() {
    var fac = document.getElementById('evalFaculty');
    var facText = fac.options[fac.selectedIndex] ? fac.options[fac.selectedIndex].text : '';
    var info = document.getElementById('evalBuildInfo').textContent;
    document.getElementById('evalPreviewInfo').textContent = info + ' | Prof. ' + facText;

    var container = document.getElementById('evalPreviewContainer');
    var html = '';
    for (var i = 0; i < EVAL_BLOCKS.length; i++) {
        var b = EVAL_BLOCKS[i];
        html += '<div class="eval-block eval-block-preview">';
        html += '<div class="eval-block-header"><span class="eval-block-title">' + b.letter + '. ' + b.title + '</span></div>';

        for (var q = 0; q < b.questions.length; q++) {
            html += '<div class="eval-question"><span class="eval-q-text">' + (q + 1) + '. ' + b.questions[q].text + '</span></div>';
            html += '<div class="eval-likert" data-block="' + b.id + '" data-q="' + b.questions[q].id + '">';
            var labels = ['Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree'];
            for (var l = 5; l >= 1; l--) {
                html += '<div class="eval-likert-item" data-value="' + l + '" onclick="selectLikert(this)">';
                html += '<span class="eval-likert-num">' + l + '</span>';
                html += '<span class="eval-likert-label">' + labels[5 - l] + '</span>';
                html += '</div>';
            }
            html += '</div>';
        }
        html += '</div>';
    }
    container.innerHTML = html;

    /* Show/hide buttons based on saved state */
    document.getElementById('evalSaveBtn').style.display = EVAL_SAVED ? 'none' : 'inline-flex';
    document.getElementById('evalPublishBtn').style.display = EVAL_SAVED ? 'inline-flex' : 'none';
    document.getElementById('evalEditBtn').textContent = EVAL_SAVED ? 'Edit' : 'Edit';
}

/* ════════════════════════════════════
   SAVE / PUBLISH
   ════════════════════════════════════ */
function handleSaveEval() {
    EVAL_SAVED = true;
    EVAL_PREVIEW_DONE = true;
    var subj = document.getElementById('evalSubject');
    var subjText = subj.options[subj.selectedIndex] ? subj.options[subj.selectedIndex].text : 'Evaluation';

    /* Add to library as Draft */
    var exists = EVAL_LIBRARY.find(function(e) { return e.name === subjText; });
    if (!exists) {
        EVAL_LIBRARY.unshift({ id: EVAL_LIBRARY.length + 1, name: subjText, status: 'Draft', responses: 0 });
    }
    renderLibrary();
    renderPreview();
    updateStepCheckmarks();
    showEvalSuccessModal('Evaluation form saved as Draft.');
}

function handlePublishEval() {
    EVAL_PREVIEW_DONE = true;
    var subj = document.getElementById('evalSubject');
    var subjText = subj.options[subj.selectedIndex] ? subj.options[subj.selectedIndex].text : 'Evaluation';
    var item = EVAL_LIBRARY.find(function(e) { return e.name === subjText; });
    if (item) item.status = 'Published';
    renderLibrary();
    showEvalSuccessModal('Evaluation published successfully!');
}

/* ════════════════════════════════════
   LIBRARY / DASHBOARD (right panel)
   ════════════════════════════════════ */
function renderLibrary() {
    var container = document.getElementById('evalLibraryList');
    var html = '';
    for (var i = 0; i < EVAL_LIBRARY.length; i++) {
        var e = EVAL_LIBRARY[i];
        var statusClass = 'eval-status-' + e.status.toLowerCase();
        html += '<div class="eval-lib-card">';
        html += '<div class="eval-lib-card-top">';
        html += '<span class="eval-lib-name">' + e.name + '</span>';
        html += '<button type="button" class="eval-lib-menu" onclick="toggleLibMenu(' + e.id + ', event)">&#x2026;</button>';
        html += '<div class="eval-lib-dropdown" id="evalLibMenu' + e.id + '">';
        html += '<button onclick="viewLibraryItem(' + e.id + ')">View</button>';
        if (e.status === 'Draft') html += '<button onclick="publishLibraryItem(' + e.id + ')">Publish</button>';
        if (e.status !== 'Closed') html += '<button onclick="closeLibraryItem(' + e.id + ')">Close</button>';
        html += '</div>';
        html += '</div>';
        html += '<div class="eval-lib-card-bottom">';
        html += '<span>Status: <span class="eval-status-badge ' + statusClass + '">' + e.status + '</span></span>';
        html += '<span>' + e.responses + ' Responses</span>';
        html += '</div>';
        html += '</div>';
    }
    container.innerHTML = html;
}

function toggleLibMenu(id, e) {
    e.stopPropagation();
    var menu = document.getElementById('evalLibMenu' + id);
    var isOpen = menu.classList.contains('open');
    document.querySelectorAll('.eval-lib-dropdown').forEach(function(d) { d.classList.remove('open'); });
    if (!isOpen) menu.classList.add('open');
}

function viewLibraryItem(id) {
    document.querySelectorAll('.eval-lib-dropdown').forEach(function(d) { d.classList.remove('open'); });
    showRegistrarToast('Viewing evaluation details...', 'success');
}

function publishLibraryItem(id) {
    var item = EVAL_LIBRARY.find(function(e) { return e.id === id; });
    if (item) item.status = 'Published';
    document.querySelectorAll('.eval-lib-dropdown').forEach(function(d) { d.classList.remove('open'); });
    renderLibrary();
    updateStepCheckmarks();
    showEvalSuccessModal(item.name + ' published successfully!');
}

function closeLibraryItem(id) {
    var item = EVAL_LIBRARY.find(function(e) { return e.id === id; });
    if (item) item.status = 'Closed';
    document.querySelectorAll('.eval-lib-dropdown').forEach(function(d) { d.classList.remove('open'); });
    renderLibrary();
}

/* Click outside to close lib menus */
document.addEventListener('click', function(e) {
    if (!e.target.closest('.eval-lib-menu') && !e.target.closest('.eval-lib-dropdown')) {
        document.querySelectorAll('.eval-lib-dropdown').forEach(function(d) { d.classList.remove('open'); });
    }
});

/* ════════════════════════════════════
   SUCCESS MODAL
   ════════════════════════════════════ */
function showEvalSuccessModal(msg) {
    document.getElementById('evalSuccessMsg').textContent = msg;
    document.getElementById('evalSuccessModal').style.display = 'flex';
}
function closeEvalSuccessModal() {
    document.getElementById('evalSuccessModal').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEvalSuccessModal();
        closeDeleteModal();
    }
});

/* ── Init ── */
renderBlocks();
renderLibrary();
