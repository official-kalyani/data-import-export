<?php

namespace App\Services\DataTransfer\Importers;

class CsvImporter extends AbstractImporter
{
    public function import(string|\Illuminate\Http\UploadedFile $file): array
    {
        $path = $this->pathFrom($file);
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $rows = [];
        $headers = [];
        $i = 0;
        while (($data = fgetcsv($handle)) !== false) {
            if ($i === 0) {
                $headers = $data;
            } else {
                $rows[] = array_combine($headers, $data + array_fill(0, max(0, count($headers) - count($data)), null));
            }
            $i++;
        }
        fclose($handle);
        return $rows;
    }

    public function extension(): string { return 'csv'; }
}
