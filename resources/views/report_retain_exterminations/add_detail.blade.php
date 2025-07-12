@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4>Tambah Detail ke Report Tanggal {{ $report->date }} (Shift {{ $report->shift }})</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('report_retain_exterminations.store-detail', $report->uuid) }}">
                @csrf
                <table class="table table-bordered">
                    <thead class="text-center">
                        <tr>
                            <th>Nama Retain</th>
                            <th>Exp Date</th>
                            <th>Kondisi</th>
                            <th>Bentuk</th>
                            <th>Jumlah</th>
                            <th>Jumlah Kg</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="retain_name" class="form-control" required></td>
                            <td><input type="date" name="exp_date" class="form-control" required></td>
                            <td><input type="text" name="retain_condition" class="form-control" required></td>
                            <td>
                                <select name="shape" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="Box">Box</option>
                                    <option value="Karung">Karung</option>
                                    <option value="Plastik">Plastik</option>
                                </select>
                            </td>
                            <td><input type="number" name="quantity" class="form-control" required></td>
                            <td><input type="number" step="0.01" name="quantity_kg" class="form-control" required></td>
                            <td><input type="text" name="notes" class="form-control"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="mt-3">
                    <button class="btn btn-primary">Simpan Detail</button>
                    <a href="{{ route('report_retain_exterminations.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection