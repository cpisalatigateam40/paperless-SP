@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan', 'url' => route('report-re-cleanliness.index')],
        ['label' => 'Edit Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header"><h4 class="mb-0">Edit Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan</h4></div>
        <div class="card-body">
            @include('report_re_cleanliness._form', [
                'action'      => route('report-re-cleanliness.update', $report->uuid),
                'method'      => 'PUT',
                'report'      => $report,
                'sessionTime' => $sessionTime,
            ])
        </div>
    </div>
</div>
@endsection