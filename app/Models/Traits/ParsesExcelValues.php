<?php

namespace App\Models\Traits;

trait ParsesExcelValues
{
    // Shift cadangan bila kolom Shift di Excel kosong
    private static function defaultShift(): string
    {
        return auth()->user()->hasRole('QC Inspector') && session('shift_number') && session('shift_group')
            ? session('shift_number') . '-' . session('shift_group')
            : 'NON-SHIFT';
    }

    // Serial Excel / DateTime / "2026-10-27" / "27/10/2026" -> "Y-m-d" (atau null)
    private static function toDate($v): ?string
    {
        if ($v === null || $v === '') return null;
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');

        if (is_int($v) || is_float($v) || (is_string($v) && is_numeric($v))) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v)->format('Y-m-d');
        }

        $s = trim((string) $v);
        foreach (['Y-m-d', 'j/n/Y', 'j-n-Y', 'j.n.Y'] as $format) {
            $d   = \DateTime::createFromFormat('!' . $format, $s);
            $err = \DateTime::getLastErrors();
            if ($d && (!$err || ($err['warning_count'] === 0 && $err['error_count'] === 0))) {
                return $d->format('Y-m-d');
            }
        }
        return null;
    }

    // "08:30" / "8.30" / serial 0.3541666 / DateTime -> "H:i" (atau null)
    private static function toHi($v): ?string
    {
        if ($v === null || $v === '') return null;
        if ($v instanceof \DateTimeInterface) return $v->format('H:i');
        if (is_int($v) || is_float($v)) {
            $secs = (int) round(fmod((float) $v, 1) * 86400);
            return sprintf('%02d:%02d', intdiv($secs, 3600) % 24, intdiv($secs % 3600, 60));
        }
        if (preg_match('/^(\d{1,2})[:.](\d{2})/', trim((string) $v), $m) && $m[1] < 24 && $m[2] < 60) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }
        return null;
    }

    // ✓ / x / OK / 1 / true -> bool
    private static function toBool($v, string $label): bool
    {
        if (is_bool($v)) return $v;

        $s = mb_strtolower(trim((string) $v));
        if (in_array($s, ['✓', '√', '✔', 'v', 'ok', '1', 'true', 'ya', 'yes'], true)) return true;
        if (in_array($s, ['x', '×', 'nok', 'tidak ok', '0', 'false', 'tidak', 'no'], true)) return false;

        throw new \RuntimeException("Nilai \"{$v}\" pada kolom {$label} tidak valid, gunakan ✓ atau x.");
    }

    // Sama dengan calculateExpirationDate() di JavaScript form (24 bulan dari kode batch)
    private static function bestBeforeFromCode(string $code, int $months = 24): ?string
    {
        if (!preg_match('/([A-Z]{2}\d{2})/i', $code, $m)) {
            return null;
        }

        $batch = strtoupper($m[1]);
        $year  = 2009 + (ord($batch[0]) - ord('A'));
        $month = ord($batch[1]) - ord('A') + 1;
        $day   = (int) substr($batch, 2, 2);

        if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        return \Carbon\Carbon::create($year, $month, $day)
            ->addMonthsNoOverflow($months)
            ->toDateString();
    }

        // Cocokkan nilai Excel ke daftar resmi tanpa peduli huruf besar/kecil
    private static function matchOption(string $value, array $options): string
    {
        $v = mb_strtolower(trim($value));
        foreach ($options as $opt) {
            if (mb_strtolower($opt) === $v) {
                return $opt;
            }
        }
        return trim($value);
    }

        // OK / Tidak OK / ✓ / x / 1 / 0 -> 1 atau 0
    private static function parseVerification($v, string $label): int
    {
        try {
            return self::toBool($v, $label) ? 1 : 0;
        } catch (\RuntimeException $e) {
            throw new \RuntimeException("Nilai \"{$v}\" pada kolom {$label} tidak valid, gunakan OK atau Tidak OK.");
        }
    }
}