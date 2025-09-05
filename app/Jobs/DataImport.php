<?php

namespace App\Jobs;

use App\Services\DataTransfer\Importers\CsvImporter;
use App\Services\DataTransfer\Importers\JsonImporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var class-string<Model> */
    public string $modelClass;
    public string $storedPath; // relative to storage/app
    public ?string $extension;

    /**
     * @param class-string $modelClass  Model to insert into
     * @param string|UploadedFile $file path or uploaded file
     */
    public function __construct(string $modelClass, string|UploadedFile $file)
    {
        $this->modelClass = $modelClass;

        if ($file instanceof UploadedFile) {
            $this->extension = strtolower($file->getClientOriginalExtension());
            $this->storedPath = $file->store('imports');
        } else {
            $this->extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $filename = 'imports/' . basename($file);
            Storage::put($filename, file_get_contents($file));
            $this->storedPath = $filename;
        }
    }

    public function handle(): void
    {
        $path = storage_path('app/' . $this->storedPath);

        $importer = match ($this->extension) {
            'csv'  => app(CsvImporter::class),
            'json' => app(JsonImporter::class),
            default => throw new \InvalidArgumentException("Unsupported import extension: {$this->extension}")
        };

        $rows = $importer->import($path);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                ($this->modelClass)::query()->create($row);
            }
        });
    }
}
