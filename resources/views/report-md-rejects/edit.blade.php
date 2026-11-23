@extends('layouts.app')

@section('content')
<div class="container-fluid">

    @if ($report->is_audit)
        <x-audit-banner />
    @endif
    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0">Edit Pemeriksaan Produk Reject Metal Detector</h5>
        </div>
        <div class="card-body">
            @include('report-md-rejects._form', [
                'action' => route('report_md_rejects.update', $report->uuid),
                'report' => $report,
            ])
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('report-md-rejects._script', ['report' => $report])
@endsection