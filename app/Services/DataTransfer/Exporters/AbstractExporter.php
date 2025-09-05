<?php

namespace App\Services\DataTransfer\Exporters;

use App\Services\DataTransfer\Contracts\Exporter;
use Illuminate\Support\Arr;

abstract class AbstractExporter implements Exporter
{
   
    protected function normalize(iterable $data): array
    {
        $rows = [];
        foreach ($data as $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $rows[] = $item->toArray();
            } elseif (is_array($item)) {
                $rows[] = $item;
            } else {
                $rows[] = (array) $item;
            }
        }
        $allKeys = [];
        foreach ($rows as $row) {
            $allKeys = array_unique(array_merge($allKeys, array_keys($row)));
        }
        $normalized = [];
        foreach ($rows as $row) {
            $normalized[] = Arr::only($row, $allKeys) + array_fill_keys(array_diff($allKeys, array_keys($row)), null);
        }
        return ['headers' => $allKeys, 'rows' => $normalized];
    }
}
