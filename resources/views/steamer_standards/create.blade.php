@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-3">Tambah Standar Steamer</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('steamer-standards.store') }}" method="POST">
                @csrf
                @include('steamer_standards._form')
            </form>
        </div>
    </div>
</div>
@endsection