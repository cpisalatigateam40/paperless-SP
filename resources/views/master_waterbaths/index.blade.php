@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Master Standar Waterbath</h4>
            <a href="{{ route('master_waterbaths.create') }}" class="btn btn-primary">
                Tambah Master
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Initial Temp (°C)</th>
                            <th>Parameter Terisi</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($masters as $master)
                        <tr>
                            <td class="text-center">{{ $masters->firstItem() + $loop->index }}</td>
                            <td>{{ $master->product->product_name ?? '-' }}</td>
                            <td class="text-center">{{ $master->initial_temp_label }}</td>
                            <td class="text-center">
                                {{ $master->parameters->whereNotNull('set_time')->count() }} / {{ count(\App\Models\MasterWaterbathParameter::PARAMETERS) }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex" style="gap: .4rem;">
                                    <button type="button" class="btn btn-info btn-sm btn-toggle-detail"
                                        data-bs-toggle="collapse" data-bs-target="#detail-{{ $master->uuid }}"
                                        aria-expanded="false" title="Lihat detail">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <a href="{{ route('master_waterbaths.edit', $master->uuid) }}" class="btn btn-warning btn-sm">Edit</a>

                                    <form action="{{ route('master_waterbaths.destroy', $master->uuid) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Hapus master ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Baris detail (collapse) --}}
                        <tr>
                            <td colspan="5" class="p-0 border-0">
                                <div class="collapse" id="detail-{{ $master->uuid }}">
                                    <div class="p-3 bg-light">
                                        <div class="mb-2">
                                            <strong>Initial Temp:</strong> {{ $master->initial_temp_label }} °C
                                        </div>

                                        <table class="table table-bordered table-sm bg-white mb-0">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>Parameter</th>
                                                    <th>Set Temp (°C)</th>
                                                    <th>Set Time (menit)</th>
                                                    <th>Std CT Prod. (°C)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(\App\Models\MasterWaterbathParameter::PARAMETERS as $key => $label)
                                                    @php $p = $master->parameter($key); @endphp
                                                    <tr>
                                                        <td>{{ $label }}</td>
                                                        <td class="text-center">
                                                            @if($key === 'drying')
                                                                Hot zone: {{ $p?->set_temp_range ?? '-' }}<br>
                                                                Cold zone: {{ $p?->cold_zone_range ?? '-' }}
                                                            @else
                                                                {{ $p?->set_temp_range ?? '-' }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center">{{ \App\Models\MasterWaterbathParameter::fmt($p?->set_time) ?? '-' }}</td>
                                                        <td class="text-center">{{ $p?->std_ct_range ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada master.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $masters->links() }}
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.querySelectorAll('.collapse[id^="detail-"]').forEach(function (el) {
    const icon = document.querySelector('[data-bs-target="#' + el.id + '"] i');

    el.addEventListener('show.bs.collapse', () => icon.classList.replace('fa-eye', 'fa-eye-slash'));
    el.addEventListener('hide.bs.collapse', () => icon.classList.replace('fa-eye-slash', 'fa-eye'));
});
</script>
@endsection