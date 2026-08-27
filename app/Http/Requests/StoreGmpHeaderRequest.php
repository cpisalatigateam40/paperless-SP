<?php
// app/Http/Requests/StoreGmpHeaderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGmpHeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'date' => ['required', 'date'],
            'shift' => ['nullable', 'string'], // otomatis dari session kalau QC Inspector, jadi nullable
            'section' => ['required', 'in:gmp_karyawan,sanitasi_area'],
            'known_by' => ['nullable', 'string'],
            'approved_by' => ['nullable', 'string'],
            'waktu' => ['required', 'array', 'min:1'],
            'waktu.*.jam_pemeriksaan' => ['nullable', 'date_format:H:i'],
            'waktu.*.catatan' => ['nullable', 'string'],
        ];

        if ($this->input('section') === 'gmp_karyawan') {
            $rules += [
                'waktu.*.employees' => ['required', 'array', 'min:1'],
                'waktu.*.employees.*.section_uuid' => ['required', 'uuid', 'exists:sections,uuid'],
                'waktu.*.employees.*.employee_name' => ['required', 'string'],
                'waktu.*.employees.*.seragam_apd_lengkap' => ['nullable', 'boolean'],
                'waktu.*.employees.*.sarung_tangan_utuh' => ['nullable', 'boolean'],
                'waktu.*.employees.*.sepatu_boots_bersih' => ['nullable', 'boolean'],
                'waktu.*.employees.*.tidak_pakai_perhiasan' => ['nullable', 'boolean'],
                'waktu.*.employees.*.kuku_tangan_bersih' => ['nullable', 'boolean'],
                'waktu.*.employees.*.kuku_tidak_panjang' => ['nullable', 'boolean'],
                'waktu.*.employees.*.perilaku_kerja' => ['nullable', 'boolean'],
                'waktu.*.employees.*.potensi_cross_contamination' => ['nullable', 'boolean'],
                'waktu.*.employees.*.tindakan_koreksi' => ['nullable', 'string'],
                'waktu.*.employees.*.keterangan' => ['nullable', 'string'],
            ];
        } else {
            $rules += [
                'waktu.*.sanitations' => ['required', 'array', 'min:1'],
                'waktu.*.sanitations.*.section_uuid' => ['required', 'uuid', 'exists:sections,uuid'],
                'waktu.*.sanitations.*.item_verifikasi' => ['required', 'string'],
                'waktu.*.sanitations.*.standar_klorin' => ['nullable', 'numeric'],
                'waktu.*.sanitations.*.kadar_klorin' => ['nullable', 'numeric'],
                'waktu.*.sanitations.*.suhu' => ['nullable', 'numeric'],
                'waktu.*.sanitations.*.tindakan_koreksi' => ['nullable', 'string'],
                'waktu.*.sanitations.*.keterangan' => ['nullable', 'string'],
            ];
        }

        return $rules;
    }
}