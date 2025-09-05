<?php

namespace App\Services\DataTransfer\MultiFormat;

use App\Services\DataTransfer\Exporters\CsvExporter;
use App\Services\DataTransfer\Exporters\JsonExporter;
use App\Services\DataTransfer\Exporters\XmlExporter;
use Illuminate\Support\Facades\Storage;

class MultiFormatExporter
{
    public function exportAll(iterable $data, array $formats, string $baseName): array
    {
        $paths = [];
        foreach ($formats as $format) {
            $exporter = match (strtolower($format)) {
                'csv'  => app(CsvExporter::class),
                'json' => app(JsonExporter::class),
                'xml'  => app(XmlExporter::class),
                default => null
            };
            if (!$exporter) { continue; }

            $payload = $exporter->export($data);
            $path = "exports/{$baseName}.{$exporter->extension()}";
            Storage::put($path, $payload);
            $paths[] = $path;
        }
        return $paths;
    }
}
