<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistPackagingDetail extends Model
{
    protected $table = 'checklist_packaging_details';

    protected $fillable = [
        'uuid',
        'detail_uuid',

        // In cutting manual
        'in_cutting_manual_1',
        'in_cutting_manual_2',
        'in_cutting_manual_3',
        'in_cutting_manual_4',
        'in_cutting_manual_5',

        // In cutting machine
        'in_cutting_machine_1',
        'in_cutting_machine_2',
        'in_cutting_machine_3',
        'in_cutting_machine_4',
        'in_cutting_machine_5',

        // Packaging thermoformer
        'packaging_thermoformer_1',
        'packaging_thermoformer_2',
        'packaging_thermoformer_3',
        'packaging_thermoformer_4',
        'packaging_thermoformer_5',

        // Packaging manual
        'packaging_manual_1',
        'packaging_manual_2',
        'packaging_manual_3',
        'packaging_manual_4',
        'packaging_manual_5',

        // Sealing condition
        'sealing_condition_1',
        'sealing_condition_2',
        'sealing_condition_3',
        'sealing_condition_4',
        'sealing_condition_5',

        // Sealing vacuum
        'sealing_vacuum_1',
        'sealing_vacuum_2',
        'sealing_vacuum_3',
        'sealing_vacuum_4',
        'sealing_vacuum_5',

        // Content per pack
        'content_per_pack_1',
        'content_per_pack_2',
        'content_per_pack_3',
        'content_per_pack_4',
        'content_per_pack_5',

        // Berat
        'standard_weight',
        'actual_weight_1',
        'actual_weight_2',
        'actual_weight_3',
        'actual_weight_4',
        'actual_weight_5',
    ];


    public function detail()
    {
        return $this->belongsTo(DetailPackagingVerif::class, 'detail_uuid', 'uuid');
    }
}