@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Tambah Standard Stuffer</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('standard-stuffers.store') }}" method="POST">
                @csrf

                @include('standard_stuffers._form', ['stuffer' => null])

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('standard-stuffers.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection