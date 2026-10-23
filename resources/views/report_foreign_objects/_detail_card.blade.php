{{-- $detail : DetailForeignObject, $number : nomor urut --}}
@php
    $actionLabels = \App\Models\DetailForeignObject::ACTION_OPTIONS;
    $statusLabels = \App\Models\DetailForeignObject::PRODUCT_STATUS_OPTIONS;
    $statusColors = [
        'hold'    => 'warning',
        'pending' => 'info',
        'lainnya' => 'secondary',
    ];

    $gramase = !empty($detail->gramase) ? $detail->gramase : ($detail->product->nett_weight ?? null);
    $gramase = is_numeric($gramase) ? rtrim(rtrim(number_format($gramase, 2, '.', ''), '0'), '.') . ' g' : null;

    $type = $detail->contaminationType;
@endphp

@once
<style>
    .fo-card { border: 1px solid #e3e6ef; border-radius: .5rem; background: #fff; overflow: hidden; }
    .fo-card-header { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .75rem;
        padding: .75rem 1rem; background: #f6f8fc; border-bottom: 1px solid #e3e6ef; }
    .fo-card-header .fo-no { background: #3b5bdb; color: #fff; border-radius: 999px;
        min-width: 1.75rem; height: 1.75rem; display: inline-flex; align-items: center;
        justify-content: center; font-size: .8rem; font-weight: 600; }
    .fo-card-header .fo-title { font-weight: 600; font-size: .95rem; color: #1f2937; }
    .fo-card-header .fo-time { color: #6b7280; font-size: .85rem; }
    .fo-badges { margin-left: auto; display: flex; flex-wrap: wrap; gap: .4rem; }
    .fo-card-body { padding: 1rem; }
    .fo-section { height: 100%; padding: .25rem 0; }
    .fo-section-title { font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
        color: #3b5bdb; padding-bottom: .4rem; margin-bottom: .75rem; border-bottom: 2px solid #dbe4ff; }
    .fo-field { margin-bottom: .7rem; }
    .fo-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #8a94a6; }
    .fo-value { font-size: .9rem; color: #1f2937; line-height: 1.35; }
    .fo-card-footer { display: flex; gap: 1rem; align-items: flex-start; padding: .85rem 1rem;
        border-top: 1px dashed #d5dbe8; background: #fcfcfd; }
    .fo-card-footer img { width: 96px; height: 72px; object-fit: cover; border-radius: .375rem; border: 1px solid #e3e6ef; }
</style>
@endonce

<div class="fo-card mb-3">
    {{-- HEADER --}}
    <div class="fo-card-header">
        <span class="fo-no">{{ $number }}</span>
        <span class="fo-title">{{ $detail->item_name ?? '-' }}</span>
        <span class="fo-time">&middot; {{ substr($detail->time, 0, 5) }}</span>

        <div class="fo-badges">
            @if ($type)
                <span class="badge bg-primary text-dark border">
                    {{ $type->category->name ?? '-' }} / {{ $type->name }}
                </span>
            @elseif ($detail->contaminant_type)
                <span class="badge bg-primary text-dark border">{{ $detail->contaminant_type }}</span>
            @endif

            @if ($detail->product_status)
                <span class="badge bg-{{ $statusColors[$detail->product_status] ?? 'secondary' }}">
                    {{ $statusLabels[$detail->product_status] ?? $detail->product_status }}
                </span>
            @endif
        </div>
    </div>

    {{-- BODY --}}
    <div class="fo-card-body">
        <div class="row g-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="fo-section">
                    <div class="fo-section-title">A &middot; Event Information</div>
                    <x-fo-field label="Produk / RM">
                        {{ $detail->item_name }}
                        @if (($detail->item_type ?? 'product') !== 'product')
                            <span class="text-muted">({{ $detail->item_type_label }})</span>
                        @endif
                    </x-fo-field>
                    <x-fo-field label="Kode Produksi" :value="$detail->production_code" />
                    <x-fo-field label="Gramase" :value="$gramase" />
                    <x-fo-field label="Process Stage" :value="$detail->process_stage" />
                    <x-fo-field label="Line" :value="$detail->line" />
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="fo-section">
                    <div class="fo-section-title">B &middot; Contamination Details</div>
                    <x-fo-field label="Category / Type"
                        :value="$type ? ($type->category->name ?? '-') . ' / ' . $type->name : $detail->contaminant_type" />
                    <x-fo-field label="Size / Quantity" :value="$detail->size_quantity" />
                    <x-fo-field label="Deskripsi" :value="$detail->description" />
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="fo-section">
                    <div class="fo-section-title">C &middot; Product Control &amp; Disposition</div>
                    <x-fo-field label="Product Status" :value="$statusLabels[$detail->product_status] ?? null" />
                    <x-fo-field label="Affected Quantity" :value="$detail->affected_quantity" />
                    <x-fo-field label="Disposition" :value="$actionLabels[$detail->disposition] ?? null" />
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="fo-section">
                    <div class="fo-section-title">D &middot; Investigation &amp; Corrective Action</div>
                    @if (!empty($detail->investigation))
                        <x-fo-field label="Investigation">
                            <ol class="ps-3 mb-0">
                                @foreach ($detail->investigation as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ol>
                        </x-fo-field>
                    @else
                        <x-fo-field label="Investigation" value="-" />
                    @endif
                    <x-fo-field label="Immediate Correction" :value="$detail->immediate_correction" />
                    <x-fo-field label="Corrective Action" :value="$detail->corrective_action" />

                    {{-- data lama --}}
                    @if ($detail->analysis_stage)
                        <x-fo-field label="Tahapan Analisis" :value="$detail->analysis_stage" />
                    @endif
                    @if ($detail->contaminant_origin)
                        <x-fo-field label="Asal Kontaminan" :value="$detail->contaminant_origin" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- E - EVIDENCE --}}
    @php $evidences = $detail->evidence; @endphp

    @if (!empty($evidences) || $detail->notes)
        <div class="fo-card-footer">
            @if (!empty($evidences))
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($evidences as $i => $path)
                        <a href="{{ asset('storage/' . $path) }}" target="_blank"
                            title="Evidence {{ sprintf('%02d', $i + 1) }}">
                            <img src="{{ asset('storage/' . $path) }}" alt="Evidence {{ $i + 1 }}">
                        </a>
                    @endforeach
                </div>
            @endif
            <div>
                <div class="fo-label">E &middot; Evidence</div>
                <div class="fo-value">{{ $detail->notes ?: '-' }}</div>
            </div>
        </div>
    @endif
</div>