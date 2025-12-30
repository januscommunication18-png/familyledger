<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\MemberDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MemberDocumentController extends Controller
{
    /**
     * Display the document vault for a family member.
     */
    public function index(FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
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
        if ($member->tenant_id !== Auth::user()->tenant_id) {
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
            'tenant_id' => Auth::user()->tenant_id,
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
                'private'
            );
            $data['front_image'] = $path;
        }

        if ($request->hasFile('back_image')) {
            $path = $request->file('back_image')->store(
                "documents/{$member->id}/back",
                'private'
            );
            $data['back_image'] = $path;
        }

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
        if ($document->tenant_id !== Auth::user()->tenant_id) {
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
        if ($document->tenant_id !== Auth::user()->tenant_id) {
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
        if ($request->hasFile('front_image')) {
            if ($document->front_image) {
                Storage::disk('private')->delete($document->front_image);
            }
            $path = $request->file('front_image')->store(
                "documents/{$member->id}/front",
                'private'
            );
            $data['front_image'] = $path;
        }

        if ($request->hasFile('back_image')) {
            if ($document->back_image) {
                Storage::disk('private')->delete($document->back_image);
            }
            $path = $request->file('back_image')->store(
                "documents/{$member->id}/back",
                'private'
            );
            $data['back_image'] = $path;
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
        if ($document->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete uploaded files
        if ($document->front_image) {
            Storage::disk('private')->delete($document->front_image);
        }
        if ($document->back_image) {
            Storage::disk('private')->delete($document->back_image);
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
        if ($document->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $path = $type === 'front' ? $document->front_image : $document->back_image;

        if (!$path || !Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->response($path);
    }

    /**
     * Show the driver's license form page.
     */
    public function driversLicense(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_DRIVERS_LICENSE)
            ->first();

        return view('family-circle.member.documents.drivers-license', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
        ]);
    }

    /**
     * Show the passport form page.
     */
    public function passport(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_PASSPORT)
            ->first();

        return view('family-circle.member.documents.passport', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
        ]);
    }

    /**
     * Show the social security form page.
     */
    public function socialSecurity(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_SOCIAL_SECURITY)
            ->first();

        return view('family-circle.member.documents.social-security', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
        ]);
    }

    /**
     * Show the birth certificate form page.
     */
    public function birthCertificate(\App\Models\FamilyCircle $familyCircle, FamilyMember $member)
    {
        if ($member->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $document = $member->documents()
            ->where('document_type', MemberDocument::TYPE_BIRTH_CERTIFICATE)
            ->first();

        return view('family-circle.member.documents.birth-certificate', [
            'circle' => $familyCircle,
            'member' => $member,
            'document' => $document,
        ]);
    }
}
