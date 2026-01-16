<?php

namespace App\Http\Controllers;

use App\Models\FamilyCircle;
use App\Models\FamilyResource;
use App\Models\FamilyResourceFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FamilyResourceController extends Controller
{
    /**
     * Parse date from separate month/day/year fields.
     */
    private function parseDate(Request $request, string $prefix): ?string
    {
        $month = $request->input($prefix . '_month');
        $day = $request->input($prefix . '_day');
        $year = $request->input($prefix . '_year');

        if ($month && $day && $year) {
            try {
                return Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Display the family resources landing page.
     */
    public function index()
    {
        $user = Auth::user();

        $resources = FamilyResource::where('tenant_id', $user->tenant_id)
            ->with(['files', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group resources by type
        $resourcesByType = $resources->groupBy('document_type');

        // Get counts
        $counts = [
            'total' => $resources->count(),
            'active' => $resources->where('status', FamilyResource::STATUS_ACTIVE)->count(),
            'emergency' => $resources->where('document_type', FamilyResource::TYPE_EMERGENCY)->count(),
            'evacuation' => $resources->where('document_type', FamilyResource::TYPE_EVACUATION_PLAN)->count(),
            'fire' => $resources->where('document_type', FamilyResource::TYPE_FIRE_EXTINGUISHER)->count(),
            'rental' => $resources->where('document_type', FamilyResource::TYPE_RENTAL_AGREEMENT)->count(),
            'warranty' => $resources->where('document_type', FamilyResource::TYPE_HOME_WARRANTY)->count(),
            'other' => $resources->where('document_type', FamilyResource::TYPE_OTHER)->count(),
        ];

        return view('pages.family-resources.index', [
            'resources' => $resources,
            'resourcesByType' => $resourcesByType,
            'counts' => $counts,
            'documentTypes' => FamilyResource::DOCUMENT_TYPES,
            'statuses' => FamilyResource::STATUSES,
        ]);
    }

    /**
     * Show the form for creating a new family resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $selectedType = $request->get('type');
        $selectedFamilyCircleId = $request->get('family_circle_id');

        $familyCircles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        return view('pages.family-resources.form', [
            'resource' => null,
            'documentTypes' => FamilyResource::DOCUMENT_TYPES,
            'statuses' => FamilyResource::STATUSES,
            'selectedType' => $selectedType,
            'familyCircles' => $familyCircles,
            'selectedFamilyCircleId' => $selectedFamilyCircleId,
        ]);
    }

    /**
     * Store a newly created family resource.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(FamilyResource::DOCUMENT_TYPES)),
            'custom_document_type' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'original_location' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:' . implode(',', array_keys(FamilyResource::STATUSES)),
            'family_circle_id' => 'nullable|exists:family_circles,id',
            'digital_copy_date' => 'nullable|date',
        ]);

        $user = Auth::user();

        $data = $validated;

        // Parse date from MM/DD/YYYY format
        if (!empty($data['digital_copy_date'])) {
            try {
                $data['digital_copy_date'] = Carbon::createFromFormat('m/d/Y', $data['digital_copy_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $data['digital_copy_date'] = null;
            }
        }

        $data['tenant_id'] = $user->tenant_id;
        $data['created_by'] = $user->id;
        $data['status'] = $data['status'] ?? FamilyResource::STATUS_ACTIVE;

        $resource = FamilyResource::create($data);

        // Handle file uploads
        if ($request->hasFile('files')) {
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/heic',
                'image/heif',
                'image/heic-sequence',
                'image/heif-sequence',
                'application/octet-stream', // Sometimes HEIC files are detected as this
            ];
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
            $maxSize = 20 * 1024 * 1024; // 20MB

            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    $mimeType = $file->getMimeType();
                    $extension = strtolower($file->getClientOriginalExtension());

                    // Check mime type OR extension (for files with unrecognized mime types)
                    if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
                        continue;
                    }
                    if ($file->getSize() > $maxSize) {
                        continue;
                    }

                    $path = $file->store('documents/family-resources/' . $resource->id, 'private');

                    FamilyResourceFile::create([
                        'family_resource_id' => $resource->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $mimeType,
                        'file_size' => $file->getSize(),
                        'folder' => null,
                    ]);
                }
            }
        }

        return redirect()->route('family-resources.show', $resource)
            ->with('success', 'Family resource created successfully.');
    }

    /**
     * Display the specified family resource.
     */
    public function show(FamilyResource $familyResource)
    {
        if ($familyResource->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $familyResource->load(['files', 'creator']);

        return view('pages.family-resources.show', [
            'resource' => $familyResource,
            'documentTypes' => FamilyResource::DOCUMENT_TYPES,
            'statuses' => FamilyResource::STATUSES,
        ]);
    }

    /**
     * Show the form for editing the specified family resource.
     */
    public function edit(FamilyResource $familyResource)
    {
        $user = Auth::user();

        if ($familyResource->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $familyResource->load('files');

        $familyCircles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        return view('pages.family-resources.form', [
            'resource' => $familyResource,
            'documentTypes' => FamilyResource::DOCUMENT_TYPES,
            'statuses' => FamilyResource::STATUSES,
            'selectedType' => null,
            'familyCircles' => $familyCircles,
        ]);
    }

    /**
     * Update the specified family resource.
     */
    public function update(Request $request, FamilyResource $familyResource)
    {
        if ($familyResource->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(FamilyResource::DOCUMENT_TYPES)),
            'custom_document_type' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'original_location' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:' . implode(',', array_keys(FamilyResource::STATUSES)),
            'family_circle_id' => 'nullable|exists:family_circles,id',
            'digital_copy_date' => 'nullable|date',
        ]);

        $data = $validated;

        // Parse date from MM/DD/YYYY format
        if (!empty($data['digital_copy_date'])) {
            try {
                $data['digital_copy_date'] = Carbon::createFromFormat('m/d/Y', $data['digital_copy_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $data['digital_copy_date'] = null;
            }
        }

        $familyResource->update($data);

        // Handle new file uploads
        if ($request->hasFile('files')) {
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/heic',
                'image/heif',
                'image/heic-sequence',
                'image/heif-sequence',
                'application/octet-stream', // Sometimes HEIC files are detected as this
            ];
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
            $maxSize = 20 * 1024 * 1024; // 20MB

            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    $mimeType = $file->getMimeType();
                    $extension = strtolower($file->getClientOriginalExtension());

                    // Check mime type OR extension (for files with unrecognized mime types)
                    if (!in_array($mimeType, $allowedMimes) && !in_array($extension, $allowedExtensions)) {
                        continue;
                    }
                    if ($file->getSize() > $maxSize) {
                        continue;
                    }

                    $path = $file->store('documents/family-resources/' . $familyResource->id, 'private');

                    FamilyResourceFile::create([
                        'family_resource_id' => $familyResource->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $mimeType,
                        'file_size' => $file->getSize(),
                        'folder' => null,
                    ]);
                }
            }
        }

        return redirect()->route('family-resources.show', $familyResource)
            ->with('success', 'Family resource updated successfully.');
    }

    /**
     * Remove the specified family resource.
     */
    public function destroy(FamilyResource $familyResource)
    {
        if ($familyResource->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete all files from storage
        foreach ($familyResource->files as $file) {
            Storage::disk('private')->delete($file->file_path);
        }

        $familyResource->delete();

        return redirect()->route('family-resources.index')
            ->with('success', 'Family resource deleted successfully.');
    }

    /**
     * Download a file.
     */
    public function downloadFile(FamilyResource $familyResource, FamilyResourceFile $file)
    {
        if ($familyResource->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($file->family_resource_id !== $familyResource->id) {
            abort(404);
        }

        if (!Storage::disk('private')->exists($file->file_path)) {
            abort(404);
        }

        return Storage::disk('private')->download($file->file_path, $file->original_name);
    }

    /**
     * View a file (for images/PDFs).
     */
    public function viewFile(FamilyResource $familyResource, FamilyResourceFile $file)
    {
        if ($familyResource->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($file->family_resource_id !== $familyResource->id) {
            abort(404);
        }

        if (!Storage::disk('private')->exists($file->file_path)) {
            abort(404);
        }

        return Storage::disk('private')->response($file->file_path);
    }

    /**
     * Delete a file.
     */
    public function destroyFile(FamilyResource $familyResource, FamilyResourceFile $file)
    {
        if ($familyResource->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($file->family_resource_id !== $familyResource->id) {
            abort(404);
        }

        Storage::disk('private')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
