@php
    // $index  : angka (0,1,2..) atau '__INDEX__' untuk template JS
    // $detail : instance DetailForeignObject (edit) atau null (create)
    $d = $detail ?? null;
    $val = fn ($field, $default = null) => old("details.{$index}.{$field}", $d->{$field} ?? $default);

    $investigations = array_values(array_filter((array) $val('investigation', []), fn ($v) => filled($v)));
    if (empty($investigations)) {
        $investigations = [''];
    }

    $selectedItem = old(
        "details.{$index}.item",
        $d ? (($d->item_type ?? 'product') . ':' . ($d->item_uuid ?? $d->product_uuid)) : null
    );

    $existingEvidence = (array) old("details.{$index}.old_evidence", $d ? $d->evidence : []);
@endphp

<div class="detail-block fo-detail" data-index="{{ $index }}">
    @if ($d)
        <input type="hidden" name="details[{{ $index }}][uuid]" value="{{ $d->uuid }}">
    @endif

    {{-- HEADER BLOK --}}
    <div class="fo-detail-head">
        <div class="d-flex align-items-center gap-2">
            <span class="fo-no detail-number">{{ is_numeric($index) ? $index + 1 : '' }}</span>
            <span class="fo-detail-title">Detail Kontaminasi</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-detail" onclick="removeDetail(this)">
            Hapus Detail
        </button>
    </div>

    <div class="fo-detail-body">

        {{-- A - EVENT INFORMATION --}}
        <section class="fo-sec">
            <div class="fo-sec-head">
                <span class="fo-sec-letter">A</span>
                <div>
                    <div class="fo-sec-title">Event Information</div>
                    <div class="fo-sec-sub">Kapan, produk apa, dan di proses mana temuan terjadi</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fo-label">Waktu Ditemukan <span class="req">*</span></label>
                    <input type="time" name="details[{{ $index }}][time]" class="form-control"
                        value="{{ substr($val('time', now()->format('H:i')), 0, 5) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Produk / RM <span class="req">*</span></label>
                    <select name="details[{{ $index }}][item]" class="form-control select2-detail" required>
                        <option value="">Cari produk / raw material / premix...</option>

                        <optgroup label="Produk">
                            @foreach ($products as $product)
                                <option value="product:{{ $product->uuid }}" @selected($selectedItem === 'product:' . $product->uuid)>
                                    {{ $product->product_name }}
                                </option>
                            @endforeach
                        </optgroup>

                        <optgroup label="Raw Material">
                            @foreach ($rawMaterials as $rm)
                                <option value="raw_material:{{ $rm->uuid }}" @selected($selectedItem === 'raw_material:' . $rm->uuid)>
                                    {{ $rm->material_name }}
                                </option>
                            @endforeach
                        </optgroup>

                        <optgroup label="Premix">
                            @foreach ($premixes as $premix)
                                <option value="premix:{{ $premix->uuid }}" @selected($selectedItem === 'premix:' . $premix->uuid)>
                                    {{ $premix->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Kode Produksi</label>
                    <input type="text" name="details[{{ $index }}][production_code]" class="form-control"
                        placeholder="mis: SOR QJ 27 801 AA0" value="{{ $val('production_code') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fo-label">Gramase</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" name="details[{{ $index }}][gramase]"
                            class="form-control" placeholder="65" value="{{ $val('gramase') }}">
                        <span class="input-group-text">gr</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Process Stage</label>
                    <input type="text" name="details[{{ $index }}][process_stage]" class="form-control"
                        placeholder="mis: Metal Detector" value="{{ $val('process_stage') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Line</label>
                    <input type="text" name="details[{{ $index }}][line]" class="form-control"
                        placeholder="mis: Line RTG" value="{{ $val('line') }}">
                </div>
            </div>
        </section>

        {{-- B - CONTAMINATION DETAILS --}}
        <section class="fo-sec">
            <div class="fo-sec-head">
                <span class="fo-sec-letter">B</span>
                <div>
                    <div class="fo-sec-title">Contamination Details</div>
                    <div class="fo-sec-sub">Jenis, ukuran, dan ciri benda asing</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fo-label">Category / Type</label>
                    <select name="details[{{ $index }}][contamination_type_uuid]" class="form-control select2-detail">
                        <option value="">Pilih kategori / tipe...</option>
                        @foreach ($contaminationCategories as $category)
                            <optgroup label="{{ $category->name }}">
                                @foreach ($category->types as $type)
                                    <option value="{{ $type->uuid }}" @selected($val('contamination_type_uuid') == $type->uuid)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Size / Quantity</label>
                    <input type="text" name="details[{{ $index }}][size_quantity]" class="form-control"
                        placeholder="mis: 7 mm / 1 pcs" value="{{ $val('size_quantity') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Deskripsi</label>
                    <input type="text" name="details[{{ $index }}][description]" class="form-control"
                        placeholder="mis: Wiremesh berwarna bening" value="{{ $val('description') }}">
                </div>
            </div>
        </section>

        {{-- C - PRODUCT CONTROL & DISPOSITION --}}
        <section class="fo-sec">
            <div class="fo-sec-head">
                <span class="fo-sec-letter">C</span>
                <div>
                    <div class="fo-sec-title">Product Control &amp; Disposition</div>
                    <div class="fo-sec-sub">Status produk terdampak dan keputusan penanganannya</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fo-label">Product Status</label>
                    <select name="details[{{ $index }}][product_status]" class="form-select form-control">
                        <option value="">Pilih status...</option>
                        @foreach ($actionOptions as $key => $label)
                            <option value="{{ $key }}" @selected($val('product_status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Affected Quantity</label>
                    <input type="text" name="details[{{ $index }}][affected_quantity]" class="form-control"
                        placeholder="mis: 1 pack" value="{{ $val('affected_quantity') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fo-label">Disposition</label>
                    <select name="details[{{ $index }}][disposition]" class="form-select form-control">
                        <option value="">Pilih disposition...</option>
                        @foreach ($actionOptions as $key => $label)
                            <option value="{{ $key }}" @selected($val('disposition') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- D - INVESTIGATION & CORRECTIVE ACTION --}}
        <section class="fo-sec">
            <div class="fo-sec-head">
                <span class="fo-sec-letter">D</span>
                <div>
                    <div class="fo-sec-title">Investigation &amp; Corrective Action</div>
                    <div class="fo-sec-sub">Analisa asal kontaminasi dan tindakan perbaikan</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fo-label">Investigation <span class="fo-hint">maks. 5 poin</span></label>

                    <div class="investigation-list" data-name="details[{{ $index }}][investigation][]">
                        @foreach ($investigations as $text)
                            <div class="investigation-row input-group mb-2">
                                <span class="input-group-text investigation-no">{{ $loop->iteration }}</span>
                                <textarea name="details[{{ $index }}][investigation][]" class="form-control" rows="2"
                                    placeholder="Analisa asal kontaminasi">{{ $text }}</textarea>
                                <button type="button" class="btn btn-outline-danger btn-remove-investigation"
                                    title="Hapus">&times;</button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-investigation">
                        + Tambah Investigation
                    </button>
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label fo-label">Immediate Correction</label>
                    <textarea name="details[{{ $index }}][immediate_correction]" class="form-control" rows="1"
                        placeholder="Tindakan segera yang dilakukan">{{ $val('immediate_correction') }}</textarea>
                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label fo-label">Corrective Action</label>
                    <select name="details[{{ $index }}][corrective_action]" class="form-select form-control">
                        <option value="">Pilih tindakan...</option>
                        @foreach ($actionOptions as $key => $label)
                            <option value="{{ $key }}" @selected($val('corrective_action') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        {{-- E - EVIDENCE --}}
        <section class="fo-sec mb-0">
            <div class="fo-sec-head">
                <span class="fo-sec-letter">E</span>
                <div>
                    <div class="fo-sec-title">Evidence</div>
                    <div class="fo-sec-sub">Foto bukti, bisa lebih dari satu</div>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6 fo-evidence">
                    <label class="form-label fo-label">
                        Foto Evidence
                        <!-- <span class="fo-hint">maks. {{ \App\Models\DetailForeignObject::MAX_EVIDENCE }} foto &middot; otomatis dikompres</span> -->
                    </label>

                    @if (!empty($existingEvidence))
                        <div class="evidence-existing">
                            @foreach ($existingEvidence as $path)
                                <div class="evidence-thumb">
                                    <input type="hidden" name="details[{{ $index }}][old_evidence][]" value="{{ $path }}">
                                    <img src="{{ asset('storage/' . $path) }}" alt="Evidence">
                                    <button type="button" class="evidence-remove" title="Hapus foto">&times;</button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <input type="file" name="details[{{ $index }}][evidence][]" class="form-control evidence-input"
                        accept="image/jpeg,image/png" multiple
                        data-max="{{ \App\Models\DetailForeignObject::MAX_EVIDENCE }}">
                    <div class="evidence-preview"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fo-label">Caption</label>
                    <textarea name="details[{{ $index }}][notes]" class="form-control" rows="1"
                        placeholder="Nomor bukti, waktu (bila ada), dan keterangan">{{ $val('notes') }}</textarea>
                </div>
            </div>
        </section>
    </div>
</div>