@extends('layouts.app')

@section('content')
<div class="container-fluid">
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