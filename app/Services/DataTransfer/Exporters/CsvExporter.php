<?php

namespace App\Services\DataTransfer\Exporters;

use SplTempFileObject;

class CsvExporter extends AbstractExporter
{
    public function export(iterable $data): string
    {
        $normalized = $this->normalize($data);
        $tmp = new SplTempFileObject();
        $tmp->fputcsv($normalized['headers']);
        foreach ($normalized['rows'] as $row) {
            $tmp->fputcsv(array_values($row));
        }
        $tmp->rewind();
        $csv = '';
        while (!$tmp->eof()) {
            $csv .= $tmp->fgets();
        }
        return $csv;
    }

    public function extension(): string { return 'csv'; }
    public function mime(): string { return 'text/csv'; }
}
