@php
    $fieldId = isset($id) && trim((string) $id) !== '' ? (string) $id : 'rg-listbox-' . uniqid();
    $fieldName = isset($name) ? (string) $name : $fieldId;
    $placeholderText = isset($placeholder) ? (string) $placeholder : 'Select option';
    $selectedValue = isset($selected) ? (string) $selected : '';

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

<div class="rg-listbox" data-listbox-select>
    <select id="{{ $fieldId }}" name="{{ $fieldName }}" class="app-filter-select rg-listbox-native js-rg-listbox-native" data-placeholder="{{ $placeholderText }}">
        @foreach($normalizedOptions as $option)
            <option value="{{ $option['value'] }}" {{ (string) $option['value'] === $selectedValue ? 'selected' : '' }}>{{ $option['label'] }}</option>
        @endforeach
    </select>

    <button type="button" class="rg-listbox-trigger" data-select-trigger aria-haspopup="listbox" aria-expanded="false">
        <span class="rg-listbox-trigger-text" data-select-current>{{ $selectedLabel }}</span>
        <span class="rg-listbox-trigger-caret" aria-hidden="true"></span>
    </button>

    <ul class="rg-listbox-menu" data-select-menu role="listbox" tabindex="-1"></ul>
</div>
