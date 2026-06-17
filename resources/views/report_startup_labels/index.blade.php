@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Laporan Startup Label</h4>

            <div class="d-flex gap-2">
                @can('create report')
                <a href="{{ route('report_startup_labels.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Tambah Laporan
                </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
            <div id="success-alert" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div id="error-alert" class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Tanggal</th>
                            <th class="align-middle">Shift</th>
                            <th class="align-middle">Area</th>
                            <th class="align-middle">Dibuat Oleh</th>
                            <th class="align-middle">Diketahui Oleh</th>
                            <th class="align-middle">Disetujui Oleh</th>
                            <th class="align-middle text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $i => $report)
                        <tr>
                            <td class="align-middle">{{ $i + $reports->firstItem() }}</td>
                            <td class="align-middle">{{ $report->date ? $report->date->format('d-m-Y') : '-' }}</td>
                            <td class="align-middle">{{ $report->shift ?? '-' }}</td>
                            <td class="align-middle">{{ $report->area->name ?? '-' }}</td>
                            <td class="align-middle">{{ $report->created_by ?? '-' }}</td>
                            <td class="align-middle">
                                @if($report->known_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    <i class="fas fa-check"></i> {{ $report->known_by }}
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="align-middle">
                                @if($report->approved_by)
                                <span class="badge bg-success"
                                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                                </span>
                                @else
                                -
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                {{-- Toggle Detail --}}
                                <button class="btn btn-sm btn-info toggle-detail"
                                    data-target="#detail-{{ $report->id }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @can('edit report')
                                <a href="{{ route('report_startup_labels.edit', $report->uuid) }}"
                                    class="btn btn-sm btn-warning" title="Edit Laporan">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan

                                @can('delete report')
                                <form action="{{ route('report_startup_labels.destroy', $report->uuid) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Yakin hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>

                        <tr id="detail-{{ $report->id }}" class="d-none">
                            <td colspan="8">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="align-middle">Produk</th>
                                            <th class="align-middle">Waktu</th>
                                            <th class="align-middle">Kode Produksi</th>
                                            <th class="align-middle">Best Before</th>
                                            <th class="align-middle">Hasil</th>
                                            <th class="align-middle">Tindakan Koreksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report->details as $detail)
                                        <tr>
                                            <td class="align-middle">{{ $detail->product->name ?? '-' }}</td>
                                            <td class="align-middle text-center">
                                                {{ $detail->time ? \Illuminate\Support\Str::substr($detail->time, 0, 5) : '-' }}
                                            </td>
                                            <td class="align-middle">{{ $detail->production_code ?? '-' }}</td>
                                            <td class="align-middle">
                                                {{ $detail->best_before ? $detail->best_before->format('d-m-Y') : '-' }}
                                            </td>
                                            <td class="align-middle text-center">{{ $detail->result ?? '-' }}</td>
                                            <td class="align-middle">{{ $detail->corrective_action ?? '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada detail</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada laporan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $reports->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);

    $('.toggle-detail').on('click', function() {
        const target = $(this.dataset.target);
        const isHidden = target.hasClass('d-none');

        $('tr[id^="detail-"]').addClass('d-none');

        if (isHidden) {
            target.removeClass('d-none');
        } else {
            target.addClass('d-none');
        }
    });
});
</script>
@endsection