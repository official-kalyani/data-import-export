<?php

namespace App\Jobs;

use App\Services\DataTransfer\Exporters\CsvExporter;
use App\Services\DataTransfer\Exporters\JsonExporter;
use App\Services\DataTransfer\Exporters\XmlExporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var class-string<\Illuminate\Database\Eloquent\Model> */
    public string $modelClass;
    public string $format;
    public ?string $filename;

    /**
     * @param class-string $modelClass  e.g., App\Models\User::class
     * @param string $format            csv|json|xml
     * @param string|null $filename     custom filename without extension
     */
    public function __construct(string $modelClass, string $format = 'csv', ?string $filename = null)
    {
        $this->modelClass = $modelClass;
        $this->format = strtolower($format);
        $this->filename = $filename;
    }

    public function handle(): void
    {
        /** @var Builder $query */
        $query = ($this->modelClass)::query();

        $exporter = match ($this->format) {
            'csv'  => app(CsvExporter::class),
            'json' => app(JsonExporter::class),
            'xml'  => app(XmlExporter::class),
            default => throw new \InvalidArgumentException("Unsupported format: {$this->format}")
        };

        $chunkSize = 2000;
        $contentPieces = [];

        $isCsv = $this->format === 'csv';
        $headersWritten = false;
        $csvHeaders = [];

        $query->chunk($chunkSize, function ($chunk) use (&$contentPieces, $exporter, $isCsv, &$headersWritten, &$csvHeaders) {
            if ($isCsv) {
                // Export CSV in chunks with consistent headers
                $normalized = (new \App\Services\DataTransfer\Exporters\CsvExporter())->export($chunk);
                // Split first line headers from body
                $lines = preg_split("/\r\n|\n|\r/", trim($normalized));
                if (!$headersWritten) {
                    $contentPieces[] = array_shift($lines); // headers
                    $headersWritten = true;
                } else {
                    // discard duplicate headers
                    array_shift($lines);
                }
                foreach ($lines as $line) {
                    if ($line !== '') $contentPieces[] = $line;
                }
            } else {
                $contentPieces[] = $exporter->export($chunk);
            }
        });

        $payload = '';
        if ($isCsv) {
            $payload = implode(PHP_EOL, $contentPieces) . PHP_EOL;
        } elseif ($this->format === 'json') {
            // Merge JSON arrays
            $arrays = array_map(fn($s) => json_decode($s, true), $contentPieces);
            $merged = [];
            foreach ($arrays as $arr) { $merged = array_merge($merged, $arr); }
            $payload = json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else { // xml
            // Re-wrap rows inside <rows>...</rows>
            $xmlBodies = array_map(function ($xml) {
                // get inner of <rows>...</rows>
                preg_match('/<rows>(.*)<\/rows>/s', $xml, $m);
                return $m[1] ?? '';
            }, $contentPieces);
            $payload = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rows>\n" . implode("\n", $xmlBodies) . "\n</rows>\n";
        }

        $name = $this->filename ?: class_basename($this->modelClass) . '_' . now()->format('Ymd_His');
        $path = "exports/{$name}.{$exporter->extension()}";

        Storage::put($path, $payload);
        
    }
}
