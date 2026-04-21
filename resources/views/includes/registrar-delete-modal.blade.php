@php
    $modalId = $id ?? 'premiumDeleteModal';
    $title = $title ?? 'Confirm Delete';
    $message = $message ?? 'Are you sure you want to delete this record?';
    $confirmBtnId = $confirmBtnId ?? 'confirmDeleteBtn';
    $confirmBtnText = $confirmBtnText ?? 'Delete';
    $cancelAction = $cancelAction ?? '';
    $confirmAction = $confirmAction ?? '';
    $detailId = $detailId ?? ($modalId . 'Detail');
    $confirmActionAttr = $confirmActionAttr ?? '';
    $cancelActionAttr = $cancelActionAttr ?? '';
    $dataModal = $dataModal ?? '';
@endphp

<div class="req-modal-overlay cfg-modal-overlay is-hidden" id="{{ $modalId }}" @if($dataModal) data-cfg-modal="{{ $dataModal }}" @endif @if($cancelAction) onclick="if(event.target===this) {!! $cancelAction !!}" @endif>
    <div class="req-modal-box cfg-delete-box">
        <h3 class="cfg-delete-title">{{ $title }}</h3>
        <p class="cfg-delete-text text-center">{{ $message }}</p>
        <div id="{{ $detailId }}" style="font-weight: 600; color: #334; margin-bottom: 20px; font-size: 0.9rem;"></div>
        <div class="req-modal-actions cfg-modal-actions">
            <button type="button" class="cfg-btn-secondary" {!! $cancelActionAttr !!} @if($cancelAction) onclick="{!! $cancelAction !!}" @else data-cfg-action="close-modal" data-cfg-modal-target="{{ $modalId }}" @endif>Cancel</button>
            <button type="button" class="cfg-btn-danger" id="{{ $confirmBtnId }}" {!! $confirmActionAttr !!} @if($confirmAction) onclick="{!! $confirmAction !!}" @endif>{{ $confirmBtnText }}</button>
        </div>
    </div>
</div>
