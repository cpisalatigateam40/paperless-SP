<?php
// config/traceability.php

return [
    'modules' => [
        'md_adonan' => [
            'model' => \App\Models\DetailMetalDetector::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportMetalDetector::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product'],
            'table_columns' => [
                'Jam', 'Produk', 'Gramase', 'Kode Produksi',
                'Fe 1.5 mm', 'Non Fe 2 mm', 'SUS 316 2.5 mm',
                'Status', 'Tindakan Koreksi', 'Keterangan',
            ],
            'table_row' => function ($detail) {
                $gramase = !empty($detail->gramase)
                    ? $detail->gramase
                    : ($detail->product->nett_weight ?? '-');

                return [
                    $detail->hour,
                    $detail->product->product_name ?? '-',
                    $gramase . ' g',
                    $detail->production_code,
                    $detail->result_fe,
                    $detail->result_non_fe,
                    $detail->result_sus316,
                    $detail->verif_after_correct,
                    $detail->corrective_action,
                    $detail->notes,
                ];
            },
            'form_notes' => fn($form) => $form->notes,
            'pdf_route' => 'report_metal_detectors.export_pdf',
            'label' => 'Verifikasi Kinerja Metal Detector Adonan',
            'route_key_column' => 'uuid',
            'display_fields' => fn($detail) => [],
            'related' => [],
        ],

        'process_prod' => [
            'model' => \App\Models\DetailProcessProd::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportProcessProd::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => [
                'product', 'formula', 'reworkProduct',
                'items.formulation.rawMaterial', 'items.formulation.premix',
                'emulsifying', 'sensoric', 'tumbling', 'aging',
            ],
            'pdf_route' => 'report_process_productions.export',
            'label' => 'Verifikasi Proses Mixing, Chopping, dan Emulsifying',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.process_prod',
            'display_fields' => fn($detail) => [],
            'related' => [],
        ],

        'weight_stuffer' => [
            'model' => \App\Models\DetailWeightStuffer::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportWeightStuffer::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => [
                'product', 'townsend', 'hitech', 'vemag', 'vemag2', 'handtmann',
                'cases', 'weights', 'documentations',
            ],
            'partial' => 'traceability.partials.weight_stuffer',
            'pdf_route' => 'report_weight_stuffers.export-pdf',
            'pdf_route_params' => function ($form, $group) {
                return [$form->uuid, $group->first()->uuid];
            },
            'label' => 'Verifikasi Proses Stuffing',
            'route_key_column' => 'uuid',
            'display_fields' => fn($detail) => [],
            'related' => [],
        ],

        'siomays' => [
            'model' => \App\Models\DetailSiomay::class,
            'column' => null,
            'search_columns' => [],
            'search_relations' => [
                'report' => 'production_code',
                'report.product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportSiomay::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['report.product', 'rawMaterials.rawMaterial'],
            'partial' => 'traceability.partials.siomays',
            'pdf_route' => 'report_siomays.export_pdf',
            'label' => 'Verifikasi Proses Pembuatan Kulit Siomay/Gyoza',
            'route_key_column' => 'uuid',
            'display_fields' => fn($detail) => [],
            'related' => [],
        ],

        'smokehouse' => [
            'model' => \App\Models\DetailSmokeHouse::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportSmokeHouse::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => [
                'product', 'steps', 'sensories', 'reworks.steps',
            ],
            'pdf_route' => 'report-smoke-houses.export-pdf',
            'label' => 'Verifikasi Proses Pemasakan di Smoke House',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.smokehouse',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'steamer_cooking' => [
            'model' => \App\Models\SteamerCookingDetail::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'batch.report.product' => 'product_name', // whereHas juga native support dot notation
            ],

            'form_model' => \App\Models\ReportSteamerCooking::class,
            'form_relation' => 'batch.report', // dot notation, jalan setelah fix service di atas
            'detail_relation' => 'batches.details',
            'detail_with' => [
                'batch.report.product',
                'coreTemps',
            ],

            'pdf_route' => 'report_steamer_cookings.export_pdf',
            'label' => 'Verifikasi Proses Pemasakan di Steamer',
            'route_key_column' => 'uuid',

            'partial' => 'traceability.partials.steamer_cooking',
            'display_fields' => fn($detail) => [],
            'related' => [],
        ],

        'boiling_tank' => [
            'model' => \App\Models\DetailBoilingTank::class,
            'column' => null,
            'search_columns' => [],
            'search_relations' => [
                'report' => 'product_code',
                'report.product' => 'product_name',
            ],

            'form_model' => \App\Models\ReportBoilingTank::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['report.product', 'checks'],

            'pdf_route' => 'report_boiling_tanks.export_pdf',
            'label' => 'Verifikasi Proses Pemasakan di Boiling Tank',
            'route_key_column' => 'uuid',

            'partial' => 'traceability.partials.boiling_tank',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'sauces' => [
            'model' => \App\Models\DetailSauce::class,
            'column' => null,
            'search_columns' => [],
            'search_relations' => [
                'report' => 'production_code',
                'report.product' => 'product_name',
            ],

            'form_model' => \App\Models\ReportSauce::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => [
                'report.product', 'report.formula',
                'rawMaterials.rawMaterial', 'rawMaterials.premix',
            ],

            'pdf_route' => 'report_sauces.export_pdf',
            'label' => 'Verifikasi Proses Pemasakan di Steam Kettle',
            'route_key_column' => 'uuid',

            'partial' => 'traceability.partials.sauces',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'packaging_verif' => [
            'model' => \App\Models\DetailPackagingVerif::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportPackagingVerif::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product', 'checklist'],
            'pdf_route' => 'report_packaging_verifs.export-pdf',
            'label' => 'Verifikasi Proses Pengemasan',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.packaging_verif',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'md_product' => [
            'model' => \App\Models\DetailMdProduct::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportMdProduct::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product', 'positions'],
            'pdf_route' => 'report_md_products.export-pdf',
            'label' => 'Verifikasi Kinerja Metal Detector Produk',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.md_product',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'lab_sample' => [
            'model' => \App\Models\DetailLabSample::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportLabSample::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product'],
            'table_columns' => [
                'No', 'Nama Produk', 'Gramase', 'Kode Produksi',
                'Best Before', 'Jenis Sample', 'Jumlah', 'Catatan',
            ],
            'table_row' => function ($detail) {
                static $no = 0;
                $no++;

                return [
                    $no,
                    $detail->product->product_name ?? '-',
                    $detail->gramase,
                    $detail->production_code,
                    $detail->best_before,
                    $detail->sample_type,
                    trim($detail->quantity . ' ' . $detail->unit),
                    $detail->notes,
                ];
            },
            'pdf_route' => 'report_lab_samples.export-pdf',
            'label' => 'Form Pengambilan Sample',
            'route_key_column' => 'uuid',
            'display_fields' => fn($detail) => [],
            'related' => [],
        ],

        'pasteur' => [
            'model' => \App\Models\DetailPasteur::class,
            'column' => 'product_code',
            'search_columns' => ['product_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportPasteur::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product', 'steps'],
            'pdf_route' => 'report_pasteurs.export_pdf',
            'label' => 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.pasteur',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'waterbath' => [
            'model' => \App\Models\DetailWaterbath::class,
            'column' => 'batch_code',
            'search_columns' => ['batch_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportWaterbath::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product'],
            'pdf_route' => 'report_waterbaths.export_pdf',
            'label' => 'Verifikasi Proses Pasteurisasi Produk di Waterbath',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.waterbath',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'freez_packaging' => [
            'model' => \App\Models\DetailFreezPackaging::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportFreezPackaging::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product', 'freezing', 'kartoning', 'documentations', 'kartoningDocumentations'],
            'pdf_route' => 'report_freez_packagings.export_pdf',
            'label' => 'Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.freez_packaging',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'changeover' => [
            'model' => \App\Models\DetailChangeoverCleaning::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportChangeoverCleaning::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product'],
            'pdf_route' => 'report_changeover_cleanings.exportPdf',
            'label' => 'Pemeriksaan Kebersihan Setelah Change-Over',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.changeover',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        'foreign_object' => [
            'model' => \App\Models\DetailForeignObject::class,
            'column' => 'production_code',
            'search_columns' => ['production_code'],
            'search_relations' => [
                'product' => 'product_name',
            ],
            'form_model' => \App\Models\ReportForeignObject::class,
            'form_relation' => 'report',
            'detail_relation' => 'details',
            'detail_with' => ['product'],
            'pdf_route' => 'report-foreign-objects.export-pdf',
            'label' => 'Pemeriksaan Kontaminasi Benda Asing',
            'route_key_column' => 'uuid',
            'partial' => 'traceability.partials.foreign_object',
            'display_fields' => fn($detail) => [],

            'related' => [],
        ],

        



        // modul berikutnya ditambahkan sebagai entri array baru,
        // dengan alur yang sama dari Langkah 1 s.d. 8
    ],
];