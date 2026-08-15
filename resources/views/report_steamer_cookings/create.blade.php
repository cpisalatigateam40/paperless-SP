@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Proses Pemasakan di Steamer', 'url' => route('report_steamer_cookings.index')],
        ['label' => 'Tambah Data', 'url' => null],
    ]" />

    <div class="card shadow mb-4">
        <div class="card-header">
            <h4>Tambah Verifikasi Proses Pemasakan di Steamer</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('report_steamer_cookings.store') }}" method="POST" id="steamerCookingForm">
                @csrf
                @include('report_steamer_cookings._form')
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@include('report_steamer_cookings._form_script')
@endsection