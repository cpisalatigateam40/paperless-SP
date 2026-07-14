@php
$isEdit = isset($detail);
$masterUuid = $detail->master_uuid ?? '';
$SHOWERING_PROCESS = 'Showering & Cooling Down';

$cookingSteps = $isEdit ? $detail->steps->where('process_name', '!=', $SHOWERING_PROCESS) : collect();
$showeringSteps = $isEdit ? $detail->steps->where('process_name', $SHOWERING_PROCESS) : collect();
@endphp

<div class="card detail-item mb-4" data-index="{{ $index }}" data-master-uuid="{{ $masterUuid }}">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            Produk <span class="detail-number">{{ is_numeric($index) ? $index + 1 : '' }}</span>
        </h6>
        <!-- <button type="button" class="btn btn-danger btn-sm remove-detail">
            <i class="bx bx-trash"></i>
        </button> -->
    </div>

    <div class="card-body">

        <input type="hidden" name="details[{{ $index }}][master_uuid]" class="master-uuid"
            value="{{ old("details.$index.master_uuid", $masterUuid) }}">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nama Produk</label>
                <select class="form-select form-control product-select" name="details[{{ $index }}][product_uuid]"
                    required>
                    <option value="">Pilih Product</option>
                    @foreach($products as $product)
                    <option value="{{ $product->uuid }}"
                        {{ old("details.$index.product_uuid",$detail->product_uuid ?? '')==$product->uuid?'selected':'' }}>
                        {{ $product->product_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Machine / Smoke House</label>
                <select class="form-select form-control machine-select" name="details[{{ $index }}][machine_name]"
                    required {{ $isEdit ? '' : 'disabled' }}>
                    <option value="">{{ $isEdit ? '' : 'Pilih Product dulu' }}</option>
                    @if($isEdit)
                    <option selected value="{{ $detail->machine_name }}" data-master-uuid="{{ $masterUuid }}">
                        {{ $detail->machine_name }}
                    </option>
                    @endif
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Kode Produksi</label>
                <input type="text" class="form-control" name="details[{{ $index }}][production_code]"
                    value="{{ old("details.$index.production_code",$detail->production_code ?? '') }}"
                    placeholder="mis: PJ27301CC0" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Gramase</label>
                <input type="number" step="0.01" class="form-control" name="details[{{ $index }}][gramase]"
                    value="{{ old("details.$index.gramase",$detail->gramase ?? '') }}" placeholder="mis: 500">
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Nomor Smoke House</label>
                <input type="number" class="form-control" name="details[{{ $index }}][smoke_house_no]"
                    value="{{ old("details.$index.smoke_house_no",$detail->smoke_house_no ?? '') }}" placeholder="mis: 1" >
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Jumlah Trolley</label>
                <input type="number" class="form-control" name="details[{{ $index }}][trolley_count]"
                    value="{{ old("details.$index.trolley_count",$detail->trolley_count ?? '') }}" placeholder="mis: 5" >
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Jumlah Stick</label>
                <input type="number" class="form-control" name="details[{{ $index }}][stick_count]"
                    value="{{ old("details.$index.stick_count",$detail->stick_count ?? '') }}" placeholder="mis: 10" >
            </div>
        </div>


        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Process</label>
                <input type="datetime-local" class="form-control" name="details[{{ $index }}][start_process]"
                    value="{{ old("details.$index.start_process",isset($detail->start_process)?\Carbon\Carbon::parse($detail->start_process)->format('Y-m-d\TH:i'):'') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">End Process</label>
                <input type="datetime-local" class="form-control" name="details[{{ $index }}][end_process]"
                    value="{{ old("details.$index.end_process",isset($detail->end_process)?\Carbon\Carbon::parse($detail->end_process)->format('Y-m-d\TH:i'):'') }}">
            </div>
        </div>

        <hr>
        <h6 class="mb-3" style="font-weight: bold;">Verifikasi Cooking</h6>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">No</th>
                        <th width="160">Process</th>
                        <th>Setting Suhu</th>
                        <th>Aktual Suhu</th>
                        <th>Setup Time</th>
                        <th>Actual Time</th>
                        <th>Setting RH</th>
                        <th>Aktual RH</th>
                        <th>Setting Core</th>
                        <th>Aktual Core</th>
                    </tr>
                </thead>
                <tbody class="step-container">
                    @if($isEdit && $cookingSteps->count())
                    @foreach($cookingSteps as $stepIndex => $step)
                    <tr>
                        <td>
                            {{ $step->sequence }}
                            <input type="hidden" name="details[{{ $index }}][steps][{{ $stepIndex }}][sequence]"
                                value="{{ $step->sequence }}">
                        </td>
                        <td>
                            {{ $step->process_name }}
                            <input type="hidden" name="details[{{ $index }}][steps][{{ $stepIndex }}][process_name]"
                                value="{{ $step->process_name }}">
                        </td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_temp]"
                                value="{{ $step->setting_temp }}"></td>
                        <td><input class="form-control"
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_temp]"
                                value="{{ $step->actual_temp }}" placeholder="mis: 12.5"></td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_time]"
                                value="{{ $step->setting_time }}"></td>
                        <td><input class="form-control"
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_time]"
                                value="{{ $step->actual_time }}" placeholder="mis: 120"></td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_rh]"
                                value="{{ $step->setting_rh }}"></td>
                        <td><input class="form-control" name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_rh]"
                                value="{{ $step->actual_rh }}" placeholder="mis: 60"></td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_ct]"
                                value="{{ $step->setting_ct }}"></td>
                        <td><input class="form-control" name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_ct]"
                                value="{{ $step->actual_ct }}" placeholder="mis: 5"></td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="step-placeholder">
                        <td colspan="10" class="text-center text-muted">Pilih product & machine untuk memuat parameter
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>



        {{-- ================= COOKING ULANG ================= --}}
        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0" style="font-weight: bold;">Cooking Ulang</h6>
            <button type="button" class="btn btn-success btn-sm add-rework">
                <i class="bx bx-plus"></i> Tambah Cooking Ulang
            </button>
        </div>

        <div class="rework-container">
            @if($isEdit)
            @foreach($detail->reworks as $reworkIndex => $rework)
            @include('report-smoke-houses.partials.rework', [
            'index' => $index,
            'reworkIndex' => $reworkIndex,
            'rework' => $rework,
            'masterUuid' => $masterUuid,
            ])
            @endforeach
            @endif
        </div>

        {{-- template dipakai JS untuk clone cooking ulang baru --}}
        <template class="tpl-rework">
            @include('report-smoke-houses.partials.rework', [
            'index' => $index,
            'reworkIndex' => '__RIDX__',
            'rework' => null,
            'masterUuid' => $masterUuid,
            ])
        </template>

        {{-- ================= SENSORI ================= --}}
        @php $sensories = $isEdit ? $detail->sensories : null; @endphp

        <hr>
        <h6 class="mb-3" style="font-weight: bold;">Hasil Sensori</h6>

        <div class="row">
            @foreach(['appearance' => 'Kenampakan', 'color' => 'Warna', 'aroma' => 'Aroma'] as $field => $label)
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ $label }}</label>
                <select class="form-select form-control" name="details[{{ $index }}][sensories][{{ $field }}]">
                    <option value="">Pilih</option>
                    <option value="Pass"
                        {{ old("details.$index.sensories.$field",$sensories->$field ?? '')=='Pass'?'selected':'' }}>Pass
                    </option>
                    <option value="Fail"
                        {{ old("details.$index.sensories.$field",$sensories->$field ?? '')=='Fail'?'selected':'' }}>Fail
                    </option>
                </select>
            </div>
            @endforeach
        </div>

        <div class="row">
            @foreach(['taste' => 'Rasa', 'texture' => 'Tekstur'] as $field => $label)
            <div class="col-md-6 mb-3">
                <label class="form-label">{{ $label }}</label>
                <select class="form-select form-control" name="details[{{ $index }}][sensories][{{ $field }}]">
                    <option value="">Pilih</option>
                    <option value="Pass"
                        {{ old("details.$index.sensories.$field",$sensories->$field ?? '')=='Pass'?'selected':'' }}>Pass
                    </option>
                    <option value="Fail"
                        {{ old("details.$index.sensories.$field",$sensories->$field ?? '')=='Fail'?'selected':'' }}>Fail
                    </option>
                </select>
            </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-md-12 mb-3">
                <label class="form-label">Catatan Sensori</label>
                <textarea rows="3" class="form-control"
                    name="details[{{ $index }}][sensories][notes]">{{ old("details.$index.sensories.notes",$sensories->notes ?? '') }}</textarea>
            </div>
        </div>

        {{-- ================= SHOWERING & COOLING DOWN ================= --}}
        <hr>
        <h6 class="mb-3" style="font-weight: bold;">Hasil Verifikasi Showering & Cooling Down</h6>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="160">Process</th>
                        <th>Setting Suhu</th>
                        <th>Aktual Suhu</th>
                        <th>Setup Time</th>
                        <th>Actual Time</th>
                        <th>Setting RH</th>
                        <th>Aktual RH</th>
                        <th>Setting Core</th>
                        <th>Aktual Core</th>
                    </tr>
                </thead>
                <tbody class="showering-container">
                    @if($isEdit && $showeringSteps->count())
                    @foreach($showeringSteps as $stepIndex => $step)
                    <tr>
                        <td>
                            {{ $step->process_name }}
                            <input type="hidden" name="details[{{ $index }}][steps][{{ $stepIndex }}][sequence]"
                                value="{{ $step->sequence }}">
                            <input type="hidden" name="details[{{ $index }}][steps][{{ $stepIndex }}][process_name]"
                                value="{{ $step->process_name }}">
                        </td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_temp]"
                                value="{{ $step->setting_temp }}"></td>
                        <td><input class="form-control"
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_temp]"
                                value="{{ $step->actual_temp }}" placeholder="mis: 12.5"></td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_time]"
                                value="{{ $step->setting_time }}"></td>
                        <td><input class="form-control"
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_time]"
                                value="{{ $step->actual_time }}" placeholder="mis: 120"></td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_rh]"
                                value="{{ $step->setting_rh }}"></td>
                        <td><input class="form-control" name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_rh]"
                                value="{{ $step->actual_rh }}" placeholder="mis: 60"></td>
                        <td><input class="form-control" readonly
                                name="details[{{ $index }}][steps][{{ $stepIndex }}][setting_ct]"
                                value="{{ $step->setting_ct }}"></td>
                        <td><input class="form-control" name="details[{{ $index }}][steps][{{ $stepIndex }}][actual_ct]"
                                value="{{ $step->actual_ct }}" placeholder="mis: 5"></td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="showering-placeholder">
                        <td colspan="9" class="text-center text-muted">Pilih product & machine untuk memuat parameter
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <label class="form-label">Proses Cooling Down Selesai</label>
                <input type="datetime-local" class="form-control" name="details[{{ $index }}][cooling_finish]"
                    value="{{ old("details.$index.cooling_finish",isset($detail->cooling_finish)?\Carbon\Carbon::parse($detail->cooling_finish)->format('Y-m-d\TH:i'):'') }}">
            </div>
        </div>

        <hr>
        <!-- <div class="text-end">
            <button type="button" class="btn btn-danger remove-detail">
                <i class="bx bx-trash"></i> Hapus Produk
            </button>
        </div> -->

    </div>
</div>