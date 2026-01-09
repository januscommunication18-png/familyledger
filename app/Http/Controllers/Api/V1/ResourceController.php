<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\FamilyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * Get all family resources.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $resources = FamilyResource::where('tenant_id', $tenant->id)
            ->with(['files'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by document type
        $byType = $resources->groupBy('document_type')->map->count();

        return $this->success([
            'resources' => $resources->map(function ($resource) {
                return [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'document_type' => $resource->document_type,
                    'document_type_name' => ucfirst(str_replace('_', ' ', $resource->document_type ?? 'other')),
                    'description' => $resource->description,
                    'status' => $resource->status ?? 'active',
                    'status_name' => ucfirst($resource->status ?? 'active'),
                    'digital_copy_date' => $resource->digital_copy_date?->format('Y-m-d'),
                    'expiration_date' => $resource->expiration_date?->format('Y-m-d'),
                    'files' => $resource->files,
                ];
            }),
            'counts' => [
                'emergency' => $byType['emergency'] ?? 0,
                'evacuation' => $byType['evacuation_plan'] ?? 0,
                'fire' => $byType['fire_extinguisher'] ?? 0,
                'rental' => $byType['rental_agreement'] ?? 0,
                'warranty' => $byType['home_warranty'] ?? 0,
                'other' => $byType['other'] ?? 0,
            ],
            'total' => $resources->count(),
        ]);
    }

    /**
     * Get resources by type.
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $resources = FamilyResource::where('tenant_id', $tenant->id)
            ->where('document_type', $type)
            ->with(['files'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'resources' => $resources,
            'total' => $resources->count(),
        ]);
    }

    /**
     * Get a single resource.
     */
    public function show(Request $request, FamilyResource $resource): JsonResponse
    {
        $user = $request->user();

        if ($resource->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $resource->load(['files', 'creator']);

        // Transform files for mobile
        $files = $resource->files->map(function ($file) use ($resource) {
            return [
                'id' => $file->id,
                'name' => $file->original_name,
                'file_path' => $file->file_path,
                'mime_type' => $file->mime_type,
                'file_size' => $file->file_size,
                'formatted_size' => $this->formatFileSize($file->file_size),
                'is_image' => str_starts_with($file->mime_type ?? '', 'image/'),
                'is_pdf' => $file->mime_type === 'application/pdf',
                'download_url' => route('family-resources.download', [$resource->id, $file->id]),
                'view_url' => route('family-resources.view', [$resource->id, $file->id]),
                'created_at' => $file->created_at?->format('M d, Y'),
            ];
        });

        return $this->success([
            'resource' => [
                'id' => $resource->id,
                'name' => $resource->name,
                'document_type' => $resource->document_type,
                'document_type_name' => $resource->document_type_name,
                'custom_document_type' => $resource->custom_document_type,
                'description' => $resource->description,
                'original_location' => $resource->original_location,
                'notes' => $resource->notes,
                'status' => $resource->status ?? 'active',
                'status_name' => ucfirst($resource->status ?? 'active'),
                'digital_copy_date' => $resource->digital_copy_date?->format('M d, Y'),
                'digital_copy_date_raw' => $resource->digital_copy_date?->format('Y-m-d'),
                'expiration_date' => $resource->expiration_date?->format('M d, Y'),
                'expiration_date_raw' => $resource->expiration_date?->format('Y-m-d'),
                'is_expired' => $resource->expiration_date && $resource->expiration_date->isPast(),
                'created_by' => $resource->creator ? [
                    'id' => $resource->creator->id,
                    'name' => $resource->creator->name,
                ] : null,
                'created_at' => $resource->created_at?->format('M d, Y'),
                'updated_at' => $resource->updated_at?->format('M d, Y'),
            ],
            'files' => $files,
            'stats' => [
                'total_files' => $files->count(),
                'images' => $files->where('is_image', true)->count(),
                'documents' => $files->where('is_image', false)->count(),
            ],
        ]);
    }

    /**
     * Format file size to human readable string.
     */
    private function formatFileSize(?int $bytes): string
    {
        if (!$bytes) {
            return 'Unknown';
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
