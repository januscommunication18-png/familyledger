<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\MemberDocument;
use App\Services\CollaboratorPermissionService;
use App\Services\CoparentEditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MemberDocumentController extends Controller
{
    /**
     * Map document types to permission categories.
     */
    protected function getPermissionCategory(string $documentType): string
    {
        return match ($documentType) {
            MemberDocument::TYPE_DRIVERS_LICENSE => 'drivers_license',
            MemberDocument::TYPE_PASSPORT => 'passport',
            MemberDocument::TYPE_SOCIAL_SECURITY => 'ssn',
            MemberDocument::TYPE_BIRTH_CERTIFICATE => 'birth_certificate',
            default => 'documents',
        };
    }

    /**
     * Display the document vault for a family member.
     */
    public function index(FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->hasAccess()) {
            abort(403);
        }

        // For linked members (Self), load documents from all linked member records
        if ($member->linked_user_id) {
            $linkedMemberIds = FamilyMember::where('linked_user_id', $member->linked_user_id)
                ->pluck('id')
                ->toArray();

            $documents = MemberDocument::whereIn('family_member_id', $linkedMemberIds)
                ->orderBy('document_type')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $documents = $member->documents()
                ->orderBy('document_type')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('family-circle.member.documents.index', [
            'member' => $member,
            'documents' => $documents,
            'documentTypes' => MemberDocument::DOCUMENT_TYPES,
        ]);
    }

    /**
     * Store a new document.
     */
    public function store(Request $request, FamilyMember $member)
    {
        // Use centralized permission service - check create permission for this document type
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);
        $category = $this->getPermissionCategory($request->input('document_type', ''));

        // Allow if can create OR is coparent needing approval
        if (!$permissionService->canCreate($category) && !$editService->needsApproval()) {
            abort(403);
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:' . implode(',', array_keys(MemberDocument::DOCUMENT_TYPES)),
            'document_number' => 'nullable|string|max:100',
            'state_of_issue' => 'nullable|string|max:100',
            'country_of_issue' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'details' => 'nullable|string',
            'front_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'back_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'ssn_number' => 'nullable|string|max:20', // For SSN only
        ]);

        $data = [
            'tenant_id' => $member->tenant_id,
            'family_member_id' => $member->id,
            'uploaded_by' => Auth::id(),
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'] ?? null,
            'state_of_issue' => $validated['state_of_issue'] ?? null,
            'country_of_issue' => $validated['country_of_issue'] ?? null,
            'issue_date' => $validated['issue_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'details' => $validated['details'] ?? null,
        ];

        // Handle SSN encryption
        if ($validated['document_type'] === 'social_security' && !empty($validated['ssn_number'])) {
            $data['encrypted_number'] = $validated['ssn_number'];
        }

        // Handle file uploads
        if ($request->hasFile('front_image')) {
            $path = $request->file('front_image')->store(
                "documents/{$member->id}/front",
                'do_spaces'
            );
            $data['front_image'] = $path;
        }

        if ($request->hasFile('back_image')) {
            $path = $request->file('back_image')->store(
                "documents/{$member->id}/back",
                'do_spaces'
            );
            $data['back_image'] = $path;
        }

        // If coparent, create pending edit for new document
        if ($editService->needsApproval()) {
            $result = $editService->handleCreate(MemberDocument::class, $data);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => 'Document creation submitted for owner approval',
                ], 202);
            }

            return redirect()->route('family-circle.member.show', [
                $member->familyCircle,
                $member
            ])->with('info', 'Document creation submitted for owner approval.');
        }

        // Owner can create directly
        $document = MemberDocument::create($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Document added successfully',
                'document' => $document,
            ]);
        }

        return redirect()->route('family-circle.member.show', [
            $member->familyCircle,
            $member
        ])->with('success', 'Document added successfully');
    }

    /**
     * Display the specified document.
     */
    public function show(FamilyMember $member, MemberDocument $document)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $category = $this->getPermissionCategory($document->document_type);

        if (!$permissionService->canView($category)) {
            abort(403);
        }

        return view('family-circle.member.documents.show', [
            'member' => $member,
            'document' => $document,
        ]);
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, FamilyMember $member, MemberDocument $document)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);
        $category = $this->getPermissionCategory($document->document_type);

        // Allow if can edit OR is coparent needing approval
        if (!$permissionService->canEdit($category) && !$editService->needsApproval()) {
            abort(403);
        }

        $validated = $request->validate([
            'document_number' => 'nullable|string|max:100',
            'state_of_issue' => 'nullable|string|max:100',
            'country_of_issue' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'details' => 'nullable|string',
            'front_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'back_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'ssn_number' => 'nullable|string|max:20',
        ]);

        $data = [
            'document_number' => $validated['document_number'] ?? null,
            'state_of_issue' => $validated['state_of_issue'] ?? null,
            'country_of_issue' => $validated['country_of_issue'] ?? null,
            'issue_date' => $validated['issue_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'details' => $validated['details'] ?? null,
        ];

        // Handle SSN encryption
        if ($document->document_type === 'social_security' && !empty($validated['ssn_number'])) {
            $data['encrypted_number'] = $validated['ssn_number'];
        }

        // Handle file uploads
        $newFrontImage = null;
        $newBackImage = null;
        if ($request->hasFile('front_image')) {
            $path = $request->file('front_image')->store(
                "documents/{$member->id}/front",
                'do_spaces'
            );
            $newFrontImage = $path;
            $data['front_image'] = $path;
        }

        if ($request->hasFile('back_image')) {
            $path = $request->file('back_image')->store(
                "documents/{$member->id}/back",
                'do_spaces'
            );
            $newBackImage = $path;
            $data['back_image'] = $path;
        }

        // If coparent, create pending edits for each changed field
        if ($editService->needsApproval()) {
            $pendingCount = 0;
            $fieldsToCheck = ['document_number', 'state_of_issue', 'country_of_issue', 'issue_date', 'expiry_date', 'details'];

            foreach ($fieldsToCheck as $field) {
                $oldValue = $document->$field;
                $newValue = $data[$field] ?? null;

                $oldNormalized = is_null($oldValue) ? '' : (string) $oldValue;
                $newNormalized = is_null($newValue) ? '' : (string) $newValue;

                if ($oldNormalized !== $newNormalized) {
                    $editService->handleUpdate($document, $field, $newValue);
                    $pendingCount++;
                }
            }

            // Handle SSN separately
            if ($document->document_type === 'social_security' && !empty($validated['ssn_number'])) {
                $editService->handleUpdate($document, 'encrypted_number', $validated['ssn_number']);
                $pendingCount++;
            }

            // Handle images
            if ($newFrontImage) {
                $editService->handleUpdate($document, 'front_image', $newFrontImage);
                $pendingCount++;
            }
            if ($newBackImage) {
                $editService->handleUpdate($document, 'back_image', $newBackImage);
                $pendingCount++;
            }

            if ($pendingCount === 0) {
                return redirect()->route('family-circle.member.show', [
                    $member->familyCircle,
                    $member
                ])->with('info', 'No changes detected.');
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => "{$pendingCount} edit(s) submitted for owner approval",
                ], 202);
            }

            return redirect()->route('family-circle.member.show', [
                $member->familyCircle,
                $member
            ])->with('info', "{$pendingCount} edit(s) submitted for owner approval.");
        }

        // Owner can update directly - delete old files if new ones uploaded
        if ($newFrontImage && $document->front_image) {
            Storage::disk('do_spaces')->delete($document->front_image);
        }
        if ($newBackImage && $document->back_image) {
            Storage::disk('do_spaces')->delete($document->back_image);
        }

        $document->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Document updated successfully',
                'document' => $document,
            ]);
        }

        return redirect()->route('family-circle.member.show', [
            $member->familyCircle,
            $member
        ])->with('success', 'Document updated successfully');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(FamilyMember $member, MemberDocument $document)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $editService = CoparentEditService::forMember($member);
        $category = $this->getPermissionCategory($document->document_type);

        // Allow if can delete OR is coparent needing approval
        if (!$permissionService->canDelete($category) && !$editService->needsApproval()) {
            abort(403);
        }

        // If coparent, create pending delete
        if ($editService->needsApproval()) {
            $editService->handleDelete($document);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'pending' => true,
                    'message' => 'Delete request submitted for owner approval',
                ], 202);
            }
            return redirect()->route('family-circle.member.show', [
                $member->familyCircle,
                $member
            ])->with('info', 'Delete request submitted for owner approval.');
        }

        // Delete uploaded files
        if ($document->front_image) {
            Storage::disk('do_spaces')->delete($document->front_image);
        }
        if ($document->back_image) {
            Storage::disk('do_spaces')->delete($document->back_image);
        }

        $document->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Document deleted successfully',
            ]);
        }

        return redirect()->route('family-circle.member.show', [
            $member->familyCircle,
            $member
        ])->with('success', 'Document deleted successfully');
    }

    /**
     * Serve a document image securely.
     */
    public function image(FamilyMember $member, MemberDocument $document, string $type)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);
        $category = $this->getPermissionCategory($document->document_type);

        if (!$permissionService->canView($category)) {
            abort(403);
        }

        $path = $type === 'front' ? $document->front_image : $document->back_image;

        if (!$path || !Storage::disk('do_spaces')->exists($path)) {
            abort(404);
        }

        return Storage::disk('do_spaces')->response($path);
    }

    /**
     * Show the driver's license form page.
     */
    public function driversLicense(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('drivers_license')) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_DRIVERS_LICENSE)
            ->first();

        return view('family-circle.member.documents.drivers-license', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Show the passport form page.
     */
    public function passport(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('passport')) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_PASSPORT)
            ->first();

        return view('family-circle.member.documents.passport', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Show the social security form page.
     */
    public function socialSecurity(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('ssn')) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_SOCIAL_SECURITY)
            ->first();

        return view('family-circle.member.documents.social-security', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Show the birth certificate form page.
     */
    public function birthCertificate(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('birth_certificate')) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_BIRTH_CERTIFICATE)
            ->first();

        return view('family-circle.member.documents.birth-certificate', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
            'access' => $permissionService->forView(),
        ]);
    }
}
