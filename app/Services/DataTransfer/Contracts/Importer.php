<?php

namespace App\Services\DataTransfer\Contracts;

use Illuminate\Http\UploadedFile;

interface Importer
{
    /**
     * @param string|UploadedFile $file Path or uploaded file
     * @return array Parsed rows as array of associative arrays
     */
    public function import(string|UploadedFile $file): array;

    /** Supported extension (e.g., 'csv', 'json') */
    public function extension(): string;
}
