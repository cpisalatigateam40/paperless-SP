@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header">
            <h4 class="mb-0">Edit Master Standar Waterbath</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('master_waterbaths.update', $master->uuid) }}" method="POST">
                @method('PUT')
                @include('master_waterbaths._form')
            </form>
        </div>
    </div>
</div>
@endsection