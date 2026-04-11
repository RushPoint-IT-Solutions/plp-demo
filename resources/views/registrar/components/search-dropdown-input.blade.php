@php
    $inputId = isset($id) && trim((string) $id) !== '' ? (string) $id : 'rg-search-dropdown-' . uniqid();
    $dropdownElementId = isset($dropdownId) && trim((string) $dropdownId) !== ''
        ? (string) $dropdownId
        : $inputId . 'Dropdown';

    $inputName = isset($name) ? trim((string) $name) : '';
    $inputType = isset($type) && trim((string) $type) !== '' ? (string) $type : 'text';
    $inputValue = isset($value) ? (string) $value : '';
    $placeholderText = isset($placeholder) ? (string) $placeholder : '';
    $autoComplete = isset($autocomplete) ? (string) $autocomplete : 'off';

    $wrapperClasses = trim('smrg-search-wrap ' . (isset($wrapperClass) ? (string) $wrapperClass : ''));
    $inputClasses = trim('req-modal-input smrg-search-input ' . (isset($inputClass) ? (string) $inputClass : ''));
    $dropdownClasses = trim('smrg-search-dropdown ' . (isset($dropdownClass) ? (string) $dropdownClass : ''));

    $extraAttributes = isset($inputAttributes) && is_array($inputAttributes)
        ? $inputAttributes
        : [];
@endphp

<div class="{{ $wrapperClasses }}">
    <input
        id="{{ $inputId }}"
        type="{{ $inputType }}"
        class="{{ $inputClasses }}"
        placeholder="{{ $placeholderText }}"
        value="{{ $inputValue }}"
        autocomplete="{{ $autoComplete }}"
        @if($inputName !== '')
            name="{{ $inputName }}"
        @endif
        @foreach($extraAttributes as $attributeKey => $attributeValue)
            @if(is_bool($attributeValue))
                @if($attributeValue)
                    {{ $attributeKey }}
                @endif
            @else
                {{ $attributeKey }}="{{ $attributeValue }}"
            @endif
        @endforeach
    >
    <div class="{{ $dropdownClasses }}" id="{{ $dropdownElementId }}"></div>
</div>
