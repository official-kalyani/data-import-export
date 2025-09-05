<?php

namespace App\Services\DataTransfer\Importers;

class JsonImporter extends AbstractImporter
{
    public function import(string|\Illuminate\Http\UploadedFile $file): array
    {
        $path = $this->pathFrom($file);
        $content = file_get_contents($path);
        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return [];
        }
       
        return array_values($decoded);
    }

    public function extension(): string { return 'json'; }
}
