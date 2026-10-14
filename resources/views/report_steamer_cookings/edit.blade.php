@extends('layouts.app')

@php
    $auditMode = $report->is_audit || request()->boolean('audit');
@endphp

@section('content')
<div class="container-fluid">
    @if ($report->is_audit)
        <x-audit-banner />
    @endif
    
    <x-breadcrumb :items="[
        [
            'label' => $auditMode ? 'Verifikasi Proses Pemasakan di Steamer (Audit)' : 'Verifikasi Proses Pemasakan di Steamer',
            'url'   => $auditMode ? route('report_steamer_cookings.audit') : route('report_steamer_cookings.index'),
        ],

        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Edit Verifikasi Proses Pemasakan di Steamer</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_steamer_cookings.update', $report->uuid) }}" method="POST"
                id="steamerCookingForm">
                @csrf
                @method('PUT')
                @include('report_steamer_cookings._form')
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@include('report_steamer_cookings._form_script')
@endsection