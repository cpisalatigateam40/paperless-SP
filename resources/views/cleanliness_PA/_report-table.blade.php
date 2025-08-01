<table class="table table-bordered">
    <thead class="thead-light">
        <tr>
            <th>Tanggal</th>
            <th>Shift</th>
            <th>Area</th>
            <th>Dibuat Oleh</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filteredReports as $report)
        <tr>
            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
            <td>{{ $report->shift }}</td>
            <td>{{ $report->section_name }}</td>
            <td>{{ $report->created_by }}</td>
            <td class="d-flex" style="gap: .3rem;">
                {{-- Lihat Detail --}}
                <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}"
                    title="Lihat Detail">
                    <i class="fas fa-eye"></i>
                </button>

                {{-- Hapus --}}
                <form action="{{ route('process-area-cleanliness.destroy', $report->id) }}" method="POST"
                    style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>

                {{-- Known --}}
                @can('known report')
                @if(!$report->known_by)
                <form action="{{ route('process-area-cleanliness.known', $report->id) }}" method="POST"
                    style="display:inline-block;" onsubmit="return confirm('Ketahui laporan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success" title="Diketahui">
                        <i class="fas fa-eye"></i>
                    </button>
                </form>
                @else
                <span class="badge bg-success text-white rounded-pill px-3 py-1" title="Diketahui oleh">
                    <i class="fas fa-user-check"></i> {{ $report->known_by }}
                </span>
                @endif
                @else
                @if($report->known_by)
                <span class="badge bg-success text-white rounded-pill px-3 py-1" title="Diketahui oleh">
                    <i class="fas fa-user-check"></i> {{ $report->known_by }}
                </span>
                @endif
                @endcan

                {{-- Approve --}}
                @can('approve report')
                @if(!$report->approved_by)
                <form action="{{ route('process-area-cleanliness.approve', $report->id) }}" method="POST"
                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                        <i class="fas fa-thumbs-up"></i>
                    </button>
                </form>
                @else
                <span class="badge bg-success text-white rounded-pill px-3 py-1" title="Disetujui oleh">
                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                </span>
                @endif
                @else
                @if($report->approved_by)
                <span class="badge bg-success text-white rounded-pill px-3 py-1" title="Disetujui oleh">
                    <i class="fas fa-check"></i> {{ $report->approved_by }}
                </span>
                @endif
                @endcan

                {{-- Export PDF --}}
                <a href="{{ route('process-area-cleanliness.export.pdf', $report->uuid) }}" target="_blank"
                    class="btn btn-sm btn-outline-secondary" title="Cetak PDF">
                    <i class="fas fa-file-pdf"></i>
                </a>
            </td>

        </tr>

        <tr class="collapse" id="detail-{{ $report->id }}">
            <td colspan="5">
                <strong>Area:</strong> {{ $report->area->name ?? '-' }} <br>

                @if($report->approved_by)
                <div class="mb-2"><strong>Disetujui oleh:</strong> {{ $report->approved_by }}</div>
                @endif

                @foreach($report->details as $detail)
                <div class="mb-3 mt-3">
                    <strong>Jam Inspeksi:</strong> {{ $detail->inspection_hour }}
                    <table class="table table-sm mt-2">
                        <thead>
                            <tr>
                                <th class="align-middle">No</th>
                                <th class="align-middle">Item</th>
                                <th class="align-middle">Kondisi</th>
                                <th class="align-middle">Catatan</th>
                                <th class="align-middle">Tindakan Koreksi</th>
                                <th class="align-middle">Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detail->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->item }}</td>
                                <td>
                                    @if(Str::contains($item->item, 'Suhu ruang'))
                                    Actual: {{ $item->temperature_actual ?? '-' }} ℃ <br>
                                    Display: {{ $item->temperature_display ?? '-' }} ℃
                                    @else
                                    {{ $item->condition ?? '-' }}
                                    @endif
                                </td>
                                <td>{{ $item->notes }}</td>
                                <td>{{ $item->corrective_action }}</td>
                                <td>{!! $item->verification ? '✔' : '✘' !!}</td>
                            </tr>

                            @foreach($item->followups as $index => $followup)
                            <tr class="table-secondary">
                                <td></td>
                                <td colspan="2">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                <td>{{ $followup->notes }}</td>
                                <td>{{ $followup->action }}</td>
                                <td>{!! $followup->verification ? '✔' : '✘' !!}</td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach

                <div class="d-flex justify-content-end">
                    <a href="{{ route('process-area-cleanliness.detail.create', $report->id) }}"
                        class="btn btn-sm btn-primary mt-3">
                        + Tambah Detail Inspeksi
                    </a>
                </div>
            </td>
        </tr>

        @empty
        <tr>
            <td colspan="5">Tidak ada laporan pada ruangan ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-detail').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.querySelector(targetId);
            target.classList.toggle('show');
        });
    });

    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection