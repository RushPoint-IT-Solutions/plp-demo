/* document-layout-editor.js
 * Generic drag/style/save engine for registrar printable documents.
 * Extracted from honorable-dismissal.js and parameterized so any document
 * (TOR, COR, etc.) can reuse the same positioned-text-element editor
 * instead of re-implementing it per document.
 *
 * Usage:
 *   var editor = DocLayoutEditor.create({
 *       sheetEl: document.getElementById('torSheet'),
 *       toolbarEl: document.getElementById('torEditorToolbar'),
 *       loadUrl: '...',        // GET -> { success, template: { content_json } }
 *       saveUrl: '...',        // POST { content_json } -> { success, message }
 *       csrfToken: '...'
 *   });
 *   editor.load().then(function (layout) { editor.render(layout); });
 */
(function (window) {
    function clampNumber(value, min, max, fallback) {
        var n = parseFloat(value);
        if (isNaN(n)) return fallback;
        return Math.min(max, Math.max(min, n));
    }

    function escHtml(v) {
        return String(v || '').replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function hasTemplateTokens(value) {
        return /\{\{\s*[a-z0-9_]+\s*\}\}/i.test(String(value || ''));
    }

    function normalizeLayout(layout) {
        layout = layout || {};
        var elements = Array.isArray(layout.elements) ? layout.elements : [];
        return {
            page: Object.assign({ width_mm: 210, height_mm: 297, orientation: 'portrait', background: '#ffffff' }, layout.page || {}),
            elements: elements.map(function (item, index) {
                return {
                    id: String(item.id || ('element_' + index)),
                    type: 'text',
                    text: String(item.text || ''),
                    resolved_text: item.resolved_text == null ? null : String(item.resolved_text),
                    top: clampNumber(item.top, 0, 100, 10),
                    left: clampNumber(item.left, 0, 100, 10),
                    width: clampNumber(item.width, 1, 100, 30),
                    font_family: item.font_family || 'Arial',
                    font_size: clampNumber(item.font_size, 6, 96, 12),
                    font_weight: item.font_weight === 'bold' ? 'bold' : 'normal',
                    font_style: item.font_style === 'italic' ? 'italic' : 'normal',
                    text_decoration: item.text_decoration === 'underline' ? 'underline' : 'none',
                    text_align: ['left', 'center', 'right', 'justify'].indexOf(item.text_align) >= 0 ? item.text_align : 'left',
                    line_height: clampNumber(item.line_height, 0.8, 3, 1.25)
                };
            })
        };
    }

    function applyElementStyle(el, item) {
        el.style.top = item.top + '%';
        el.style.left = item.left + '%';
        el.style.width = item.width + '%';
        el.style.fontFamily = "'" + item.font_family + "', sans-serif";
        el.style.fontSize = item.font_size + 'pt';
        el.style.fontWeight = item.font_weight;
        el.style.fontStyle = item.font_style;
        el.style.textDecoration = item.text_decoration;
        el.style.textAlign = item.text_align;
        el.style.lineHeight = item.line_height;
    }

    function DocLayoutEditor(config) {
        this.sheetEl = config.sheetEl;
        this.toolbarEl = config.toolbarEl || null;
        this.loadUrl = config.loadUrl;
        this.saveUrl = config.saveUrl;
        this.csrfToken = config.csrfToken;
        this.editable = !!config.editableByDefault;
        this.elementClass = config.elementClass || 'doc-tpl-element';
        this.sheetClass = config.sheetClass || 'doc-tpl-sheet';
        this.state = null;
        this.selectedId = null;
        this.currentEntityId = null;
        this._bound = false;
    }

    DocLayoutEditor.prototype._json = function (url, options) {
        options = options || {};
        options.credentials = 'same-origin';
        options.headers = Object.assign({
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        return fetch(url, options).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (!response.ok) throw data;
                if (data && data.success === false) throw data;
                return data;
            });
        });
    };

    DocLayoutEditor.prototype.load = function (entityId) {
        var self = this;
        this.currentEntityId = entityId || null;
        var url = typeof this.loadUrl === 'function' ? this.loadUrl(entityId) : this.loadUrl;
        return this._json(url).then(function (data) {
            if (!data || !data.success || !data.template) {
                throw { message: 'Unable to load layout template.' };
            }
            self.state = normalizeLayout(data.template.content_json || {});
            return self.state;
        });
    };

    DocLayoutEditor.prototype.render = function (layout) {
        var self = this;
        var sheet = this.sheetEl;
        if (!sheet) return;
        layout = layout || this.state;

        sheet.innerHTML = '';
        sheet.classList.add(this.sheetClass);
        sheet.style.background = (layout.page && layout.page.background) || '#ffffff';
        (layout.elements || []).forEach(function (item) {
            var el = document.createElement('div');
            el.className = self.elementClass;
            el.setAttribute('data-doc-element-id', item.id);
            el.setAttribute('contenteditable', self.editable ? 'true' : 'false');
            el.innerHTML = escHtml(item.resolved_text || item.text).replace(/\n/g, '<br>');
            applyElementStyle(el, item);
            sheet.appendChild(el);
        });

        this.selectElement(this.selectedId && this.elementById(this.selectedId) ? this.selectedId : null);
        if (!this._bound) this.bindEditing();
    };

    DocLayoutEditor.prototype.elementById = function (id) {
        if (!this.state) return null;
        return this.state.elements.find(function (item) { return item.id === id; }) || null;
    };

    DocLayoutEditor.prototype.setEditable = function (editable) {
        this.editable = !!editable;
        var self = this;
        if (this.sheetEl) {
            this.sheetEl.querySelectorAll('[data-doc-element-id]').forEach(function (el) {
                el.setAttribute('contenteditable', self.editable ? 'true' : 'false');
            });
        }
        if (!this.editable) this.selectElement(null);
        this.syncToolbar();
    };

    DocLayoutEditor.prototype.selectElement = function (id) {
        this.selectedId = id;
        if (this.sheetEl) {
            var self = this;
            this.sheetEl.querySelectorAll('[data-doc-element-id]').forEach(function (el) {
                el.classList.toggle('is-selected', el.getAttribute('data-doc-element-id') === id);
            });
        }
        this.syncToolbar();
    };

    DocLayoutEditor.prototype.syncToolbar = function () {
        var toolbar = this.toolbarEl;
        var item = this.elementById(this.selectedId);
        if (!toolbar) return;

        toolbar.setAttribute('aria-hidden', item ? 'false' : 'true');
        toolbar.classList.toggle('is-visible', !!item);
        if (!item) return;

        this._setControl('[data-doc-font-family]', item.font_family);
        this._setControl('[data-doc-font-size]', item.font_size);
        this._setControl('[data-doc-text-align]', item.text_align);
        this._setControl('[data-doc-top]', item.top);
        this._setControl('[data-doc-left]', item.left);
        toolbar.querySelectorAll('[data-doc-style]').forEach(function (btn) {
            var style = btn.getAttribute('data-doc-style');
            var active = (style === 'bold' && item.font_weight === 'bold') ||
                (style === 'italic' && item.font_style === 'italic') ||
                (style === 'underline' && item.text_decoration === 'underline');
            btn.classList.toggle('is-active', active);
        });
    };

    DocLayoutEditor.prototype._setControl = function (selector, value) {
        if (!this.toolbarEl) return;
        var control = this.toolbarEl.querySelector(selector);
        if (control) control.value = value;
    };

    DocLayoutEditor.prototype.updateSelected = function (changes) {
        var item = this.elementById(this.selectedId);
        if (!item) return;
        Object.assign(item, changes);
        var self = this;
        var el = null;
        this.sheetEl.querySelectorAll('[data-doc-element-id]').forEach(function (candidate) {
            if (candidate.getAttribute('data-doc-element-id') === item.id) el = candidate;
        });
        if (el) applyElementStyle(el, item);
        this.syncToolbar();
    };

    DocLayoutEditor.prototype.deleteSelected = function () {
        if (!this.state || !this.selectedId) return;
        this.state.elements = this.state.elements.filter(function (item) {
            return item.id !== this.selectedId;
        }, this);
        this.selectedId = null;
        this.render(this.state);
    };

    DocLayoutEditor.prototype.forSave = function () {
        var state = this.state || normalizeLayout({});
        return {
            page: state.page,
            elements: state.elements.map(function (item) {
                var copy = Object.assign({}, item);
                delete copy.resolved_text;
                return copy;
            })
        };
    };

    DocLayoutEditor.prototype.save = function () {
        var self = this;
        return this._json(this.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ content_json: this.forSave() })
        }).then(function (data) {
            return self.load(self.currentEntityId).then(function (layout) {
                self.render(layout);
                return data;
            });
        });
    };

    DocLayoutEditor.prototype.cleanPrintHtml = function () {
        var clone = this.sheetEl.cloneNode(true);
        clone.removeAttribute('id');
        clone.classList.remove(this.sheetClass + '-canvas');
        clone.querySelectorAll('[data-doc-element-id]').forEach(function (el) {
            el.classList.remove('is-selected');
            el.removeAttribute('contenteditable');
        });
        return clone.outerHTML;
    };

    DocLayoutEditor.prototype.bindEditing = function () {
        var self = this;
        var sheet = this.sheetEl;
        if (!sheet || this._bound) return;
        this._bound = true;

        sheet.addEventListener('mousedown', function (e) {
            if (!self.editable) return;
            var target = e.target.closest('[data-doc-element-id]');
            if (!target || !sheet.contains(target)) {
                self.selectElement(null);
                return;
            }

            var id = target.getAttribute('data-doc-element-id');
            var item = self.elementById(id);
            if (!item) return;
            self.selectElement(id);

            var sheetRect = sheet.getBoundingClientRect();
            var targetRect = target.getBoundingClientRect();
            var startX = e.clientX;
            var startY = e.clientY;
            var startLeftPx = targetRect.left - sheetRect.left;
            var startTopPx = targetRect.top - sheetRect.top;
            var moved = false;

            function onMove(ev) {
                moved = true;
                var nextLeft = ((startLeftPx + ev.clientX - startX) / sheetRect.width) * 100;
                var nextTop = ((startTopPx + ev.clientY - startY) / sheetRect.height) * 100;
                item.left = Math.round(clampNumber(nextLeft, 0, 100, item.left) * 1000) / 1000;
                item.top = Math.round(clampNumber(nextTop, 0, 100, item.top) * 1000) / 1000;
                applyElementStyle(target, item);
                self.syncToolbar();
            }

            function onUp() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                if (moved) target.blur();
            }

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        sheet.addEventListener('input', function (e) {
            if (!self.editable) return;
            var target = e.target.closest('[data-doc-element-id]');
            if (!target) return;
            var item = self.elementById(target.getAttribute('data-doc-element-id'));
            if (item) {
                item.resolved_text = target.innerText.trim();
                if (!self.currentEntityId || !hasTemplateTokens(item.text)) {
                    item.text = item.resolved_text;
                }
            }
        });

        if (!this.toolbarEl) return;
        var toolbar = this.toolbarEl;
        var fontFamily = toolbar.querySelector('[data-doc-font-family]');
        var fontSize = toolbar.querySelector('[data-doc-font-size]');
        var textAlign = toolbar.querySelector('[data-doc-text-align]');
        var topPercent = toolbar.querySelector('[data-doc-top]');
        var leftPercent = toolbar.querySelector('[data-doc-left]');
        var deleteBtn = toolbar.querySelector('[data-doc-delete]');

        if (fontFamily) fontFamily.addEventListener('change', function () { self.updateSelected({ font_family: this.value }); });
        if (fontSize) fontSize.addEventListener('input', function () { self.updateSelected({ font_size: clampNumber(this.value, 6, 96, 12) }); });
        if (textAlign) textAlign.addEventListener('change', function () { self.updateSelected({ text_align: this.value }); });
        if (topPercent) topPercent.addEventListener('input', function () { self.updateSelected({ top: clampNumber(this.value, 0, 100, 0) }); });
        if (leftPercent) leftPercent.addEventListener('input', function () { self.updateSelected({ left: clampNumber(this.value, 0, 100, 0) }); });
        if (deleteBtn) deleteBtn.addEventListener('click', function () { self.deleteSelected(); });

        toolbar.querySelectorAll('[data-doc-style]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var item = self.elementById(self.selectedId);
                if (!item) return;
                var style = btn.getAttribute('data-doc-style');
                if (style === 'bold') self.updateSelected({ font_weight: item.font_weight === 'bold' ? 'normal' : 'bold' });
                if (style === 'italic') self.updateSelected({ font_style: item.font_style === 'italic' ? 'normal' : 'italic' });
                if (style === 'underline') self.updateSelected({ text_decoration: item.text_decoration === 'underline' ? 'none' : 'underline' });
            });
        });
    };

    window.DocLayoutEditor = {
        create: function (config) { return new DocLayoutEditor(config); },
        escHtml: escHtml,
        normalizeLayout: normalizeLayout
    };
})(window);
