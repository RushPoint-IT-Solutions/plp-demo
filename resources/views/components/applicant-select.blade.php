@php
    $fieldId = isset($id) && trim((string) $id) !== '' ? (string) $id : 'applicant-select-' . uniqid();
    $fieldName = isset($name) ? (string) $name : $fieldId;
    $placeholderText = isset($placeholder) ? (string) $placeholder : 'Select option';
    $selectedValue = isset($selected) ? (string) $selected : '';
    $required = !empty($required) ? 'required' : '';
    $disabled = !empty($disabled) ? 'disabled' : '';
    $wrapperClass = isset($wrapperClass) ? (string) $wrapperClass : '';
    $inputClass = isset($inputClass) ? (string) $inputClass : '';

    $normalizedOptions = collect($options ?? [])->map(function ($option) {
        if (is_array($option)) {
            return [
                'value' => (string) ($option['value'] ?? ''),
                'label' => (string) ($option['label'] ?? ($option['value'] ?? '')),
            ];
        }

        return [
            'value' => (string) $option,
            'label' => (string) $option,
        ];
    })->values();

    $selectedLabel = $placeholderText;
    foreach ($normalizedOptions as $option) {
        if ((string) $option['value'] === $selectedValue) {
            $selectedLabel = (string) $option['label'];
            break;
        }
    }
@endphp

<div class="applicant-select-wrap {{ $wrapperClass }}" data-applicant-select>
    <select id="{{ $fieldId }}" name="{{ $fieldName }}" class="applicant-select-native {{ $inputClass }}" {{ $required }} {{ $disabled }} data-placeholder="{{ $placeholderText }}">
        <option value="">{{ $placeholderText }}</option>
        @foreach($normalizedOptions as $option)
            <option value="{{ $option['value'] }}" {{ (string) $option['value'] === $selectedValue ? 'selected' : '' }}>{{ $option['label'] }}</option>
        @endforeach
    </select>

    <button type="button" class="applicant-select-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false" {{ $disabled ? 'disabled' : '' }}>
        <span class="applicant-select-trigger-text" data-select-current>{{ $selectedLabel }}</span>
        <span class="applicant-select-trigger-caret" aria-hidden="true"></span>
    </button>

    <ul class="applicant-select-menu" data-select-menu role="listbox" tabindex="-1"></ul>
</div>
