@php
    $inputId = isset($id) && trim((string) $id) !== '' ? (string) $id : 'rg-search-' . uniqid();
    $inputName = isset($name) ? trim((string) $name) : '';
    $inputValue = isset($value) ? (string) $value : '';
    $placeholderText = isset($placeholder) && trim((string) $placeholder) !== ''
        ? (string) $placeholder
        : 'Search';

    $containerClasses = trim('sf-search-box ' . (isset($containerClass) ? (string) $containerClass : ''));
    $inputClasses = trim('sf-search-input ' . (isset($inputClass) ? (string) $inputClass : ''));

    $extraAttributes = isset($inputAttributes) && is_array($inputAttributes)
        ? $inputAttributes
        : [];
@endphp

<div class="{{ $containerClasses }}">
    <input
        type="text"
        id="{{ $inputId }}"
        class="{{ $inputClasses }}"
        placeholder="{{ $placeholderText }}"
        value="{{ $inputValue }}"
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

    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sf-search-icon">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
</div>
