<?php

namespace App\Models\Traits;

use App\Models\Premix;
use App\Models\RawMaterial;

trait HasMaterialImportOptions
{
    private static ?array $materialOptionsCache = null;

    // SAMAKAN query dan urutannya dengan method create() di controller form
    private static function materialOptions(): array
    {
        if (self::$materialOptionsCache === null) {
            $list = [];
            foreach (RawMaterial::orderBy('material_name')->get() as $m) {
                $list[] = ['label' => $m->material_name, 'uuid' => $m->uuid, 'type' => 'raw'];
            }
            foreach (Premix::orderBy('name')->get() as $p) {
                $list[] = ['label' => $p->name . ' (Premix)', 'uuid' => $p->uuid, 'type' => 'premix'];
            }
            self::$materialOptionsCache = $list;
        }
        return self::$materialOptionsCache;
    }

    // Label dropdown -> ['label', 'uuid', 'type']; lempar exception bila tidak ada / ganda
    private static function resolveMaterial(string $name): array
    {
        $name  = trim($name);
        $match = array_values(array_filter(
            self::materialOptions(),
            fn ($o) => mb_strtolower(trim($o['label'])) === mb_strtolower($name)
        ));

        if (count($match) === 0) {
            throw new \RuntimeException("Bahan \"{$name}\" tidak ditemukan.");
        }
        if (count($match) > 1) {
            throw new \RuntimeException("Bahan \"{$name}\" ganda, tidak bisa dibedakan.");
        }
        return $match[0];
    }
}