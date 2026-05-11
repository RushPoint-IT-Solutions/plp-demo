@extends('layouts.registrar')

@section('title', 'PLP - Email Notifications & Templates')
@section('page-title', 'EMAIL NOTIFICATIONS & TEMPLATES')

@section('content')
<div class="pf-page et-page" id="emailTemplatePage" data-store-url="{{ route('registrar.communication.email-templates.store') }}" data-csrf="{{ csrf_token() }}">
    <style>.et-panel{background:#fff;border:1px solid #dfe8e2;border-radius:8px;padding:14px;margin-bottom:16px}.et-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.et-grid .full{grid-column:1/-1}.et-page label{display:block;color:#46564a;font-size:.78rem;font-weight:800;margin-bottom:5px}.et-page input,.et-page textarea{width:100%;border:1px solid #cfd9d2;border-radius:7px;padding:8px 10px}.et-page textarea{min-height:120px}.et-save{background:#146c43;border:0;border-radius:7px;color:#fff;font-weight:800;min-height:38px;padding:8px 14px}.et-code{font-weight:900;color:#143521}.et-muted{font-size:.82rem;color:#66756b}@media(max-width:900px){.et-grid{grid-template-columns:1fr}.et-grid .full{grid-column:auto}}</style>
    <form class="et-panel" id="emailTemplateCreateForm">
        <div class="et-grid">
            <div><label>Code</label><input name="code" required placeholder="STATUS_CHANGE"></div>
            <div><label>Audience</label><input name="audience" required placeholder="Student / Applicant / Stakeholder"></div>
            <div class="full"><label>Name</label><input name="name" required placeholder="Template name"></div>
            <div class="full"><label>Subject</label><input name="subject" required placeholder="Email subject"></div>
            <div class="full"><label>Body</label><textarea name="body" required placeholder="Use placeholders like {{ '{{name}}' }}, {{ '{{status}}' }}, {{ '{{student_email}}' }}"></textarea></div>
            <div><button class="et-save" type="submit">Add Template</button></div>
        </div>
    </form>
    <div class="student-table-wrapper table-responsive">
        <table class="student-table registrar-table" data-no-auto-pager="1">
            <thead><tr><th>Code</th><th>Name</th><th>Audience</th><th>Subject</th><th>Status</th><th>Updated By</th></tr></thead>
            <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td class="et-code">{{ $template->code }}</td>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->audience }}</td>
                        <td>{{ $template->subject }}<div class="et-muted">{{ \Illuminate\Support\Str::limit($template->body, 90) }}</div></td>
                        <td>{{ $template->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>{{ optional($template->updatedBy)->name ?: 'System' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
(function(){var page=document.getElementById('emailTemplatePage'),form=document.getElementById('emailTemplateCreateForm');if(!page||!form)return;form.addEventListener('submit',function(e){e.preventDefault();var data={};new FormData(form).forEach(function(v,k){data[k]=v});fetch(page.dataset.storeUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':page.dataset.csrf},credentials:'same-origin',body:JSON.stringify(data)}).then(function(r){return r.json().then(function(p){if(!r.ok)throw p;return p})}).then(function(p){if(window.showRegistrarToast)showRegistrarToast(p.message||'Template saved.','success');window.location.reload()}).catch(function(p){var m=p&&p.message?p.message:'Unable to save template.';if(window.showRegistrarToast)showRegistrarToast(m,'error')})})})();
</script>
@endsection
