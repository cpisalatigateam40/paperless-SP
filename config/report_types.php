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
    'report_rm_arrivals' => 'Verifikasi Bahan Baku dan Bahan Penunjang',
    'report_premixes' => 'Pemeriksaan Premix',
    'report_emulsion_makings' => 'Verifikasi Proses Pembuatan Emulsi',
    'report_metal_detectors' => 'Verifikasi Kinerja Metal Detector Adonan',
    'report_process_productions' => 'Verifikasi Proses Mixing, Chopping, dan Emulsifying',
    'report_weight_stuffers' => 'Verifikasi Proses Stuffing',
    'report_siomays' => 'Verifikasi Proses Pembuatan Kulit Siomay/Gyoza',
    'report_thawings' => 'Verifikasi Proses Thawing',
    'report_mt_cleans' => 'Pemeriksaan Kebersihan Magnet Trap',

    // Cooking
    'report_smoke_houses' => 'Verifikasi Proses Pemasakan di Smoke House',
    'report_steamer_cookings' => 'Verifikasi Proses Pemasakan di Steamer',
    'report_boiling_tanks' => 'Verifikasi Proses Pemasakan di Boiling Tank',
    'report_sauces' => 'Verifikasi Proses Pemasakan di Steam Kettle',

    // Packing
    'report_packaging_verifs' => 'Verifikasi Proses Pengemasan',
    'report_md_products' => 'Verifikasi Kinerja Metal Detector Produk',
    'report_tofu_verifs' => 'Verifikasi Hasil Produksi Tofu',
    'report_lab_samples' => 'Form Pengambilan Sample',
    'report_startup_labels' => 'Verifikasi Labelisasi Start-Up',

    // Pasteurizing
    'report_pasteurs' => 'Verifikasi Proses Pasteurisasi Produk di Retort Chamber',
    'report_waterbaths' => 'Verifikasi Proses Pasteurisasi Produk di Waterbath',

    // Cartoning
    'report_freez_packagings' => 'Verifikasi Proses Pembekuan, Pengemasan Sekunder, dan Release Produk',

    // Verifikasi Non Proses
    'storage_rm_cleanliness' => 'Verifikasi Kondisi Ruang Penyimpanan Bahan Baku dan Bahan Penunjang',
    'process_area_cleanliness' => 'Verifikasi Kesesuaian Area Proses Produksi',
    'gmp' => 'Verifikasi Penerapan GMP Karyawan & Sanitasi Area',
    'report_fragile_item' => 'Pemeriksaan Barang Mudah Pecah (Glass & Brittle Plastic)',
    'report_re_cleanliness' => 'Pemeriksaan Kondisi Ruangan, Mesin, dan Peralatan',
    'report-alat-verifications' => 'Verifikasi Alat Ukur',
    'report_changeover_cleanings' => 'Pemeriksaan Kebersihan Setelah Change-Over',

    // Verifikasi dan Penanganan Ketidaksesuaian
    'report_production_nonconformities' => 'Laporan Ketidaksesuaian Proses Produksi',
    'report_foreign_objects' => 'Pemeriksaan Kontaminasi Benda Asing',

    //audit
    'report_audit_packing_primers' => 'Checklist Audit Kepatuhan Proses Packing Primer'
];