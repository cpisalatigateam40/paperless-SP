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
            'detail_with' => [],
            'pdf_route' => 'report_metal_detectors.export_pdf',
            'label' => 'Verifikasi Kinerja Metal Detector Adonan',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_process_productions.export',
            'label' => 'Verifikasi Proses Mixing, Chopping, dan Emulsifying',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_weight_stuffers.export-pdf',
            'pdf_route_params' => function ($form, $group) {
                return [$form->uuid, $group->first()->uuid];
            },
            'label' => 'Verifikasi Proses Stuffing',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],

            'pdf_route' => 'report_siomays.export_pdf',
            'label' => 'Verifikasi Proses Pembuatan Kulit Siomay/Gyoza',
            'route_key_column' => 'uuid',

            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->report->product->product_name ?? '-',
                    'Kode Produksi' => $detail->report->production_code ?? '-',
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report-smoke-houses.export-pdf',
            'label' => 'Verifikasi Proses Pemasakan di Smoke House',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],

            'pdf_route' => 'report_steamer_cookings.export_pdf',
            'label' => 'Verifikasi Proses Pemasakan di Steamer',
            'route_key_column' => 'uuid',

            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->batch->report->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],

            'pdf_route' => 'report_boiling_tanks.export_pdf',
            'label' => 'Verifikasi Proses Pemasakan di Boiling Tank',
            'route_key_column' => 'uuid',

            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->report->product->product_name ?? '-',
                    'Kode Produksi' => $detail->report->product_code ?? '-',
                ];
            },

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
            'detail_with' => [],

            'pdf_route' => 'report_sauces.export_pdf',
            'label' => 'Verifikasi Proses Pemasakan di Steam Kettle',
            'route_key_column' => 'uuid',

            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->report->product->product_name ?? '-',
                    'Kode Produksi' => $detail->report->production_code ?? '-',
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_packaging_verifs.export-pdf',
            'label' => 'Verifikasi Proses Pengemasan',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_md_products.export-pdf',
            'label' => 'Verifikasi Kinerja Metal Detector Produk',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_lab_samples.export-pdf',
            'label' => 'Form Pengambilan Sample',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_pasteurs.export_pdf',
            'label' => 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->product_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_waterbaths.export_pdf',
            'label' => 'Verifikasi Proses Pasteurisasi Produk di Waterbath',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->batch_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_freez_packagings.export_pdf',
            'label' => 'Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report_changeover_cleanings.exportPdf',
            'label' => 'Pemeriksaan Kebersihan Setelah Change-Over',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

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
            'detail_with' => [],
            'pdf_route' => 'report-foreign-objects.export-pdf',
            'label' => 'Pemeriksaan Kontaminasi Benda Asing',
            'route_key_column' => 'uuid',
            'display_fields' => function ($detail) {
                return [
                    'Nama Produk' => $detail->product->product_name ?? '-',
                    'Kode Produksi' => $detail->production_code,
                ];
            },

            'related' => [],
        ],

        



        // modul berikutnya ditambahkan sebagai entri array baru,
        // dengan alur yang sama dari Langkah 1 s.d. 8
    ],
];