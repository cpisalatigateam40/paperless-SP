@extends('layouts.app')

@php
    $auditMode = $gmpHeader->is_audit || request()->boolean('audit');
@endphp

@section('content')
<div class="container-fluid">
    @if ($gmpHeader->is_audit)
        <x-audit-banner />
    @endif

    <x-breadcrumb :items="[
        [
            'label' => $auditMode ? 'Verifikasi Penerapan GMP Karyawan & Sanitasi Area (Audit)' : 'Verifikasi Penerapan GMP Karyawan & Sanitasi Area',
            'url'   => $auditMode ? route('gmp.audit') : route('gmp.index'),
        ],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    @include('gmp._form', [
        'gmpHeader' => $gmpHeader,
        'formAction' => route('gmp.update', $gmpHeader),
        'formMethod' => 'PUT',
        'sections' => $sections,
        'sanitationItemList' => $sanitationItemList,
    ])
</div>
@endsection