@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-4">Tambah Detail untuk Report tanggal {{ $report->date }}</h4>
        </div>

        <div class="card-body">
            <form action="{{ route('report_stuffers.store-detail', $report->uuid) }}" method="POST">
                @csrf

                <h5>Rekap Stuffer</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th rowspan="2">No</th>
                            <th rowspan="2">Produk</th>
                            <th rowspan="2">Standar Berat (gram)</th>
                            <th colspan="2">Hitech</th>
                            <th colspan="2">Townsend</th>
                            <th rowspan="2">Keterangan</th>
                        </tr>
                        <tr>
                            <th>Range</th>
                            <th>Avg</th>
                            <th>Range</th>
                            <th>Avg</th>
                        </tr>
                    </thead>
                    <tbody id="rekap-stuffer-body">
                        <tr>
                            <td class="index">1</td>
                            <td>
                                <select name="detail_stuffers[0][product_uuid]" class="form-control">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="detail_stuffers[0][standard_weight]" class="form-control"
                                    step="0.01"></td>
                            <td><input type="number" name="detail_stuffers[0][hitech_range]" class="form-control"
                                    step="0.01"></td>
                            <td><input type="number" name="detail_stuffers[0][hitech_avg]" class="form-control"
                                    step="0.01"></td>
                            <td><input type="number" name="detail_stuffers[0][townsend_range]" class="form-control"
                                    step="0.01"></td>
                            <td><input type="number" name="detail_stuffers[0][townsend_avg]" class="form-control"
                                    step="0.01"></td>
                            <td><input type="text" name="detail_stuffers[0][note]" class="form-control"></td>
                        </tr>
                    </tbody>
                </table>

                <hr>
                <h5>Cooking Loss</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>% Cooking Loss Fessmann</th>
                            <th>% Cooking Loss Maurer</th>
                        </tr>
                    </thead>
                    <tbody id="cooking-loss-body">
                        <tr>
                            <td class="index">1</td>
                            <td>
                                <select name="cooking_loss_stuffers[0][product_uuid]" class="form-control">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($products as $product)
                                    <option value="{{ $product->uuid }}">{{ $product->product_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="cooking_loss_stuffers[0][fessmann]" class="form-control"
                                    step="0.01"></td>
                            <td><input type="number" name="cooking_loss_stuffers[0][maurer]" class="form-control"
                                    step="0.01"></td>
                        </tr>
                    </tbody>
                </table>

                <button type="submit" class="btn btn-primary mt-3">Simpan Detail</button>
            </form>
        </div>
    </div>
</div>
@endsection