@extends('layouts.app') {{-- atau sesuaikan layout --}}
@section('content')

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
             <h5>Daftar Laporan Kebersihan Ruang Penyimpanan</h5>
             <a href="{{ route('cleanliness.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
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

                <table class="table table-bordered table-hover">
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
                    @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->shift }}</td>
                            <td>{{ $report->room_name }}</td>
                            <td>{{ $report->created_by }}</td>
                            <td>
                                {{-- <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#detail-{{ $report->id }}">Lihat Detail</button> --}}
                            </td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $report->id }}">
                            <td colspan="5">
                                @foreach($report->details as $detail)
                                    <div class="mb-2">
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
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    {{ $reports->links() }}
</div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
        setTimeout(() => {
            $('#success-alert').fadeOut('slow');
            $('#error-alert').fadeOut('slow');
        }, 3000);
    });
    </script>
@endsection
