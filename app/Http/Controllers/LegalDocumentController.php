<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $user = Auth::user();

        $documents = LegalDocument::where('tenant_id', $user->tenant_id)
            ->with(['files', 'attorney', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group documents by type
        $documentsByType = $documents->groupBy('document_type');

        // Get counts
        $counts = [
            'total' => $documents->count(),
            'active' => $documents->where('status', LegalDocument::STATUS_ACTIVE)->count(),
            'wills' => $documents->where('document_type', LegalDocument::TYPE_WILL)->count(),
            'trusts' => $documents->where('document_type', LegalDocument::TYPE_TRUST)->count(),
            'poa' => $documents->where('document_type', LegalDocument::TYPE_POWER_OF_ATTORNEY)->count(),
            'medical' => $documents->where('document_type', LegalDocument::TYPE_MEDICAL_DIRECTIVE)->count(),
            'other' => $documents->where('document_type', LegalDocument::TYPE_OTHER)->count(),
        ];

        return view('pages.legal.index', [
            'documents' => $documents,
            'documentsByType' => $documentsByType,
            'counts' => $counts,
            'documentTypes' => LegalDocument::DOCUMENT_TYPES,
            'statuses' => LegalDocument::STATUSES,
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

        // Pre-select document type if provided
        $selectedType = $request->get('type');

        return view('pages.legal.form', [
            'document' => null,
            'attorneys' => $attorneys,
            'documentTypes' => LegalDocument::DOCUMENT_TYPES,
            'statuses' => LegalDocument::STATUSES,
            'selectedType' => $selectedType,
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
            // Date component fields
            'execution_date_month' => 'nullable|string',
            'execution_date_day' => 'nullable|string',
            'execution_date_year' => 'nullable|string',
            'expiration_date_month' => 'nullable|string',
            'expiration_date_day' => 'nullable|string',
            'expiration_date_year' => 'nullable|string',
            'digital_copy_date_month' => 'nullable|string',
            'digital_copy_date_day' => 'nullable|string',
            'digital_copy_date_year' => 'nullable|string',
        ]);

        $user = Auth::user();

        $data = collect($validated)->except([
            'execution_date_month', 'execution_date_day', 'execution_date_year',
            'expiration_date_month', 'expiration_date_day', 'expiration_date_year',
            'digital_copy_date_month', 'digital_copy_date_day', 'digital_copy_date_year',
        ])->toArray();

        // Parse dates from separate fields
        $data['execution_date'] = $this->parseDate($request, 'execution_date');
        $data['expiration_date'] = $this->parseDate($request, 'expiration_date');
        $data['digital_copy_date'] = $this->parseDate($request, 'digital_copy_date');

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
        if ($legalDocument->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $user = Auth::user();

        $attorneys = Person::where('tenant_id', $user->tenant_id)
            ->orderBy('full_name')
            ->get();

        $legalDocument->load('files');

        return view('pages.legal.form', [
            'document' => $legalDocument,
            'attorneys' => $attorneys,
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
            // Date component fields
            'execution_date_month' => 'nullable|string',
            'execution_date_day' => 'nullable|string',
            'execution_date_year' => 'nullable|string',
            'expiration_date_month' => 'nullable|string',
            'expiration_date_day' => 'nullable|string',
            'expiration_date_year' => 'nullable|string',
            'digital_copy_date_month' => 'nullable|string',
            'digital_copy_date_day' => 'nullable|string',
            'digital_copy_date_year' => 'nullable|string',
        ]);

        $data = collect($validated)->except([
            'execution_date_month', 'execution_date_day', 'execution_date_year',
            'expiration_date_month', 'expiration_date_day', 'expiration_date_year',
            'digital_copy_date_month', 'digital_copy_date_day', 'digital_copy_date_year',
        ])->toArray();

        // Parse dates from separate fields
        $data['execution_date'] = $this->parseDate($request, 'execution_date');
        $data['expiration_date'] = $this->parseDate($request, 'expiration_date');
        $data['digital_copy_date'] = $this->parseDate($request, 'digital_copy_date');

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
