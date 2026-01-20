<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LegalDocumentResource;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegalDocumentApiController extends Controller
{
    /**
     * Display a listing of legal documents.
     */
    public function index(Request $request)
    {
        $query = LegalDocument::query()
            ->withCount('files')
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by family circle
        if ($request->has('family_circle_id')) {
            $query->where('family_circle_id', $request->family_circle_id);
        }

        $documents = $query->get();

        return response()->json([
            'legal_documents' => LegalDocumentResource::collection($documents),
            'total' => $documents->count(),
        ]);
    }

    /**
     * Display the specified legal document.
     */
    public function show(LegalDocument $legalDocument)
    {
        $legalDocument->load('files', 'familyCircle', 'attorney');

        // Build files array with URLs
        $files = $legalDocument->files->map(function ($file) {
            $isImage = in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            $isPdf = $file->mime_type === 'application/pdf';

            // Generate DO Spaces URL
            $fileUrl = $file->file_path ? Storage::disk('do_spaces')->url($file->file_path) : null;

            return [
                'id' => $file->id,
                'name' => $file->file_name ?? $file->name ?? 'File',
                'file_path' => $file->file_path,
                'mime_type' => $file->mime_type,
                'file_size' => $file->file_size,
                'formatted_size' => $this->formatFileSize($file->file_size),
                'is_image' => $isImage,
                'is_pdf' => $isPdf,
                'download_url' => $fileUrl,
                'view_url' => ($isImage || $isPdf) ? $fileUrl : null,
                'created_at' => $file->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'legal_document' => new LegalDocumentResource($legalDocument),
            'files' => $files,
            'family_circle' => $legalDocument->familyCircle ? [
                'id' => $legalDocument->familyCircle->id,
                'name' => $legalDocument->familyCircle->name,
            ] : null,
        ]);
    }

    /**
     * Format file size for display.
     */
    private function formatFileSize(?int $bytes): ?string
    {
        if (!$bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}