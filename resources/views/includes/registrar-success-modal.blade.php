@php
    $modalId = $id ?? 'successModal';
    $title = $title ?? 'SUCCESSFUL!';
    $message = $message ?? 'Operation completed successfully.';
    $msgId = $msgId ?? ($modalId . 'Msg');
    $btnText = $btnText ?? 'OK';
    $onClose = $onClose ?? '';
@endphp

<div class="req-modal-overlay is-hidden" id="{{ $modalId }}" style="display:none;" @if($onClose) onclick="if(event.target===this) {!! $onClose !!}" @endif>
    <div class="req-modal-box req-modal-success">
        <h3 class="req-modal-success-title">{{ $title }}</h3>
        <p class="req-modal-success-msg" id="{{ $msgId }}">{{ $message }}</p>
        <button type="button" class="req-btn-ok" @if($onClose) onclick="{!! $onClose !!}" @else onclick="document.getElementById('{{ $modalId }}').style.display='none'" @endif>{{ $btnText }}</button>
    </div>
</div>
