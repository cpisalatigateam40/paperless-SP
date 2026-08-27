<?php

namespace App\Support;

class ChangeoverCriteria
{
    public static function options(): array
    {
        return [
            1 => 'Bersih, tidak ada sisa bahan/kemasan sebelumnya',
            2 => 'Ada sisa bahan/kemasan sebelumnya',
            3 => 'Bebas dari kontaminasi dan bahan sebelumnya',
            4 => 'Ada kontaminasi atau sisa bahan sebelumnya',
            5 => 'Bebas dari potensi kontaminasi allergen',
            6 => 'Ada potensi kontaminasi allergen',
            7 => 'Bersih, tidak ada kontaminan/kotoran, tidak tercium bau menyimpang',
            8 => 'Tidak bersih, ada kontaminan/kotoran',
        ];
    }

    // Dikelompokkan berpasangan sesuai kolom di form: 1/2, 3/4, 5/6, 7/8
    public static function pairs(): array
    {
        return [
            [1, 2],
            [3, 4],
            [5, 6],
            [7, 8],
        ];
    }
}