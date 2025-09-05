<?php

namespace App\Http\Controllers;

use App\Jobs\DataExport;
use App\Jobs\DataImport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DataController extends Controller
{
    
    public function export(Request $request)
    {
        $validated = $request->validate([
            'model' => ['required', 'string'],
            'format' => ['required', 'in:csv,json,xml'],
            'filename' => ['nullable', 'string']
        ]);

        $modelClass = $this->resolveModelClass($validated['model']);

        dispatch(new DataExport(
            modelClass: $modelClass,
            format: $validated['format'],
            filename: $validated['filename'] ?? null
        ));

        return response()->json([
            'status' => 'queued',
            'message' => 'Export job dispatched. File will be saved under storage/app/exports.',
        ]);
    }

    
    public function import(Request $request)
    {
        $validated = $request->validate([
            'model' => ['required', 'string'],
            'file'  => ['required', 'file', 'mimes:csv,json,txt'] // txt in case JSON with txt
        ]);

        $modelClass = $this->resolveModelClass($validated['model']);

        dispatch(new DataImport(
            modelClass: $modelClass,
            file: $validated['file']
        ));

        return response()->json([
            'status' => 'queued',
            'message' => 'Import job dispatched. Records will be inserted in the background.',
        ]);
    }

    protected function resolveModelClass(string $model): string
    {
        if (class_exists($model)) {
            return $model;
        }
        
        $fqcn = 'App\\Models\\' . ltrim($model, '\\');
        if (class_exists($fqcn)) {
            return $fqcn;
        }
        abort(422, "Model not found: {$model}");
    }
}
