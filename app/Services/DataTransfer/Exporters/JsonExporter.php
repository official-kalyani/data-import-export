<?php

namespace App\Services\DataTransfer\Exporters;

class JsonExporter extends AbstractExporter
{
    public function export(iterable $data): string
    {
        $rows = [];
        foreach ($data as $item) {
            $rows[] = is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : (array) $item;
        }
        return json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function extension(): string { return 'json'; }
    public function mime(): string { return 'application/json'; }
}
