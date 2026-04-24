@php
    use Illuminate\Support\Facades\Route;

    $registrarFormDefinitions = \App\RegistrarFormUpload::definitions();
    $registrarFormOptions = [];
    $selectedFormKey = old('form_key', request()->input('form_key', ''));
    $currentRouteName = Route::currentRouteName();

    foreach ($registrarFormDefinitions as $formKey => $definition) {
        $registrarFormOptions[] = [
            'value' => (string) $formKey,
            'label' => (string) ($definition['label'] ?? $formKey),
        ];

        if ($selectedFormKey === '' && (string) ($definition['route_name'] ?? '') === (string) $currentRouteName) {
            $selectedFormKey = (string) $formKey;
        }
    }

    if ($selectedFormKey === '' && array_key_exists('placeholder', $registrarFormDefinitions)) {
        $selectedFormKey = 'placeholder';
    }

    $currentFormUploads = \App\RegistrarFormUpload::currentForFormKeys(array_keys($registrarFormDefinitions));
    $selectedUpload = $selectedFormKey !== '' && isset($currentFormUploads[$selectedFormKey])
        ? $currentFormUploads[$selectedFormKey]
        : null;
@endphp

<div class="registrar-forms-toolbar">
    <div class="registrar-forms-toolbar__head">
        <div>
            <div class="registrar-forms-toolbar__eyebrow">Registrar Forms</div>
            <h2 class="registrar-forms-toolbar__title">Upload a current form version</h2>
        </div>
        <div class="registrar-forms-toolbar__count">
            {{ count($registrarFormOptions) }} forms tracked
        </div>
    </div>

    <form class="registrar-forms-toolbar__form" action="{{ route('registrar.registrar-menu.forms.uploads.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="registrar-form-row registrar-forms-toolbar__row">
            <div class="registrar-form-group registrar-forms-toolbar__group registrar-forms-toolbar__group--wide">
                <label class="registrar-form-label" for="registrarFormKey">Form</label>
                @include('registrar.components.listbox-select', [
                    'id' => 'registrarFormKey',
                    'name' => 'form_key',
                    'options' => $registrarFormOptions,
                    'selected' => $selectedFormKey,
                    'placeholder' => '- Select form -',
                ])
            </div>

            <div class="registrar-form-group registrar-forms-toolbar__group registrar-forms-toolbar__group--file">
                <label class="registrar-form-label" for="registrarFormFile">Upload File</label>
                <input id="registrarFormFile" class="registrar-form-input registrar-forms-toolbar__file" type="file" name="form_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" required>
            </div>

            <div class="registrar-form-group registrar-forms-toolbar__group registrar-forms-toolbar__group--notes">
                <label class="registrar-form-label" for="registrarFormNotes">Notes</label>
                <textarea id="registrarFormNotes" class="registrar-form-input registrar-forms-toolbar__notes" name="notes" rows="2" placeholder="Optional upload notes"></textarea>
            </div>

            <div class="registrar-form-btn-group registrar-forms-toolbar__actions">
                <button class="btn-registrar-save registrar-forms-toolbar__submit" type="submit">Upload</button>
            </div>
        </div>
    </form>

    <div class="registrar-forms-toolbar__status">
        @if ($selectedUpload)
            <div class="registrar-forms-toolbar__status-card">
                <div class="registrar-forms-toolbar__status-label">Current file</div>
                <div class="registrar-forms-toolbar__status-name">
                    {{ $selectedUpload->form_label }} v{{ $selectedUpload->version_number }}
                </div>
                <div class="registrar-forms-toolbar__status-meta">
                    {{ $selectedUpload->original_filename }}
                    @if (!empty($selectedUpload->size_bytes))
                        • {{ number_format($selectedUpload->size_bytes / 1024, 1) }} KB
                    @endif
                    @if (!empty($selectedUpload->updated_at))
                        • {{ optional($selectedUpload->updated_at)->format('M d, Y h:i A') }}
                    @endif
                </div>
                <div class="registrar-forms-toolbar__status-actions">
                    <a class="registrar-forms-toolbar__download" href="{{ route('registrar.registrar-menu.forms.uploads.download', ['registrarFormUpload' => $selectedUpload->id]) }}">Open current file</a>
                </div>
            </div>
        @else
            <div class="registrar-forms-toolbar__status-card registrar-forms-toolbar__status-card--empty">
                <div class="registrar-forms-toolbar__status-label">Current file</div>
                <div class="registrar-forms-toolbar__status-name">No uploaded version yet</div>
                <div class="registrar-forms-toolbar__status-meta">Upload a file to create the first version for the selected form.</div>
            </div>
        @endif
    </div>
</div>
