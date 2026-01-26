<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\FamilyMemberResource;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Models\MemberDocument;
use App\Models\MemberMedicalInfo;
use App\Models\MemberAllergy;
use App\Models\MemberMedication;
use App\Models\MemberContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API Controller for Family Members.
 */
class FamilyMemberController extends Controller
{
    /**
     * Get all members in a family circle.
     */
    public function index(Request $request, FamilyCircle $familyCircle): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        $members = $familyCircle->familyMembers()
            ->withCount('documents')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'members' => FamilyMemberResource::collection($members),
            'total' => $members->count(),
        ]);
    }

    /**
     * Get a specific family member with details.
     */
    public function show(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        // Ensure the member belongs to this family circle
        if ($member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found in this family circle');
        }

        // Load relationships
        $member->load([
            'medicalInfo',
            'contacts',
            'documents',
            'allergies',
            'medications',
            'medicalConditions',
            'vaccinations',
            'healthcareProviders',
            'schoolInfo',
            'schoolRecords.documents',
        ]);
        $member->loadCount('documents');

        return $this->success([
            'member' => new FamilyMemberResource($member),
        ]);
    }

    /**
     * Create a new family member.
     */
    public function store(Request $request, FamilyCircle $familyCircle): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'date_of_birth' => 'required|date|before:today',
            'relationship' => 'required|string|in:' . implode(',', array_keys(FamilyMember::RELATIONSHIPS)),
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'is_minor' => 'nullable|boolean',
            'co_parenting_enabled' => 'nullable|boolean',
            'immigration_status' => 'nullable|string|in:' . implode(',', array_keys(FamilyMember::IMMIGRATION_STATUSES)),
            'profile_image' => 'nullable|string', // Base64 encoded image
        ]);

        $data = [
            'family_circle_id' => $familyCircle->id,
            'created_by' => $user->id,
            'tenant_id' => $user->tenant_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone_country_code' => $validated['phone_country_code'] ?? '+1',
            'date_of_birth' => $validated['date_of_birth'],
            'relationship' => $validated['relationship'],
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'is_minor' => $request->boolean('is_minor'),
            'co_parenting_enabled' => $request->boolean('co_parenting_enabled'),
            'immigration_status' => $validated['immigration_status'] ?? null,
        ];

        // Handle base64 profile image
        if (!empty($validated['profile_image'])) {
            $imageData = $validated['profile_image'];

            // Remove data URL prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $extension = $matches[1];
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            } else {
                $extension = 'jpg';
            }

            $decodedImage = base64_decode($imageData);
            if ($decodedImage !== false) {
                $filename = 'profile_' . time() . '_' . uniqid() . '.' . $extension;
                $path = 'family-ledger/members/profiles/' . $filename;

                $disk = Storage::disk('do_spaces');
                $disk->put($path, $decodedImage);
                $disk->setVisibility($path, 'public');

                $data['profile_image'] = $path;
            }
        }

        $member = FamilyMember::create($data);

        return $this->success([
            'member' => new FamilyMemberResource($member),
        ], 'Family member added successfully', 201);
    }

    /**
     * Get relationships list for dropdown.
     */
    public function relationships(): JsonResponse
    {
        return $this->success([
            'relationships' => FamilyMember::RELATIONSHIPS,
        ]);
    }

    /**
     * Get immigration statuses list for dropdown.
     */
    public function immigrationStatuses(): JsonResponse
    {
        return $this->success([
            'immigration_statuses' => FamilyMember::IMMIGRATION_STATUSES,
        ]);
    }

    /**
     * Update a family member.
     */
    public function update(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        // Ensure the member belongs to this family circle
        if ($member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found in this family circle');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'date_of_birth' => 'required|date|before:today',
            'relationship' => 'required|string|in:' . implode(',', array_keys(FamilyMember::RELATIONSHIPS)),
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'is_minor' => 'nullable|boolean',
            'co_parenting_enabled' => 'nullable|boolean',
            'immigration_status' => 'nullable|string|in:' . implode(',', array_keys(FamilyMember::IMMIGRATION_STATUSES)),
            'profile_image' => 'nullable|string', // Base64 encoded image
        ]);

        $data = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'phone_country_code' => $validated['phone_country_code'] ?? '+1',
            'date_of_birth' => $validated['date_of_birth'],
            'relationship' => $validated['relationship'],
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'is_minor' => $request->boolean('is_minor'),
            'co_parenting_enabled' => $request->boolean('co_parenting_enabled'),
            'immigration_status' => $validated['immigration_status'] ?? null,
        ];

        // Handle base64 profile image
        if (!empty($validated['profile_image'])) {
            $imageData = $validated['profile_image'];

            // Remove data URL prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
                $extension = $matches[1];
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            } else {
                $extension = 'jpg';
            }

            $decodedImage = base64_decode($imageData);
            if ($decodedImage !== false) {
                // Delete old profile image if exists
                if ($member->profile_image) {
                    Storage::disk('do_spaces')->delete($member->profile_image);
                }

                $filename = 'profile_' . time() . '_' . uniqid() . '.' . $extension;
                $path = 'family-ledger/members/profiles/' . $filename;

                $disk = Storage::disk('do_spaces');
                $disk->put($path, $decodedImage);
                $disk->setVisibility($path, 'public');

                $data['profile_image'] = $path;
            }
        }

        $member->update($data);

        return $this->success([
            'member' => new FamilyMemberResource($member->fresh()),
        ], 'Family member updated successfully');
    }

    /**
     * Delete a family member.
     */
    public function destroy(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        // Ensure the family circle belongs to the user's tenant
        if ($familyCircle->tenant_id !== $user->tenant_id) {
            return $this->notFound('Family circle not found');
        }

        // Ensure the member belongs to this family circle
        if ($member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found in this family circle');
        }

        // Delete profile image if exists
        if ($member->profile_image) {
            Storage::disk('do_spaces')->delete($member->profile_image);
        }

        $member->delete();

        return $this->success(null, 'Family member deleted successfully');
    }

    /**
     * Get blood types list for dropdown.
     */
    public function bloodTypes(): JsonResponse
    {
        return $this->success([
            'blood_types' => MemberMedicalInfo::BLOOD_TYPES,
        ]);
    }

    /**
     * Store a new document for a member.
     */
    public function storeDocument(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:drivers_license,passport,social_security,birth_certificate',
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'issuing_country' => 'nullable|string|max:255',
            'issuing_state' => 'nullable|string|max:255',
            'front_image' => 'nullable|string',
            'back_image' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Handle base64 image uploads
        $frontImagePath = null;
        $backImagePath = null;

        if (!empty($validated['front_image'])) {
            $frontImagePath = $this->saveBase64Image($validated['front_image'], 'documents');
        }

        if (!empty($validated['back_image'])) {
            $backImagePath = $this->saveBase64Image($validated['back_image'], 'documents');
        }

        $document = MemberDocument::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'uploaded_by' => $user->id,
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'] ?? null,
            'issue_date' => $validated['issue_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'country_of_issue' => $validated['issuing_country'] ?? null,
            'state_of_issue' => $validated['issuing_state'] ?? null,
            'front_image' => $frontImagePath,
            'back_image' => $backImagePath,
        ]);

        return $this->success([
            'document' => $document,
        ], 'Document added successfully', 201);
    }

    /**
     * Update a member document.
     */
    public function updateDocument(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberDocument $document): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($document->family_member_id !== $member->id) {
            return $this->notFound('Document not found');
        }

        $validated = $request->validate([
            'document_number' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'issuing_country' => 'nullable|string|max:255',
            'issuing_state' => 'nullable|string|max:255',
            'front_image' => 'nullable|string',
            'back_image' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $updateData = [
            'document_number' => $validated['document_number'] ?? $document->document_number,
            'issue_date' => $validated['issue_date'] ?? $document->issue_date,
            'expiry_date' => $validated['expiry_date'] ?? $document->expiry_date,
            'country_of_issue' => $validated['issuing_country'] ?? $document->country_of_issue,
            'state_of_issue' => $validated['issuing_state'] ?? $document->state_of_issue,
        ];

        // Handle base64 image uploads
        if (!empty($validated['front_image'])) {
            $updateData['front_image'] = $this->saveBase64Image($validated['front_image'], 'documents');
        }

        if (!empty($validated['back_image'])) {
            $updateData['back_image'] = $this->saveBase64Image($validated['back_image'], 'documents');
        }

        $document->update($updateData);

        return $this->success([
            'document' => $document->fresh(),
        ], 'Document updated successfully');
    }

    /**
     * Delete a member document.
     */
    public function deleteDocument(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberDocument $document): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($document->family_member_id !== $member->id) {
            return $this->notFound('Document not found');
        }

        $document->delete();

        return $this->success(null, 'Document deleted successfully');
    }

    /**
     * Update member medical info (blood type, insurance, etc).
     */
    public function updateMedicalInfo(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'blood_type' => 'nullable|string|in:' . implode(',', array_keys(MemberMedicalInfo::BLOOD_TYPES)),
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'primary_physician' => 'nullable|string|max:255',
            'primary_physician_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $medicalInfo = MemberMedicalInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            array_merge($validated, ['tenant_id' => $user->tenant_id])
        );

        return $this->success([
            'medical_info' => $medicalInfo,
        ], 'Medical info updated successfully');
    }

    /**
     * Store a new allergy for a member.
     */
    public function storeAllergy(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'allergen_name' => 'required|string|max:255',
            'allergy_type' => 'nullable|string|in:food,medication,environmental,latex,insect,other',
            'allergen_type' => 'nullable|string|in:food,medication,environmental,latex,insect,other',
            'severity' => 'nullable|string|in:mild,moderate,severe,life_threatening',
            'reaction' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Accept either allergy_type or allergen_type from iOS
        $allergyType = $validated['allergy_type'] ?? $validated['allergen_type'] ?? 'other';

        $allergy = MemberAllergy::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'allergen_name' => $validated['allergen_name'],
            'allergy_type' => $allergyType,
            'severity' => $validated['severity'] ?? 'moderate',
            'notes' => $validated['reaction'] ?? $validated['notes'] ?? null,
        ]);

        return $this->success([
            'allergy' => $allergy,
        ], 'Allergy added successfully', 201);
    }

    /**
     * Update an allergy.
     */
    public function updateAllergy(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberAllergy $allergy): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($allergy->family_member_id !== $member->id) {
            return $this->notFound('Allergy not found');
        }

        $validated = $request->validate([
            'allergen_name' => 'sometimes|required|string|max:255',
            'allergy_type' => 'nullable|string|in:food,medication,environmental,latex,insect,other',
            'allergen_type' => 'nullable|string|in:food,medication,environmental,latex,insect,other',
            'severity' => 'nullable|string|in:mild,moderate,severe,life_threatening',
            'reaction' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $updateData = [];
        if (isset($validated['allergen_name'])) {
            $updateData['allergen_name'] = $validated['allergen_name'];
        }
        if (isset($validated['allergy_type']) || isset($validated['allergen_type'])) {
            $updateData['allergy_type'] = $validated['allergy_type'] ?? $validated['allergen_type'];
        }
        if (isset($validated['severity'])) {
            $updateData['severity'] = $validated['severity'];
        }
        if (isset($validated['reaction'])) {
            $updateData['notes'] = $validated['reaction'];
        }
        if (isset($validated['notes'])) {
            $updateData['notes'] = $validated['notes'];
        }

        $allergy->update($updateData);

        return $this->success([
            'allergy' => $allergy->fresh(),
        ], 'Allergy updated successfully');
    }

    /**
     * Delete an allergy.
     */
    public function deleteAllergy(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberAllergy $allergy): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($allergy->family_member_id !== $member->id) {
            return $this->notFound('Allergy not found');
        }

        $allergy->delete();

        return $this->success(null, 'Allergy deleted successfully');
    }

    /**
     * Store a new medication for a member.
     */
    public function storeMedication(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'frequency' => 'nullable|string|max:100',
            'prescribing_doctor' => 'nullable|string|max:255',
            'pharmacy' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $medication = MemberMedication::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'dosage' => $validated['dosage'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'prescribing_doctor' => $validated['prescribing_doctor'] ?? null,
            'pharmacy' => $validated['pharmacy'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        return $this->success([
            'medication' => $medication,
        ], 'Medication added successfully', 201);
    }

    /**
     * Update a medication.
     */
    public function updateMedication(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberMedication $medication): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($medication->family_member_id !== $member->id) {
            return $this->notFound('Medication not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:100',
            'frequency' => 'nullable|string|max:100',
            'prescribing_doctor' => 'nullable|string|max:255',
            'pharmacy' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $medication->update($validated);

        return $this->success([
            'medication' => $medication->fresh(),
        ], 'Medication updated successfully');
    }

    /**
     * Delete a medication.
     */
    public function deleteMedication(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberMedication $medication): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($medication->family_member_id !== $member->id) {
            return $this->notFound('Medication not found');
        }

        $medication->delete();

        return $this->success(null, 'Medication deleted successfully');
    }

    /**
     * Store a new emergency contact for a member.
     */
    public function storeEmergencyContact(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'relationship' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'priority' => 'nullable|integer|min:1|max:10',
            'is_primary' => 'nullable|boolean',
        ]);

        $contact = MemberContact::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'relationship' => $validated['relationship'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'priority' => $validated['priority'] ?? null,
            'is_emergency_contact' => true,
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return $this->success([
            'contact' => $contact,
        ], 'Emergency contact added successfully', 201);
    }

    /**
     * Update an emergency contact.
     */
    public function updateEmergencyContact(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberContact $contact): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($contact->family_member_id !== $member->id) {
            return $this->notFound('Contact not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'relationship' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'priority' => 'nullable|integer|min:1|max:10',
            'is_primary' => 'nullable|boolean',
        ]);

        $contact->update($validated);

        return $this->success([
            'contact' => $contact->fresh(),
        ], 'Emergency contact updated successfully');
    }

    /**
     * Delete an emergency contact.
     */
    public function deleteEmergencyContact(Request $request, FamilyCircle $familyCircle, FamilyMember $member, MemberContact $contact): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($contact->family_member_id !== $member->id) {
            return $this->notFound('Contact not found');
        }

        $contact->delete();

        return $this->success(null, 'Emergency contact deleted successfully');
    }

    // ==================== MEDICAL CONDITIONS ====================

    /**
     * Store a new medical condition.
     */
    public function storeCondition(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|in:active,managed,resolved,monitoring',
            'diagnosed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $condition = \App\Models\MemberMedicalCondition::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'name' => $validated['name'],
            'status' => $validated['status'] ?? 'active',
            'diagnosed_date' => $validated['diagnosed_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->success([
            'condition' => $condition,
        ], 'Medical condition added successfully');
    }

    /**
     * Update a medical condition.
     */
    public function updateCondition(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberMedicalCondition $condition): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($condition->family_member_id !== $member->id) {
            return $this->notFound('Condition not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'status' => 'nullable|string|in:active,managed,resolved,monitoring',
            'diagnosed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $condition->update($validated);

        return $this->success([
            'condition' => $condition->fresh(),
        ], 'Medical condition updated successfully');
    }

    /**
     * Delete a medical condition.
     */
    public function deleteCondition(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberMedicalCondition $condition): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($condition->family_member_id !== $member->id) {
            return $this->notFound('Condition not found');
        }

        $condition->delete();

        return $this->success(null, 'Medical condition deleted successfully');
    }

    // ==================== HEALTHCARE PROVIDERS ====================

    /**
     * Store a new healthcare provider.
     */
    public function storeProvider(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'provider_type' => 'required|string|in:primary_care,specialist,dentist,optometrist,therapist,other',
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'clinic_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $provider = \App\Models\MemberHealthcareProvider::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'provider_type' => $validated['provider_type'],
            'name' => $validated['name'],
            'specialty' => $validated['specialty'] ?? null,
            'clinic_name' => $validated['clinic_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return $this->success([
            'provider' => $provider,
        ], 'Healthcare provider added successfully');
    }

    /**
     * Update a healthcare provider.
     */
    public function updateProvider(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberHealthcareProvider $provider): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($provider->family_member_id !== $member->id) {
            return $this->notFound('Provider not found');
        }

        $validated = $request->validate([
            'provider_type' => 'sometimes|required|string|in:primary_care,specialist,dentist,optometrist,therapist,other',
            'name' => 'sometimes|required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'clinic_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $provider->update($validated);

        return $this->success([
            'provider' => $provider->fresh(),
        ], 'Healthcare provider updated successfully');
    }

    /**
     * Delete a healthcare provider.
     */
    public function deleteProvider(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberHealthcareProvider $provider): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($provider->family_member_id !== $member->id) {
            return $this->notFound('Provider not found');
        }

        $provider->delete();

        return $this->success(null, 'Healthcare provider deleted successfully');
    }

    // ==================== VACCINATIONS ====================

    /**
     * Store a new vaccination record.
     */
    public function storeVaccination(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'vaccine_type' => 'required|string|max:100',
            'custom_vaccine_name' => 'nullable|string|max:255',
            'vaccination_date' => 'nullable|date',
            'next_vaccination_date' => 'nullable|date',
            'administered_by' => 'nullable|string|max:255',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $vaccination = \App\Models\MemberVaccination::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'vaccine_type' => $validated['vaccine_type'],
            'custom_vaccine_name' => $validated['custom_vaccine_name'] ?? null,
            'vaccination_date' => $validated['vaccination_date'] ?? null,
            'next_vaccination_date' => $validated['next_vaccination_date'] ?? null,
            'administered_by' => $validated['administered_by'] ?? null,
            'lot_number' => $validated['lot_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->success([
            'vaccination' => $vaccination,
        ], 'Vaccination record added successfully');
    }

    /**
     * Update a vaccination record.
     */
    public function updateVaccination(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberVaccination $vaccination): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($vaccination->family_member_id !== $member->id) {
            return $this->notFound('Vaccination not found');
        }

        $validated = $request->validate([
            'vaccine_type' => 'sometimes|required|string|max:100',
            'custom_vaccine_name' => 'nullable|string|max:255',
            'vaccination_date' => 'nullable|date',
            'next_vaccination_date' => 'nullable|date',
            'administered_by' => 'nullable|string|max:255',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $vaccination->update($validated);

        return $this->success([
            'vaccination' => $vaccination->fresh(),
        ], 'Vaccination record updated successfully');
    }

    /**
     * Delete a vaccination record.
     */
    public function deleteVaccination(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberVaccination $vaccination): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($vaccination->family_member_id !== $member->id) {
            return $this->notFound('Vaccination not found');
        }

        $vaccination->delete();

        return $this->success(null, 'Vaccination record deleted successfully');
    }

    // ==================== SCHOOL RECORDS ====================

    /**
     * Store a new school record.
     */
    public function storeSchoolRecord(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'school_name' => 'required|string|max:255',
            'school_year' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'student_id' => 'nullable|string|max:50',
            'is_current' => 'nullable|boolean',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'counselor_name' => 'nullable|string|max:255',
            'counselor_email' => 'nullable|email|max:255',
            'bus_number' => 'nullable|string|max:20',
            'bus_pickup_time' => 'nullable|string|max:10',
            'bus_dropoff_time' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $schoolRecord = \App\Models\MemberSchoolInfo::create([
            'family_member_id' => $member->id,
            'tenant_id' => $user->tenant_id,
            'school_name' => $validated['school_name'],
            'school_year' => $validated['school_year'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'student_id' => $validated['student_id'] ?? null,
            'is_current' => $validated['is_current'] ?? true,
            'school_address' => $validated['school_address'] ?? null,
            'school_phone' => $validated['school_phone'] ?? null,
            'school_email' => $validated['school_email'] ?? null,
            'teacher_name' => $validated['teacher_name'] ?? null,
            'teacher_email' => $validated['teacher_email'] ?? null,
            'counselor_name' => $validated['counselor_name'] ?? null,
            'counselor_email' => $validated['counselor_email'] ?? null,
            'bus_number' => $validated['bus_number'] ?? null,
            'bus_pickup_time' => $validated['bus_pickup_time'] ?? null,
            'bus_dropoff_time' => $validated['bus_dropoff_time'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->success([
            'school_record' => [
                'id' => $schoolRecord->id,
                'school_name' => $schoolRecord->school_name,
                'grade_level' => $schoolRecord->grade_level,
                'grade_level_name' => $schoolRecord->grade_level_name,
                'school_year' => $schoolRecord->school_year,
                'is_current' => (bool) $schoolRecord->is_current,
            ],
        ], 'School record added successfully', 201);
    }

    /**
     * Update a school record.
     */
    public function updateSchoolRecord(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberSchoolInfo $schoolRecord): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($schoolRecord->family_member_id !== $member->id) {
            return $this->notFound('School record not found');
        }

        $validated = $request->validate([
            'school_name' => 'sometimes|required|string|max:255',
            'school_year' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'student_id' => 'nullable|string|max:50',
            'is_current' => 'nullable|boolean',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'teacher_name' => 'nullable|string|max:255',
            'teacher_email' => 'nullable|email|max:255',
            'counselor_name' => 'nullable|string|max:255',
            'counselor_email' => 'nullable|email|max:255',
            'bus_number' => 'nullable|string|max:20',
            'bus_pickup_time' => 'nullable|string|max:10',
            'bus_dropoff_time' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $schoolRecord->update($validated);

        return $this->success([
            'school_record' => $schoolRecord->fresh(),
        ], 'School record updated successfully');
    }

    /**
     * Delete a school record.
     */
    public function deleteSchoolRecord(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberSchoolInfo $schoolRecord): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($schoolRecord->family_member_id !== $member->id) {
            return $this->notFound('School record not found');
        }

        $schoolRecord->delete();

        return $this->success(null, 'School record deleted successfully');
    }

    // ==================== EDUCATION DOCUMENTS ====================

    /**
     * Store an education document via API.
     */
    public function storeEducationDocument(Request $request, FamilyCircle $familyCircle, FamilyMember $member): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        $validated = $request->validate([
            'document_type' => 'required|string|in:report_card,transcript,diploma,certificate,award,iep,504_plan,enrollment,immunization,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'school_year' => 'nullable|string|max:20',
            'grade_level' => 'nullable|string|max:50',
            'school_record_id' => 'nullable|integer|exists:member_school_infos,id',
            'file' => 'required|string', // Base64 encoded file
            'file_name' => 'required|string|max:255',
            'mime_type' => 'required|string|max:100',
        ]);

        // Decode base64 file
        $fileData = base64_decode($validated['file']);
        if ($fileData === false) {
            return $this->error('Invalid file data', 422);
        }

        // Validate file size (10MB max)
        if (strlen($fileData) > 10 * 1024 * 1024) {
            return $this->error('File size exceeds 10MB limit', 422);
        }

        // Upload file to DigitalOcean Spaces
        $tenantId = $user->tenant_id;
        $fileName = $validated['file_name'];
        $path = "tenants/{$tenantId}/education-documents/{$member->id}/" . uniqid() . '_' . $fileName;

        \Illuminate\Support\Facades\Storage::disk('do_spaces')->put($path, $fileData, 'private');

        $document = \App\Models\MemberEducationDocument::create([
            'tenant_id' => $tenantId,
            'family_member_id' => $member->id,
            'school_record_id' => $validated['school_record_id'] ?? null,
            'uploaded_by' => $user->id,
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'school_year' => $validated['school_year'] ?? null,
            'grade_level' => $validated['grade_level'] ?? null,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => strlen($fileData),
            'mime_type' => $validated['mime_type'],
        ]);

        return $this->success([
            'document' => [
                'id' => $document->id,
                'document_type' => $document->document_type,
                'document_type_name' => $document->document_type_name,
                'title' => $document->title,
                'file_name' => $document->file_name,
            ],
        ], 'Education document uploaded successfully', 201);
    }

    /**
     * Delete an education document via API.
     */
    public function deleteEducationDocument(Request $request, FamilyCircle $familyCircle, FamilyMember $member, \App\Models\MemberEducationDocument $document): JsonResponse
    {
        $user = $request->user();

        if ($familyCircle->tenant_id !== $user->tenant_id || $member->family_circle_id !== $familyCircle->id) {
            return $this->notFound('Member not found');
        }

        if ($document->family_member_id !== $member->id) {
            return $this->notFound('Document not found');
        }

        // Delete file from storage
        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::disk('do_spaces')->delete($document->file_path);
        }

        $document->delete();

        return $this->success(null, 'Education document deleted successfully');
    }

    /**
     * Get all family members across all family circles for the current tenant.
     * Used for selecting policyholders, covered members, etc.
     */
    public function allMembers(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $members = FamilyMember::whereHas('familyCircle', function ($query) use ($tenant) {
            $query->where('tenant_id', $tenant->id);
        })
            ->select('id', 'first_name', 'last_name', 'family_circle_id')
            ->orderBy('first_name')
            ->get();

        return $this->success([
            'members' => $members,
        ]);
    }

    /**
     * Save a base64 encoded image to storage.
     */
    private function saveBase64Image(string $imageData, string $folder): ?string
    {
        // Remove data URL prefix if present
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $extension = $matches[1];
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        } else {
            $extension = 'jpg';
        }

        $decodedImage = base64_decode($imageData);
        if ($decodedImage === false) {
            return null;
        }

        $filename = $folder . '_' . time() . '_' . uniqid() . '.' . $extension;
        $path = 'family-ledger/members/' . $folder . '/' . $filename;

        $disk = Storage::disk('do_spaces');
        $disk->put($path, $decodedImage);
        $disk->setVisibility($path, 'public');

        return $path;
    }
}
