<?php

namespace App\Traits;

use App\Services\DataTransfer\Importers\CsvImporter;
use App\Services\DataTransfer\Importers\JsonImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

trait Importable
{
    
    public static function importFromFile(string|UploadedFile $file): int
    {
        $ext = strtolower(is_string($file) ? pathinfo($file, PATHINFO_EXTENSION) : $file->getClientOriginalExtension());

        $importer = match ($ext) {
            'csv'  => app(CsvImporter::class),
            'json' => app(JsonImporter::class),
            default => throw new \InvalidArgumentException("Unsupported import extension: {$ext}")
        };

        $rows = $importer->import($file);
        $count = 0;

        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $row) {
                static::query()->create($row);
                $count++;
            }
        });

        return $count;
    }
}
