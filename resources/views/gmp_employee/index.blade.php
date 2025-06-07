@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Daftar Laporan Kontrol Sanitasi</h5>
                <a href="{{ route('gmp-employee.create') }}" class="btn btn-primary btn-sm">Tambah Laporan</a>
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                {{-- <th>Area</th> --}}
                                <th>Dibuat oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $index => $report)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $report->date }}</td>
                                    <td>{{ $report->shift }}</td>
                                    {{-- <td> {{ $report->details->first()->section_name ?? '-' }}</td> --}}
                                    <td>{{ $report->created_by }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#detail-{{ $report->id }}">
                                            Lihat Detail
                                        </button>

                                        <form action="{{ route('gmp-employee.destroy', $report->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>


                                {{-- Detail Row --}}
                                <tr class="collapse" id="detail-{{ $report->id }}">
                                    <td colspan="6">
                                        <strong>Area:</strong> {{ $report->area->name ?? '-' }}<br><br>

                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Jam Inspeksi</th>
                                                    <th>Bagian</th>
                                                    <th>Nama Karyawan</th>
                                                    <th>Catatan</th>
                                                    <th>Tindakan Koreksi</th>
                                                    <th>Verifikasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report->details as $i => $detail)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $detail->inspection_hour }}</td>
                                                        <td>{{ $detail->section_name }}</td>
                                                        <td>{{ $detail->employee_name }}</td>
                                                        <td>{{ $detail->notes }}</td>
                                                        <td>{{ $detail->corrective_action }}</td>
                                                        <td>{{ $detail->verification ? '✔' : '✘' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <div class="d-flex justify-content-end">
                                            <a href="{{ route('gmp-employee.detail.create', $report->id) }}" class="btn btn-sm btn-primary mt-3">+ Tambah Detail</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Belum ada laporan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
    });
    </script>
@endsection