<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            {{ isset($master) ? 'Edit Master Smoke House' : 'Tambah Master Smoke House' }}
        </h5>
    </div>

    <div class="card-body">

        <div class="row mb-4">

            <div class="col-md-6">
                <label class="form-label">Product <span class="text-danger">*</span></label>

                <select name="product_uuid" class="form-select form-control" required>

                    <option value="">-- Pilih Product --</option>

                    @foreach($products as $product)

                        <option
                            value="{{ $product->uuid }}"
                            {{ old('product_uuid', $master->product_uuid ?? '') == $product->uuid ? 'selected' : '' }}>

                            {{ $product->product_name }}

                        </option>

                    @endforeach

                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Machine <span class="text-danger">*</span></label>

                <select name="machine_name" class="form-select form-control" required>

                    @foreach(['Fessmann','Maurer','Bastra','Vemag'] as $machine)

                        <option
                            value="{{ $machine }}"
                            {{ old('machine_name', $master->machine_name ?? '') == $machine ? 'selected' : '' }}>

                            {{ $machine }}

                        </option>

                    @endforeach

                </select>
            </div>

        </div>

        <hr>

        <div class="alert alert-info d-flex align-items-start" role="alert">
            <i class="bx bx-info-circle me-2 mt-1"></i>
            <div>
                <strong>Catatan pengisian parameter:</strong>
                <ul class="mb-0 mt-1">
                    <li>Kolom <strong>Min</strong> dan <strong>Max</strong> dipakai untuk parameter yang punya rentang nilai (misal suhu 55–60°C).</li>
                    <li>Kalau nilainya <strong>tidak berupa range</strong> (cuma satu angka pasti), isi kolom <strong>Min</strong> saja dan biarkan <strong>Max</strong> kosong.</li>
                    <li>Kolom yang dikosongi otomatis tidak ditampilkan sebagai range di form laporan.</li>
                </ul>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">
                Process Setting
            </h5>

            <button
                type="button"
                class="btn btn-primary btn-sm"
                id="btn-add">

                <i class="bx bx-plus"></i>

                Tambah Step

            </button>

        </div>

        @php

        $processes = [
            'Showering',
            'Warming',
            'Drying I',
            'Drying II',
            'Drying III',
            'Drying IV',
            'Drying V',
            'Smoking',
            'Cooking I',
            'Cooking II',
            'Evacuation',
            'Showering & Cooling Down',
        ];

        $steps = old('steps', isset($master) ? $master->steps->toArray() : []);

        @endphp

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead>

                    <tr>

                        <th width="90">Seq</th>

                        <th width="220">Process</th>

                        <th>Temperature Min (°C)</th>

                        <th>Temperature Max (°C)</th>

                        <th>Time Min (Minute)</th>
                        <th>Time Max (Minute)</th>

                        <th>RH (%)</th>

                        <th>CT Min (°C)</th>
                        <th>CT Max (°C)</th>

                        <th width="70">Action</th>

                    </tr>

                </thead>

                <tbody id="step-body">

                    @forelse($steps as $i => $step)

                        <tr>

                            <td>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="steps[{{ $i }}][sequence]"
                                    value="{{ $step['sequence'] }}">

                            </td>

                            <td>

                                <select
                                    class="form-select form-control"
                                    name="steps[{{ $i }}][process_name]">

                                    @foreach($processes as $process)

                                        <option
                                            value="{{ $process }}"
                                            {{ $step['process_name'] == $process ? 'selected' : '' }}>

                                            {{ $process }}

                                        </option>

                                    @endforeach

                                </select>

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    name="steps[{{ $i }}][temperature_min]"
                                    value="{{ $step['temperature_min'] }}"
                                    placeholder="mis: 12.5">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    name="steps[{{ $i }}][temperature_max]"
                                    value="{{ $step['temperature_max'] }}"
                                    placeholder="mis: 12.5">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="steps[{{ $i }}][time_minutes]"
                                    value="{{ $step['time_minutes'] }}"
                                    placeholder="mis: 12">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    class="form-control"
                                    name="steps[{{ $i }}][time_minutes_max]"
                                    value="{{ $step['time_minutes_max'] }}"
                                    placeholder="mis: 15">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    name="steps[{{ $i }}][rh]"
                                    value="{{ $step['rh'] }}"
                                    placeholder="mis: 12.5">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    name="steps[{{ $i }}][core_temperature]"
                                    value="{{ $step['core_temperature'] }}"
                                    placeholder="mis: 12">

                            </td>

                            <td>

                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    name="steps[{{ $i }}][core_temperature_max]"
                                    value="{{ $step['core_temperature_max'] }}"
                                    placeholder="mis: 15">

                            </td>

                            <td class="text-center">

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm btn-remove">

                                    <i class="bx bx-trash"></i>

                                </button>

                            </td>

                        </tr>

                    @empty

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="card-footer text-end">

        <a
            href="{{ route('master-smoke-houses.index') }}"
            class="btn btn-secondary">

            Kembali

        </a>

        <button
            class="btn btn-primary">

            Simpan

        </button>

    </div>

</div>

@section('script')

<script>

let index = {{ count($steps) }};

const processes = @json($processes);

document.getElementById('btn-add').addEventListener('click', function(){

    let options = '';

    processes.forEach(function(item){

        options += `<option value="${item}">${item}</option>`;

    });

    let row = `
    <tr>

        <td>
            <input type="number"
                class="form-control"
                name="steps[${index}][sequence]"
                value="${index+1}">
        </td>

        <td>
            <select
                class="form-select form-control"
                name="steps[${index}][process_name]">
                ${options}
            </select>
        </td>

        <td>
            <input type="number"
                step="0.01"
                class="form-control"
                name="steps[${index}][temperature_min]" placeholder="mis: 12">
        </td>

        <td>
            <input type="number"
                step="0.01"
                class="form-control"
                name="steps[${index}][temperature_max]" placeholder="mis: 15">
        </td>

        <td>
            <input type="number"
                class="form-control"
                name="steps[${index}][time_minutes]" placeholder="mis: 6">
        </td>

        <td>
            <input type="number"
                class="form-control"
                name="steps[${index}][time_minutes_max]" placeholder="mis: 12">
        </td>

        <td>
            <input type="number"
                step="0.01"
                class="form-control"
                name="steps[${index}][rh]" placeholder="mis: 12">
        </td>

        <td>
            <input type="number"
                step="0.01"
                class="form-control"
                name="steps[${index}][core_temperature]" placeholder="mis: 12">
        </td>

        <td>
            <input type="number"
                step="0.01"
                class="form-control"
                name="steps[${index}][core_temperature_max]" placeholder="mis: 15">
        </td>

        <td class="text-center">

            <button
                type="button"
                class="btn btn-danger btn-sm btn-remove">

                <i class="bx bx-trash">x</i>

            </button>

        </td>

    </tr>`;

    document
        .getElementById('step-body')
        .insertAdjacentHTML('beforeend', row);

    index++;

});

document.addEventListener('click', function(e){

    if(e.target.closest('.btn-remove')){

        e.target.closest('tr').remove();

    }

});

</script>

@endsection