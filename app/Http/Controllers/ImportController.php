<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Services\FrolloImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index')->with('page', 'import');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'import_service' => 'required|string|in:FrolloImportService,ExcelImportService',
        ]);

        try {
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();

            $serviceClass = "App\\Services\\" . $request->input('import_service');
            $importService = new $serviceClass(Auth::id());
            $stats = $importService->importCsv($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully!',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
