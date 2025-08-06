@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Standard Maurer</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('maurer-standards.store') }}" method="POST">
                @csrf

                @include('maurer-standards._form', ['standard' => null])

                <button type="button" class="btn btn-outline-primary" id="add-step">+ Tambah Step</button>

                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('maurer-standards.index') }}" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let stepIndex = 1;

document.getElementById('add-step').addEventListener('click', function() {
    const container = document.getElementById('steps-container');
    const newStep = container.firstElementChild.cloneNode(true);

    // Ubah semua input name agar jadi steps[1], steps[2], dst
    newStep.querySelectorAll('input, select').forEach(function(el) {
        if (el.name) {
            el.name = el.name.replace(/\[\d+\]/, `[${stepIndex}]`);
            el.value = '';
        }
    });

    container.appendChild(newStep);
    stepIndex++;
});

// Tombol hapus step
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-step')) {
        const stepGroup = e.target.closest('.step-group');
        const container = document.getElementById('steps-container');
        if (container.children.length > 1) {
            stepGroup.remove();
        }
    }
});
</script>
@endsection