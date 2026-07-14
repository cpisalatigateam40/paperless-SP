@extends('layouts.app')

@section('title','Edit Master Smoke House')

@section('content')

<form
    action="{{ route('master-smoke-houses.update',$master->uuid) }}"
    method="POST">

    @csrf
    @method('PUT')

    @include('master-smoke-houses.form')

</form>

@endsection