<?php

namespace App\Http\Controllers;

use App\Models\FamilyCircle;
use App\Models\LegalDocument;
use App\Models\LegalDocumentFile;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LegalDocumentController extends Controller
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
     * Display the legal documents landing page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = LegalDocument::where('tenant_id', $user->tenant_id)
            ->with(['files', 'attorney', 'creator']);

        // Filter by type if specified
        $filterType = $request->query('type');
        if ($filterType) {
            $typeMapping = [
                'will' => [LegalDocument::TYPE_WILL],
                'trust' => [LegalDocument::TYPE_TRUST],
                'power_of_attorney' => [LegalDocument::TYPE_POWER_OF_ATTORNEY, 'financial_poa', 'healthcare_poa'],
                'medical_directive' => [LegalDocument::TYPE_MEDICAL_DIRECTIVE, 'living_will', 'healthcare_proxy', 'dnr'],
                'other' => [LegalDocument::TYPE_OTHER],
            ];

            if (isset($typeMapping[$filterType])) {
                $query->whereIn('document_type', $typeMapping[$filterType]);
            }
        }

        $documents = $query->orderBy('created_at', 'desc')->get();

        // Get all documents for counts (unfiltered)
        $allDocuments = LegalDocument::where('tenant_id', $user->tenant_id)->get();

        // Group documents by type
        $documentsByType = $allDocuments->groupBy('document_type');

        // Get counts from all documents
        $counts = [
            'total' => $allDocuments->count(),
            'active' => $allDocuments->where('status', LegalDocument::STATUS_ACTIVE)->count(),
            'wills' => $allDocuments->where('document_type', LegalDocument::TYPE_WILL)->count(),
            'trusts' => $allDocuments->where('document_type', LegalDocument::TYPE_TRUST)->count(),
            'poa' => $allDocuments->whereIn('document_type', [LegalDocument::TYPE_POWER_OF_ATTORNEY, 'financial_poa', 'healthcare_poa'])->count(),
            'medical' => $allDocuments->whereIn('document_type', [LegalDocument::TYPE_MEDICAL_DIRECTIVE, 'living_will', 'healthcare_proxy', 'dnr'])->count(),
            'other' => $allDocuments->where('document_type', LegalDocument::TYPE_OTHER)->count(),
        ];

        return view('pages.legal.index', [
            'documents' => $documents,
            'allDocuments' => $allDocuments,
            'documentsByType' => $documentsByType,
            'counts' => $counts,
            'documentTypes' => LegalDocument::DOCUMENT_TYPES,
            'statuses' => LegalDocument::STATUSES,
            'filterType' => $filterType,
        ]);
    }

    /**
     * Show the form for creating a new legal document.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get all contacts from People directory
        $attorneys = Person::where('tenant_id', $user->tenant_id)
            ->orderBy('full_name')
            ->get();

        // Get all family circles
        $familyCircles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        // Pre-select document type if provided
        $selectedType = $request->get('type');
        $selectedFamilyCircleId = $request->get('family_circle_id');

        return view('pages.legal.form', [
            'document' => null,
            'attorneys' => $attorneys,
            'familyCircles' => $familyCircles,
            'documentTypes' => LegalDocument::DOCUMENT_TYPES,
            'statuses' => LegalDocument::STATUSES,
            'selectedType' => $selectedType,
            'selectedFamilyCircleId' => $selectedFamilyCircleId,
        ]);
    }

    /**
     * Store a newly created legal document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(LegalDocument::DOCUMENT_TYPES)),
            'custom_document_type' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'original_location' => 'nullable|string|max:500',
            'attorney_person_id' => 'nullable|exists:people,id',
            'attorney_name' => 'nullable|string|max:255',
            'attorney_phone' => 'nullable|string|max:50',
            'attorney_email' => 'nullable|email|max:255',
            'attorney_firm' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:' . implode(',', array_keys(LegalDocument::STATUSES)),
            'family_circle_id' => 'nullable|exists:family_circles,id',
            'execution_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'digital_copy_date' => 'nullable|date',
        ]);

        $user = Auth::user();

        $data = $validated;

        // Parse dates from MM/DD/YYYY format
        foreach (['execution_date', 'expiration_date', 'digital_copy_date'] as $dateField) {
            if (!empty($data[$dateField])) {
                try {
                    $data[$dateField] = Carbon::createFromFormat('m/d/Y', $data[$dateField])->format('Y-m-d');
                } catch (\Exception $e) {
                    $data[$dateField] = null;
                }
            }
        }

        $data['tenant_id'] = $user->tenant_id;
        $data['created_by'] = $user->id;
        $data['status'] = $data['status'] ?? LegalDocument::STATUS_ACTIVE;

        // Clear attorney person if manually entered
        if (!empty($data['attorney_name']) && empty($data['attorney_person_id'])) {
            $data['attorney_person_id'] = null;
        }

        $document = LegalDocument::create($data);

        // Handle file uploads
        if ($request->hasFile('files')) {
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/heic',
                'image/heif',
            ];
            $maxSize = 20 * 1024 * 1024; // 20MB

            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    // Validate file type and size
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        continue; // Skip invalid file types
                    }
                    if ($file->getSize() > $maxSize) {
                        continue; // Skip files that are too large
                    }

                    $path = $file->store('documents/legal/' . $document->id, 'private');

                    LegalDocumentFile::create([
                        'legal_document_id' => $document->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'folder' => null,
                    ]);
                }
            }
        }

        return redirect()->route('legal.show', $document)
            ->with('success', 'Legal document created successfully.');
    }

    /**
     * Display the specified legal document.
     */
    public function show(LegalDocument $legalDocument)
    {
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $legalDocument->load(['files', 'attorney', 'creator']);

        return view('pages.legal.show', [
            'document' => $legalDocument,
            'documentTypes' => LegalDocument::DOCUMENT_TYPES,
            'statuses' => LegalDocument::STATUSES,
        ]);
    }

    /**
     * Show the form for editing the specified legal document.
     */
    public function edit(LegalDocument $legalDocument)
    {
        $user = Auth::user();

        if ($legalDocument->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $attorneys = Person::where('tenant_id', $user->tenant_id)
            ->orderBy('full_name')
            ->get();

        $familyCircles = FamilyCircle::where('tenant_id', $user->tenant_id)
            ->orderBy('name')
            ->get();

        $legalDocument->load('files');

        return view('pages.legal.form', [
            'document' => $legalDocument,
            'attorneys' => $attorneys,
            'familyCircles' => $familyCircles,
            'documentTypes' => LegalDocument::DOCUMENT_TYPES,
            'statuses' => LegalDocument::STATUSES,
            'selectedType' => null,
        ]);
    }

    /**
     * Update the specified legal document.
     */
    public function update(Request $request, LegalDocument $legalDocument)
    {
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(LegalDocument::DOCUMENT_TYPES)),
            'custom_document_type' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'original_location' => 'nullable|string|max:500',
            'attorney_person_id' => 'nullable|exists:people,id',
            'attorney_name' => 'nullable|string|max:255',
            'attorney_phone' => 'nullable|string|max:50',
            'attorney_email' => 'nullable|email|max:255',
            'attorney_firm' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:' . implode(',', array_keys(LegalDocument::STATUSES)),
            'family_circle_id' => 'nullable|exists:family_circles,id',
            'execution_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'digital_copy_date' => 'nullable|date',
        ]);

        $data = $validated;

        // Parse dates from MM/DD/YYYY format
        foreach (['execution_date', 'expiration_date', 'digital_copy_date'] as $dateField) {
            if (!empty($data[$dateField])) {
                try {
                    $data[$dateField] = Carbon::createFromFormat('m/d/Y', $data[$dateField])->format('Y-m-d');
                } catch (\Exception $e) {
                    $data[$dateField] = null;
                }
            }
        }

        // Clear attorney person if manually entered
        if (!empty($data['attorney_name']) && empty($data['attorney_person_id'])) {
            $data['attorney_person_id'] = null;
        }

        $legalDocument->update($data);

        // Handle new file uploads
        if ($request->hasFile('files')) {
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/heic',
                'image/heif',
            ];
            $maxSize = 20 * 1024 * 1024; // 20MB

            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    // Validate file type and size
                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        continue; // Skip invalid file types
                    }
                    if ($file->getSize() > $maxSize) {
                        continue; // Skip files that are too large
                    }

                    $path = $file->store('documents/legal/' . $legalDocument->id, 'private');

                    LegalDocumentFile::create([
                        'legal_document_id' => $legalDocument->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'folder' => null,
                    ]);
                }
            }
        }

        return redirect()->route('legal.show', $legalDocument)
            ->with('success', 'Legal document updated successfully.');
    }

    /**
     * Remove the specified legal document.
     */
    public function destroy(LegalDocument $legalDocument)
    {
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete all files from storage
        foreach ($legalDocument->files as $file) {
            Storage::disk('private')->delete($file->file_path);
        }

        $legalDocument->delete();

        return redirect()->route('legal.index')
            ->with('success', 'Legal document deleted successfully.');
    }

    /**
     * Download a file.
     */
    public function downloadFile(LegalDocument $legalDocument, LegalDocumentFile $file)
    {
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($file->legal_document_id !== $legalDocument->id) {
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
    public function viewFile(LegalDocument $legalDocument, LegalDocumentFile $file)
    {
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($file->legal_document_id !== $legalDocument->id) {
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
    public function destroyFile(LegalDocument $legalDocument, LegalDocumentFile $file)
    {
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        if ($file->legal_document_id !== $legalDocument->id) {
            abort(404);
        }

        Storage::disk('private')->delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
