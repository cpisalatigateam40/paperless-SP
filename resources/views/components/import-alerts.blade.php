@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning">
        <strong>Detail baris gagal:</strong>
        <ul class="mb-0" style="max-height: 10rem; overflow-y: auto;">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif