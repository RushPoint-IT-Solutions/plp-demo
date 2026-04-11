document.addEventListener('DOMContentLoaded', function () {
    var dataNode = document.getElementById('facultyEvalData');
    var listNode = document.getElementById('facultyEvalList');
    var resultsNode = document.getElementById('facultyEvalResultsBody');
    var scoreTab = document.getElementById('facultyEvalTabScores');
    var commentTab = document.getElementById('facultyEvalTabComments');

    if (!dataNode || !listNode || !resultsNode || !scoreTab || !commentTab) {
        return;
    }

    var library = [];
    var panels = {};

    try {
        library = JSON.parse(dataNode.getAttribute('data-library') || '[]');
    } catch (error) {
        library = [];
    }

    try {
        panels = JSON.parse(dataNode.getAttribute('data-panels') || '{}');
    } catch (error2) {
        panels = {};
    }

    var activeSubjectId = parseInt(dataNode.getAttribute('data-selected') || '0', 10) || 0;
    var activeTab = 'scores';
    var openByTab = {
        scores: {},
        comments: {}
    };

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (ch) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
            return map[ch];
        });
    }

    function normalizeSubjectId(value) {
        return parseInt(value || '0', 10) || 0;
    }

    function statusBadgeClass(status) {
        var normalized = String(status || '').toLowerCase();
        if (normalized === 'published') return 'eval-status-published';
        if (normalized === 'draft') return 'eval-status-draft';
        return 'eval-status-closed';
    }

    function tabButtonState() {
        var isScores = activeTab === 'scores';
        scoreTab.classList.toggle('active', isScores);
        commentTab.classList.toggle('active', !isScores);
        scoreTab.setAttribute('aria-selected', isScores ? 'true' : 'false');
        commentTab.setAttribute('aria-selected', isScores ? 'false' : 'true');
    }

    function buildScoreGroup(title, item, idx) {
        var key = title + '::' + idx;
        var isOpen = openByTab.scores[key] !== false;
        var scoreValue = Number(item.mean_score || 0);
        var chevron = '<svg class="faculty-eval-group-chevron' + (isOpen ? ' is-open' : '') + '" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        return ''
            + '<div class="faculty-eval-group-card' + (isOpen ? ' is-open' : '') + '">'
            + '  <button type="button" class="faculty-eval-group-head" data-tab-group="scores" data-group-key="' + escapeHtml(key) + '">'
            + '    <span class="faculty-eval-group-title">' + escapeHtml(title) + '</span>'
            + '    <span class="faculty-eval-group-toggle">' + chevron + '</span>'
            + '  </button>'
            + '  <div class="faculty-eval-group-body"' + (isOpen ? '' : ' style="display:none;"') + '>'
            + '    <div class="faculty-eval-score-grid">'
            + '      <div class="faculty-eval-score-metric">'
            + '        <span class="faculty-eval-score-label">Mean Score:</span>'
            + '        <span class="faculty-eval-score-pill">' + scoreValue.toFixed(2) + '</span>'
            + '      </div>'
            + '      <div class="faculty-eval-score-metric">'
            + '        <span class="faculty-eval-score-label">Interpretation:</span>'
            + '        <span class="faculty-eval-interpretation">' + escapeHtml(item.interpretation || '-') + '</span>'
            + '      </div>'
            + '    </div>'
            + '  </div>'
            + '</div>';
    }

    function buildCommentGroup(title, item, idx) {
        var key = title + '::' + idx;
        var isOpen = openByTab.comments[key] === true;
        var comments = Array.isArray(item.comments) ? item.comments : [];
        var count = typeof item.count === 'number' ? item.count : comments.length;
        var listHtml = '';
        var chevron = '<svg class="faculty-eval-group-chevron' + (isOpen ? ' is-open' : '') + '" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        comments.forEach(function (comment, index) {
            listHtml += '<li class="faculty-eval-comment-item"><span class="faculty-eval-comment-num">' + (index + 1) + '.</span> ' + escapeHtml(comment) + '</li>';
        });

        return ''
            + '<div class="faculty-eval-group-card' + (isOpen ? ' is-open' : '') + '">'
            + '  <button type="button" class="faculty-eval-group-head" data-tab-group="comments" data-group-key="' + escapeHtml(key) + '">'
            + '    <span class="faculty-eval-group-title">' + escapeHtml(title) + '</span>'
            + '    <span class="faculty-eval-comment-count">' + count + ' Comments</span>'
            + '    <span class="faculty-eval-group-toggle">' + chevron + '</span>'
            + '  </button>'
            + '  <div class="faculty-eval-group-body"' + (isOpen ? '' : ' style="display:none;"') + '>'
            + '    <ul class="faculty-eval-comment-list">' + listHtml + '</ul>'
            + '  </div>'
            + '</div>';
    }

    function renderResults() {
        var panel = panels[String(activeSubjectId)] || null;

        if (!panel) {
            resultsNode.innerHTML = '<div class="faculty-eval-empty-message">Select a subject from the Evaluation List to view its evaluation results. You can only view one subject at a time.</div>';
            return;
        }

        var groups = activeTab === 'scores' ? (panel.scores || []) : (panel.comments || []);
        if (!groups.length) {
            resultsNode.innerHTML = '<div class="faculty-eval-empty-message">No ' + activeTab + ' data available for this subject.</div>';
            return;
        }

        var html = '';
        groups.forEach(function (item, idx) {
            var title = activeTab === 'scores'
                ? (item.title || ('Criteria ' + (idx + 1)))
                : (item.section || ('Section ' + (idx + 1)));
            html += activeTab === 'scores'
                ? buildScoreGroup(title, item, idx)
                : buildCommentGroup(title, item, idx);
        });

        resultsNode.innerHTML = html;
    }

    function renderList() {
        if (!library.length) {
            listNode.innerHTML = '<div class="faculty-eval-empty-message">No evaluation records found.</div>';
            return;
        }

        var html = '';
        library.forEach(function (item) {
            var id = normalizeSubjectId(item.id);
            var isActive = id === activeSubjectId;

            html += ''
                + '<button type="button" class="eval-lib-card faculty-eval-list-item' + (isActive ? ' is-active' : '') + '" data-subject-id="' + id + '">'
                + '  <div class="eval-lib-card-top">'
                + '    <span class="eval-lib-name">' + escapeHtml(item.code || item.name || '-') + '</span>'
                + '  </div>'
                + '  <div class="eval-lib-card-bottom">'
                + '    <span>Status: <span class="eval-status-badge ' + statusBadgeClass(item.status) + '">' + escapeHtml(item.status || 'Closed') + '</span></span>'
                + '    <span>' + Number(item.responses || 0) + ' Responses</span>'
                + '  </div>'
                + '</button>';
        });

        listNode.innerHTML = html;
    }

    function setActiveTab(tab) {
        activeTab = tab === 'comments' ? 'comments' : 'scores';
        tabButtonState();
        renderResults();
    }

    function setActiveSubject(subjectId) {
        activeSubjectId = normalizeSubjectId(subjectId);
        openByTab = { scores: {}, comments: {} };
        renderList();
        renderResults();
    }

    listNode.addEventListener('click', function (event) {
        var card = event.target.closest('[data-subject-id]');
        if (!card) {
            return;
        }

        setActiveSubject(card.getAttribute('data-subject-id'));
    });

    resultsNode.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-group-key][data-tab-group]');
        if (!trigger) {
            return;
        }

        var tab = trigger.getAttribute('data-tab-group');
        var key = trigger.getAttribute('data-group-key');
        if (!tab || !key || !openByTab[tab]) {
            return;
        }

        openByTab[tab][key] = !openByTab[tab][key];
        renderResults();
    });

    scoreTab.addEventListener('click', function () {
        setActiveTab('scores');
    });

    commentTab.addEventListener('click', function () {
        setActiveTab('comments');
    });

    if (!panels[String(activeSubjectId)] && library.length) {
        activeSubjectId = normalizeSubjectId(library[0].id);
    }

    tabButtonState();
    renderList();
    renderResults();
});
