<?php

namespace App\Services\DataTransfer\Contracts;

interface Exporter
{
    /**
     * @param iterable|array $data Array/Collection of arrays or Eloquent models
     * @return string Formatted string payload
     */
    public function export(iterable $data): string;

    /** File extension without dot (e.g., 'csv', 'json', 'xml') */
    public function extension(): string;

    /** MIME type for the exported content */
    public function mime(): string;
}
