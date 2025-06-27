@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h5>Buat Laporan Verifikasi Pembuatan Larutan</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('report-solvents.store') }}" method="POST">
                @csrf

                {{-- HEADER --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label>Tanggal</label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label>Shift</label>
                        <input type="text" name="shift" class="form-control" value="{{ getShift() }}" required>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">No.</th>
                                <th rowspan="2" class="align-middle">Nama Bahan</th>
                                <th rowspan="2" class="align-middle">Kadar Yang Diinginkan</th>
                                <th colspan="2" class="align-middle">Verifikasi Formulasi</th>
                                <th rowspan="2" class="align-middle">Keterangan</th>
                                <th rowspan="2" class="align-middle">Hasil Verifikasi</th>
                                <th rowspan="2" class="align-middle">Tindakan Koreksi</th>
                                <th rowspan="2" class="align-middle">Verifikasi Setelah Tindakan Koreksi</th>
                            </tr>
                            <tr>
                                <th class="align-middle">Volume Bahan (mL)</th>
                                <th class="align-middle">Volume Larutan (mL)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($solventItems as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="text-start">
                                    {{ $item->name }}
                                    <input type="hidden" name="details[{{ $i }}][solvent_uuid]"
                                        value="{{ $item->uuid }}">
                                </td>
                                <td>{{ $item->concentration }}</td>
                                <td>{{ $item->volume_material }}</td>
                                <td>{{ $item->volume_solvent }}</td>
                                <td class="text-start">{{ $item->application_area }}</td>
                                <td>
                                    <input type="checkbox" name="details[{{ $i }}][verification_result]" value="1">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][corrective_action]"
                                        class="form-control form-control-sm">
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $i }}][reverification_action]"
                                        class="form-control form-control-sm">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection