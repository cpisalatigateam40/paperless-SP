<?php
// app/Services/Excel/Contracts/ImportableModel.php
namespace App\Services\Excel\Contracts;
interface ImportableModel
{
    public static function getImportMapping(): array;
    public static function getImportRules(): array;
    public static function getMasterDataFields(): array;
    public static function transformImportRow(array $row): array;
}
