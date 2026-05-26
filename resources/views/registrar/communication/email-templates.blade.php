@extends('layouts.registrar')

@section('title', 'PLP - Email Notifications & Templates')
@section('page-title', 'EMAIL NOTIFICATIONS & TEMPLATES')

@section('content')
<div class="pf-page et-page" id="emailTemplatePage" data-store-url="{{ route('registrar.communication.email-templates.store') }}" data-update-url-template="{{ route('registrar.communication.email-templates.update', ['registrarEmailTemplate' => '__ID__']) }}" data-csrf="{{ csrf_token() }}">
    <style>.et-panel{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:14px;margin-bottom:16px}.et-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.et-grid .full{grid-column:1/-1}.et-page label{display:block;color:#46564a;font-size:.78rem;font-weight:800;margin-bottom:5px}.et-page input,.et-page textarea,.et-page select{width:100%;border:1px solid #cfd9d2;border-radius:7px;padding:8px 10px}.et-page textarea{min-height:120px}.et-save{background:#146c43;border:0;border-radius:7px;color:#fff;font-weight:800;min-height:38px;padding:8px 14px}.et-secondary{background:#eef5f0;border:1px solid #bfd2c5;border-radius:7px;color:#143521;font-weight:800;min-height:38px;padding:8px 14px}.et-code{font-weight:900;color:#143521}.et-muted{font-size:.82rem;color:#66756b}.et-action-btn{background:#146c43;border:0;border-radius:6px;color:#fff;font-size:.78rem;font-weight:800;padding:7px 12px}.et-modal{position:fixed;inset:0;z-index:1050;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.42);padding:18px}.et-modal.is-open{display:flex}.et-modal-box{width:min(720px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:8px;box-shadow:0 18px 45px rgba(15,23,42,.25);padding:18px}.et-modal-title{font-size:1.05rem;font-weight:900;color:#143521;margin:0 0 14px}.et-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px}.et-active-row{display:flex;align-items:center;gap:8px}.et-active-row input{width:auto}.et-readonly{background:#f8faf9;color:#647067}@media(max-width:900px){.et-grid{grid-template-columns:1fr}.et-grid .full{grid-column:auto}}</style>
    <form class="et-panel" id="emailTemplateCreateForm">
        <div class="et-grid">
            <div><label>Code</label><input name="code" required placeholder="STATUS_CHANGE"></div>
            <div><label>Audience</label><input name="audience" required placeholder="Student / Applicant / Stakeholder"></div>
            <div class="full"><label>Name</label><input name="name" required placeholder="Template name"></div>
            <div class="full"><label>Subject</label><input name="subject" required placeholder="Email subject"></div>
            <div class="full"><label>Body</label><textarea name="body" required placeholder="Use placeholders like @{{name}}, @{{status}}, @{{student_email}}"></textarea></div>
            <div><button class="et-save" type="submit">Add Template</button></div>
        </div>
    </form>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead><tr><th>Code</th><th>Name</th><th>Audience</th><th>Subject</th><th>Status</th><th>Updated By</th><th>Action</th></tr></thead>
            <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td class="et-code">{{ $template->code }}</td>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->audience }}</td>
                        <td>{{ $template->subject }}<div class="et-muted">{{ \Illuminate\Support\Str::limit($template->body, 90) }}</div></td>
                        <td>{{ $template->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>{{ optional($template->updatedBy)->name ?: 'System' }}</td>
                        <td><button type="button" class="et-action-btn" data-et-edit-id="{{ $template->id }}">Edit</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="et-modal" id="emailTemplateEditModal" aria-hidden="true">
        <div class="et-modal-box" role="dialog" aria-modal="true" aria-labelledby="emailTemplateEditTitle">
            <h3 class="et-modal-title" id="emailTemplateEditTitle">Edit Email Template</h3>
            <form id="emailTemplateEditForm">
                <input type="hidden" name="id" id="etEditId">
                <div class="et-grid">
                    <div><label>Code</label><input id="etEditCode" class="et-readonly" readonly></div>
                    <div><label>Audience</label><input name="audience" id="etEditAudience" required></div>
                    <div class="full"><label>Name</label><input name="name" id="etEditName" required></div>
                    <div class="full"><label>Subject</label><input name="subject" id="etEditSubject" required></div>
                    <div class="full"><label>Body</label><textarea name="body" id="etEditBody" required></textarea></div>
                    <div class="full et-active-row">
                        <input type="checkbox" name="is_active" id="etEditActive" value="1">
                        <label for="etEditActive" style="margin:0;">Active template</label>
                    </div>
                </div>
                <div class="et-modal-actions">
                    <button type="button" class="et-secondary" id="emailTemplateEditCancel">Cancel</button>
                    <button type="submit" class="et-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script type="application/json" id="emailTemplatePayload">
        @json($templates->map(function ($template) {
            return [
                'id' => $template->id,
                'code' => $template->code,
                'name' => $template->name,
                'audience' => $template->audience,
                'subject' => $template->subject,
                'body' => $template->body,
                'is_active' => (bool) $template->is_active,
            ];
        })->values())
    </script>
</div>
<script>
(function(){var page=document.getElementById('emailTemplatePage'),form=document.getElementById('emailTemplateCreateForm'),editModal=document.getElementById('emailTemplateEditModal'),editForm=document.getElementById('emailTemplateEditForm'),editCancel=document.getElementById('emailTemplateEditCancel'),payload=document.getElementById('emailTemplatePayload');if(!page||!form)return;var templates=[];try{templates=JSON.parse(payload?payload.textContent:'[]')}catch(e){templates=[]}function toast(message,type){if(window.showRegistrarToast)showRegistrarToast(message,type);else alert(message)}function parseResponse(r){return r.json().then(function(p){if(!r.ok)throw p;return p})}function templateById(id){return templates.find(function(item){return String(item.id)===String(id)})}function updateUrl(id){return (page.dataset.updateUrlTemplate||'').replace('__ID__',encodeURIComponent(id))}function openEdit(id){var item=templateById(id);if(!item||!editModal)return;document.getElementById('etEditId').value=item.id;document.getElementById('etEditCode').value=item.code||'';document.getElementById('etEditAudience').value=item.audience||'';document.getElementById('etEditName').value=item.name||'';document.getElementById('etEditSubject').value=item.subject||'';document.getElementById('etEditBody').value=item.body||'';document.getElementById('etEditActive').checked=!!item.is_active;editModal.classList.add('is-open');editModal.setAttribute('aria-hidden','false')}function closeEdit(){if(!editModal)return;editModal.classList.remove('is-open');editModal.setAttribute('aria-hidden','true')}form.addEventListener('submit',function(e){e.preventDefault();var data={};new FormData(form).forEach(function(v,k){data[k]=v});fetch(page.dataset.storeUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':page.dataset.csrf},credentials:'same-origin',body:JSON.stringify(data)}).then(parseResponse).then(function(p){toast(p.message||'Template saved.','success');window.location.reload()}).catch(function(p){toast(p&&p.message?p.message:'Unable to save template.','error')})});document.addEventListener('click',function(e){var btn=e.target.closest('[data-et-edit-id]');if(btn){openEdit(btn.getAttribute('data-et-edit-id'))}});if(editCancel)editCancel.addEventListener('click',closeEdit);if(editModal)editModal.addEventListener('click',function(e){if(e.target===editModal)closeEdit()});if(editForm)editForm.addEventListener('submit',function(e){e.preventDefault();var id=document.getElementById('etEditId').value;var data={};new FormData(editForm).forEach(function(v,k){data[k]=v});data.is_active=document.getElementById('etEditActive').checked?1:0;fetch(updateUrl(id),{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':page.dataset.csrf},credentials:'same-origin',body:JSON.stringify(data)}).then(parseResponse).then(function(p){toast(p.message||'Template updated.','success');window.location.reload()}).catch(function(p){toast(p&&p.message?p.message:'Unable to update template.','error')})})})();
</script>
@endsection
