@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Edit Standard Stuffer</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('standard-stuffers.update', $stuffer->uuid) }}" method="POST">
                @csrf
                @method('PUT')

                @include('standard_stuffers._form', ['stuffer' => $stuffer])

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('standard-stuffers.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection