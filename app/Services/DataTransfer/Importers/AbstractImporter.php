<?php

namespace App\Services\DataTransfer\Importers;

use App\Services\DataTransfer\Contracts\Importer;
use Illuminate\Http\UploadedFile;

abstract class AbstractImporter implements Importer
{
    protected function pathFrom(string|UploadedFile $file): string
    {
        if ($file instanceof UploadedFile) {
            return $file->getRealPath();
        }
        return $file;
    }
}
