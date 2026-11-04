<?php
// app/Services/Excel/Contracts/CustomImportRow.php
namespace App\Services\Excel\Contracts;
interface CustomImportRow
{
 public static function importRow(array $row): void;
}
