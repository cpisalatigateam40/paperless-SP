@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <x-breadcrumb :items="[
        ['label' => 'Verifikasi Penerapan GMP Karyawan & Sanitasi Area', 'url' => route('gmp.index')],
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