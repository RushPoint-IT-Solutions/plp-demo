@extends('layouts.registrar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Archive Settings</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('archive.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">General Settings</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Default Retention Period (Months)</label>
                                            <input type="number" name="default_retention_months" class="form-control" 
                                                   value="{{ $settings['default_retention_months']->value ?? 12 }}" min="1" max="120">
                                            <small class="form-text text-muted">Default retention period for records without specific policies.</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Auto-Archive Enabled</label>
                                            <select name="auto_archive_enabled" class="form-control">
                                                <option value="1" {{ ($settings['auto_archive_enabled']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['auto_archive_enabled']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Archive Schedule (Cron Expression)</label>
                                            <input type="text" name="archive_schedule" class="form-control" 
                                                   value="{{ $settings['archive_schedule']->value ?? '0 2 * * *' }}">
                                            <small class="form-text text-muted">Default: 2:00 AM daily. Uses cron format: minute hour day month weekday</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Disposal Settings</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Auto-Disposal Enabled</label>
                                            <select name="auto_disposal_enabled" class="form-control">
                                                <option value="1" {{ ($settings['auto_disposal_enabled']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['auto_disposal_enabled']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Default Disposal Method</label>
                                            <select name="default_disposal_method" class="form-control">
                                                <option value="secure_delete" {{ ($settings['default_disposal_method']->value ?? '') == 'secure_delete' ? 'selected' : '' }}>Secure Delete</option>
                                                <option value="anonymize" {{ ($settings['default_disposal_method']->value ?? '') == 'anonymize' ? 'selected' : '' }}>Anonymize</option>
                                                <option value="export_then_delete" {{ ($settings['default_disposal_method']->value ?? '') == 'export_then_delete' ? 'selected' : '' }}>Export Then Delete</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Require Certificate for Disposal</label>
                                            <select name="require_certificate" class="form-control">
                                                <option value="1" {{ ($settings['require_certificate']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['require_certificate']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                            <small class="form-text text-muted">Generate SHA-256 certificate for compliance.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Notification Settings</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Notify on Archive</label>
                                            <select name="notify_on_archive" class="form-control">
                                                <option value="1" {{ ($settings['notify_on_archive']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['notify_on_archive']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Notify on Retrieval Request</label>
                                            <select name="notify_on_retrieval_request" class="form-control">
                                                <option value="1" {{ ($settings['notify_on_retrieval_request']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['notify_on_retrieval_request']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Notify on Disposal Scheduled</label>
                                            <select name="notify_on_disposal_scheduled" class="form-control">
                                                <option value="1" {{ ($settings['notify_on_disposal_scheduled']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['notify_on_disposal_scheduled']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Access Control</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Allow Student Self-Retrieval</label>
                                            <select name="allow_student_self_retrieval" class="form-control">
                                                <option value="1" {{ ($settings['allow_student_self_retrieval']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['allow_student_self_retrieval']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Allow Parent Retrieval</label>
                                            <select name="allow_parent_retrieval" class="form-control">
                                                <option value="1" {{ ($settings['allow_parent_retrieval']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['allow_parent_retrieval']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Log All Access</label>
                                            <select name="log_all_access" class="form-control">
                                                <option value="1" {{ ($settings['log_all_access']->value ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ ($settings['log_all_access']->value ?? '') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                            <small class="form-text text-muted">Log every archive access for audit trail.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <a href="{{ route('archive.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Archive
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection