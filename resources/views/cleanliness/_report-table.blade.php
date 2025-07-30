<table class="table table-bordered">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Shift</th>
            <th>Ruangan</th>
            <th>Dibuat Oleh</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filteredReports as $report)
        <tr>
            <td>{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
            <td>{{ $report->shift }}</td>
            <td>{{ $report->room_name }}</td>
            <td>{{ $report->created_by }}</td>
            <td>
                <button class="btn btn-sm btn-info toggle-detail" data-target="#detail-{{ $report->id }}">
                    Lihat Detail
                </button>

                <form action="{{ route('cleanliness.destroy', $report->id) }}" method="POST"
                    style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>



                @can('known report')
                @if(!$report->known_by)
                <form action="{{ route('cleanliness.known', $report->id) }}" method="POST" style="display:inline-block;"
                    onsubmit="return confirm('Ketahui laporan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success">Diketahui</button>
                </form>
                @else
                <span class="badge bg-success"
                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                    Diketahui oleh {{ $report->known_by }}
                </span>
                @endif
                @else
                @if($report->known_by)
                <span class="badge bg-success"
                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                    Diketahui oleh {{ $report->known_by }}
                </span>
                @endif
                @endcan

                @can('approve report')
                @if(!$report->approved_by)
                <form action="{{ route('cleanliness.approve', $report->id) }}" method="POST"
                    style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                </form>
                @else
                <span class="badge bg-success"
                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                    Disetujui oleh {{ $report->approved_by }}
                </span>
                @endif
                @else
                @if($report->approved_by)
                <span class="badge bg-success"
                    style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                    Disetujui oleh {{ $report->approved_by }}
                </span>
                @endif
                @endcan

                <a href="{{ route('cleanliness.export.pdf', $report->uuid) }}" target="_blank"
                    class="btn btn-sm btn-outline-secondary">
                    🖨 Cetak PDF
                </a>
            </td>
        </tr>

        <tr class="collapse" id="detail-{{ $report->id }}">
            <td colspan="6">
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
                                <td class="align-middle">{{ $i + 1 }}</td>
                                <td class="align-middle">{{ $item->item }}</td>
                                <td class="align-middle">{{ $item->condition }}</td>
                                <td class="align-middle">
                                    @php
                                    $notes = json_decode($item->notes, true);
                                    @endphp
                                    @if(is_array($notes))
                                    {{ implode(', ', $notes) }}
                                    @else
                                    {{ $item->notes }}
                                    @endif
                                </td>
                                <td class="align-middle">{{ $item->corrective_action }}</td>
                                <td class="align-middle">{!! $item->verification ? '✔' : '✘' !!}</td>
                            </tr>

                            @foreach($item->followups as $index => $followup)
                            <tr class="table-secondary">
                                <td></td>
                                <td colspan="2" class="align-middle">↳ Koreksi Lanjutan #{{ $index + 1 }}</td>
                                <td class="align-middle">{{ $followup->notes }}</td>
                                <td class="align-middle">{{ $followup->corrective_action }}</td>
                                <td class="align-middle">{!! $followup->verification ? '✔' : '✘' !!}</td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach

                <div class="d-flex justify-content-end">
                    <a href="{{ route('cleanliness.detail.create', $report->id) }}" class="btn btn-sm btn-primary mt-3">
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
});

$(document).ready(function() {
    setTimeout(() => {
        $('#success-alert').fadeOut('slow');
        $('#error-alert').fadeOut('slow');
    }, 3000);
});
</script>
@endsection