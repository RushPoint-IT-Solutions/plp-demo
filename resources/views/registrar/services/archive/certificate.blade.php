@extends('layouts.registrar')

@section('title', 'PLP - Disposal Certificate')
@section('body-class', 'page-certificate')

@push('styles')
<style>
.certificate-page {
    background: #fff;
    padding: 40px;
    max-width: 800px;
    margin: 40px auto;
    border: 1px solid #ccc;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}
.certificate-header {
    text-align: center;
    border-bottom: 3px double #006837;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.certificate-logo {
    max-width: 120px;
    margin-bottom: 10px;
}
.certificate-title {
    font-size: 28px;
    color: #006837;
    margin: 10px 0;
    text-transform: uppercase;
    letter-spacing: 2px;
}
.certificate-subtitle {
    font-size: 14px;
    color: #666;
}
.certificate-body {
    margin-bottom: 30px;
}
.certificate-section {
    margin-bottom: 20px;
}
.certificate-section-title {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.certificate-text {
    font-size: 14px;
    color: #333;
    line-height: 1.6;
}
.certificate-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.certificate-item {
    border: 1px solid #e0e0e0;
    padding: 12px;
    border-radius: 4px;
}
.certificate-item-label {
    font-size: 10px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.certificate-item-value {
    font-size: 14px;
    color: #333;
    font-weight: 600;
}
.certificate-hash {
    background: #f5f5f5;
    padding: 16px;
    border-radius: 4px;
    margin: 20px 0;
}
.certificate-hash-label {
    font-size: 10px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.certificate-hash-value {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #333;
    word-break: break-all;
    line-height: 1.5;
}
.certificate-footer {
    border-top: 1px solid #e0e0e0;
    padding-top: 20px;
    margin-top: 30px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.certificate-signature {
    text-align: center;
    min-width: 200px;
}
.certificate-signature-line {
    border-top: 1px solid #333;
    margin-top: 40px;
    padding-top: 8px;
    font-size: 12px;
    color: #666;
}
.certificate-seal {
    text-align: center;
}
.certificate-seal-box {
    width: 100px;
    height: 100px;
    border: 2px solid #006837;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.certificate-seal-text {
    font-size: 10px;
    color: #006837;
    text-transform: uppercase;
    text-align: center;
}
.certificate-verify {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px dashed #e0e0e0;
}
.certificate-verify-text {
    font-size: 11px;
    color: #666;
}
.print-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #006837;
    color: #fff;
    border: none;
    padding: 12px 24px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.print-btn:hover {
    background: #005a2e;
}
@media print {
    .print-btn { display: none; }
    body { background: #fff; }
    .certificate-page { 
        margin: 0; 
        padding: 20px;
        box-shadow: none;
        max-width: 100%;
    }
}
</style>
@endpush

@section('content')
<div class="certificate-page">
    <div class="certificate-header">
        <img src="{{ asset('img/plplogo.png') }}" alt="PLP Logo" class="certificate-logo">
        <h1 class="certificate-title">Certificate of Disposal</h1>
        <p class="certificate-subtitle">Document Control and Record Management System</p>
    </div>

    <div class="certificate-body">
        <div class="certificate-section">
            <p class="certificate-text">
                This is to certify that the undermentioned document record has been disposed of in accordance with 
                the institution's Document Control and Record Management Policy, under the supervision of the Registrar's Office.
            </p>
        </div>

        <div class="certificate-grid">
            <div class="certificate-item">
                <div class="certificate-item-label">Certificate Number</div>
                <div class="certificate-item-value">DC-{{ str_pad($certificate->id, 8, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="certificate-item">
                <div class="certificate-item-label">Date of Disposal</div>
                <div class="certificate-item-value">{{ $certificate->disposed_at->format('F d, Y') }}</div>
            </div>
            <div class="certificate-item">
                <div class="certificate-item-label">Record Type</div>
                <div class="certificate-item-value">{{ $archive->record_type }}</div>
            </div>
            <div class="certificate-item">
                <div class="certificate-item-label">Record ID</div>
                <div class="certificate-item-value">{{ $archive->record_id }}</div>
            </div>
            <div class="certificate-item">
                <div class="certificate-item-label">Disposal Method</div>
                <div class="certificate-item-value">{{ ucfirst(str_replace('_', ' ', $certificate->disposal_method)) }}</div>
            </div>
            <div class="certificate-item">
                <div class="certificate-item-label">Disposed By</div>
                <div class="certificate-item-value">{{ $disposedBy->name ?? 'System' }}</div>
            </div>
        </div>

        @if($certificate->reason)
        <div class="certificate-section">
            <div class="certificate-section-title">Reason for Disposal</div>
            <p class="certificate-text">{{ $certificate->reason }}</p>
        </div>
        @endif

        <div class="certificate-hash">
            <div class="certificate-hash-label">SHA-256 Verification Hash</div>
            <div class="certificate-hash-value">{{ $certificate->verification_hash }}</div>
        </div>

        <div class="certificate-section">
            <div class="certificate-section-title">Verification</div>
            <p class="certificate-text">
                This certificate was generated automatically by the PLP Document Control System. 
                The SHA-256 hash above can be used to verify the authenticity of this disposal record. 
                Any tampering with this document will result in hash mismatch.
            </p>
        </div>
    </div>

    <div class="certificate-footer">
        <div class="certificate-signature">
            <div class="certificate-signature-line">
                {{ $disposedBy->name ?? 'System Administrator' }}<br>
                Registrar / Authorized Personnel
            </div>
        </div>
        <div class="certificate-seal">
            <div class="certificate-seal-box">
                <div class="certificate-seal-text">
                    OFFICIAL<br>
                    SEAL<br>
                    {{ $certificate->disposed_at->format('Y') }}
                </div>
            </div>
        </div>
        <div class="certificate-signature">
            <div class="certificate-signature-line">
                {{ $verifiedBy->name ?? 'System' }}<br>
                Verification Officer
            </div>
        </div>
    </div>

    <div class="certificate-verify">
        <p class="certificate-verify-text">
            This certificate was generated on {{ $certificate->created_at->format('F d, Y H:i:s') }} | 
            Archive ID: {{ $archive->id }} | 
            Certificate ID: {{ $certificate->id }}
        </p>
    </div>
</div>

<button type="button" class="print-btn" onclick="window.print()">Print Certificate</button>
@endsection