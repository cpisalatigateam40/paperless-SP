<?php

/**
 * Master daftar jenis laporan (report_type) untuk relasi dengan
 * modul FormNumber (nomor form per area).
 *
 * Key HARUS sama persis dengan yang dipakai saat memanggil
 * FormNumber::get($areaId, 'key_disini') di controller export PDF.
 */

return [
    // Meat Preparation
    'report_rm_arrivals' => 'Pemeriksaan Kedatangan Bahan Baku dan Bahan Penunjang',
    'report_premixes' => 'Pemeriksaan Premix',
    'report_emulsion_makings' => 'Verifikasi Pembuatan Emulsi / CCM Block',
    'report_metal_detectors' => 'Pemeriksaan Metal Detector Adonan',
    'report_process_productions' => 'Verifikasi Proses Produksi',
    'report_weight_stuffers' => 'Verifikasi Berat Stuffer',
    'report_siomays' => 'Pembuatan Kulit Siomay',
    'report_thawings' => 'Pemeriksaan Proses Thawing',
    'report_mt_cleans' => 'Pemeriksaan Kebersihan Magnet Trap',

    // Cooking
    'report_smoke_houses' => 'Cooking Smoke House',
    'report_steamer_cookings' => 'Verifikasi Proses Pemasakan di Steamer',
    'report_baso_cookings' => 'Verifikasi Pemasakan Baso',
    'report_sauces' => 'Pemasakan Produk Di Steam Kettle',

    // Packing
    'report_packaging_verifs' => 'Verifikasi Kemasan Plastik',
    'report_md_products' => 'Pemeriksaan Metal Detector Produk',
    'report_tofu_verifs' => 'Verifikasi Produk Tofu',
    'report_lab_samples' => 'Verifikasi Pengambilan Sample',
    'report_startup_labels' => 'Pemeriksaan Labelisasi Startup',

    // Pasteurizing
    'report_pasteurs' => 'Pemeriksaan Pasteurisasi Retort Chamber',
    'report_waterbaths' => 'Pemeriksaan Pasteurisasi Waterbath',

    // Cartoning
    'report_freez_packagings' => 'Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk',

    // Verifikasi Non Proses
    'storage_rm_cleanliness' => 'Kebersihan Area Penyimpanan Bahan',
    'process_area_cleanliness' => 'Kebersihan Area Proses',
    'gmp_employee' => 'GMP Karyawan & Kontrol Sanitasi',
    'report_fragile_item' => 'Barang Mudah Pecah',
    'report_re_cleanliness' => 'Kebersihan Ruangan, Mesin, dan Peralatan',
    'report_scales' => 'Timbangan & Thermometer',
    'report_changeover_cleanings' => 'Pemeriksaan Kebersihan Setelah Pergantian Produk',

    // Verifikasi dan Penanganan Ketidaksesuaian
    'report_production_nonconformities' => 'Pemeriksaan Ketidaksesuaian Proses Produksi',
    'report_foreign_objects' => 'Pemeriksaan Kontaminasi Benda Asing',

    //audit
    'report_audit_packing_primers' => 'Checklist Audit Kepatuhan Proses Packing Primer'
];