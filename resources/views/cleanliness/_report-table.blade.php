<table class="table table-bordered">
    <thead class="thead-light">
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

                    <form action="{{ route('cleanliness.destroy', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>

                    <a href="{{ route('cleanliness.export.pdf', $report->uuid) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        🖨 Cetak PDF
                    </a>

                    @can('approve report')
                        @if(!$report->approved_by)
                            <form action="{{ route('cleanliness.approve', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Setujui laporan ini?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                        @else
                            <span class="badge bg-success" style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                Disetujui oleh {{ $report->approved_by }}
                            </span>
                        @endif
                    @else
                        @if($report->approved_by)
                            <span class="badge bg-success" style="color: white; border-radius: 1rem; padding-inline: .8rem; padding-block: .3rem;">
                                Disetujui oleh {{ $report->approved_by }}
                            </span>
                        @endif
                    @endcan
                </td>
            </tr>

            <tr class="collapse" id="detail-{{ $report->id }}">
                <td colspan="5">
                    <strong>Area</strong> {{ $report->area ->name }} <br>
                    @if($report->approved_by)
                        <div class="mb-4">
                            <strong>Disetujui oleh:</strong> {{ $report->approved_by }}
                        </div>
                    @endif
                    @foreach($report->details as $detail)
                        <div class="mb-3 mt-3">
                            <strong>Jam Inspeksi:</strong> {{ $detail->inspection_hour }}
                            <table class="table table-sm table-striped mt-2">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Item</th>
                                        <th>Kondisi</th>
                                        <th>Catatan</th>
                                        <th>Tindakan Koreksi</th>
                                        <th>Verifikasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($detail->items as $i => $item)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $item->item }}</td>
                                            <td>{{ $item->condition }}</td>
                                            <td>{{ $item->notes }}</td>
                                            <td>{{ $item->corrective_action }}</td>
                                            <td>{{ $item->verification ? '✔' : '✘' }}</td>
                                        </tr>
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
            <tr><td colspan="5">Tidak ada laporan pada ruangan ini.</td></tr>
        @endforelse
    </tbody>
</table>


@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toggle-detail').forEach(function (btn) {
            btn.addEventListener('click', function () {
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
