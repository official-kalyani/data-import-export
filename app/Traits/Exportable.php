<?php

namespace App\Traits;

use App\Services\DataTransfer\Exporters\CsvExporter;
use App\Services\DataTransfer\Exporters\JsonExporter;
use App\Services\DataTransfer\Exporters\XmlExporter;
use Illuminate\Support\Collection;

trait Exportable
{
    public function scopeExportAll($query, string $format = 'csv'): string
    {
        $data = $query->get(); 
        $exporter = $this->resolveExporter($format);
        return $exporter->export($data);
    }

    protected function resolveExporter(string $format)
    {
        return match (strtolower($format)) {
            'csv'  => app(CsvExporter::class),
            'json' => app(JsonExporter::class),
            'xml'  => app(XmlExporter::class),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}")
        };
    }
}
