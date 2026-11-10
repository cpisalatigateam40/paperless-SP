@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber', 'url' => route('report_pasteurs.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Tambah Verifikasi Proses Pasteurisasi Produk di Retort Chamber</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_pasteurs.store') }}" method="POST" id="pasteur-form">
                @include('report_pasteurs._form', ['report' => null])
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
    @include('report_pasteurs._form_scripts')
@endsection