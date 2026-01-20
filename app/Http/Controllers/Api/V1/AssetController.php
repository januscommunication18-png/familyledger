<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\AssetResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API Controller for Assets (read-only for Phase 1).
 */
class AssetController extends Controller
{
    /**
     * Get all assets for the current tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $assets = Asset::where('tenant_id', $user->tenant_id)
            ->with(['owners.familyMember'])
            ->withCount('documents')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals by category
        $categoryTotals = $assets->groupBy('asset_category')->map(function ($items, $category) {
            return [
                'count' => $items->count(),
                'total_value' => $items->sum('current_value'),
            ];
        });

        return $this->success([
            'assets' => AssetResource::collection($assets),
            'total' => $assets->count(),
            'total_value' => $assets->sum('current_value'),
            'by_category' => $categoryTotals,
        ]);
    }

    /**
     * Get assets by category.
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        $user = $request->user();

        $validCategories = ['property', 'vehicle', 'valuable', 'inventory'];
        if (!in_array($category, $validCategories)) {
            return $this->error('Invalid category', 400);
        }

        $assets = Asset::where('tenant_id', $user->tenant_id)
            ->where('asset_category', $category)
            ->with(['owners.familyMember'])
            ->withCount('documents')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'category' => $category,
            'assets' => AssetResource::collection($assets),
            'total' => $assets->count(),
            'total_value' => $assets->sum('current_value'),
        ]);
    }

    /**
     * Get a specific asset with details.
     */
    public function show(Request $request, Asset $asset): JsonResponse
    {
        $user = $request->user();

        // Ensure the asset belongs to the user's tenant
        if ($asset->tenant_id !== $user->tenant_id) {
            return $this->notFound('Asset not found');
        }

        // Load relationships
        $asset->load([
            'owners.familyMember',
            'documents',
        ]);
        $asset->loadCount('documents');

        // Transform documents with DO Spaces URLs
        $files = $asset->documents->map(function ($doc) {
            $isImage = $doc->isImage();
            $isPdf = $doc->isPdf();
            $fileUrl = $doc->file_path ? Storage::disk('do_spaces')->url($doc->file_path) : null;

            return [
                'id' => $doc->id,
                'name' => $doc->original_filename ?? 'File',
                'document_type' => $doc->document_type,
                'document_type_name' => $doc->document_type_name,
                'file_path' => $doc->file_path,
                'mime_type' => $doc->mime_type,
                'file_size' => $doc->file_size,
                'formatted_size' => $doc->formatted_file_size,
                'is_image' => $isImage,
                'is_pdf' => $isPdf,
                'download_url' => $fileUrl,
                'view_url' => ($isImage || $isPdf) ? $fileUrl : null,
                'created_at' => $doc->created_at?->toISOString(),
            ];
        });

        return $this->success([
            'asset' => new AssetResource($asset),
            'files' => $files,
        ]);
    }
}
