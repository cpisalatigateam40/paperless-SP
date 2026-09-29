@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">Edit Nama Formula</h4>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('formulas.updateName', $formula->uuid) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label for="formula_name">
                            Nama Formula
                        </label>

                        <input type="text" name="formula_name" id="formula_name" class="form-control"
                            value="{{ old('formula_name', $formula->formula_name) }}" required maxlength="255">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('formulas.index') }}" class="btn btn-secondary">
                            Kembali
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection