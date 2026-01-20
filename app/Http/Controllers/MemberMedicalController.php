<?php

namespace App\Http\Controllers;

use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Models\MemberAllergy;
use App\Models\MemberHealthcareProvider;
use App\Models\MemberMedicalCondition;
use App\Models\MemberMedicalInfo;
use App\Models\MemberMedication;
use App\Models\MemberVaccination;
use App\Services\CollaboratorPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MemberMedicalController extends Controller
{
    /**
     * Display the medical info page for a family member.
     */
    public function show(FamilyCircle $familyCircle, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('medical')) {
            abort(403);
        }

        // For linked members (Self), load data from all linked member records
        if ($member->linked_user_id) {
            $linkedMemberIds = FamilyMember::where('linked_user_id', $member->linked_user_id)
                ->pluck('id')
                ->toArray();

            $member->setRelation('allergies', MemberAllergy::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('healthcareProviders', MemberHealthcareProvider::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('medications', MemberMedication::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('medicalConditions', MemberMedicalCondition::whereIn('family_member_id', $linkedMemberIds)->get());
            $member->setRelation('vaccinations', MemberVaccination::whereIn('family_member_id', $linkedMemberIds)->orderBy('vaccination_date', 'desc')->get());
            $member->setRelation('medicalInfo', MemberMedicalInfo::whereIn('family_member_id', $linkedMemberIds)->first());
        } else {
            $member->load(['medicalInfo', 'allergies', 'healthcareProviders', 'medications', 'medicalConditions', 'vaccinations']);
        }

        return view('family-circle.member.medical-info', [
            'circle' => $familyCircle,
            'member' => $member,
            'allergyTypes' => MemberAllergy::ALLERGY_TYPES,
            'severities' => MemberAllergy::SEVERITIES,
            'symptoms' => MemberAllergy::SYMPTOMS,
            'providerTypes' => MemberHealthcareProvider::PROVIDER_TYPES,
            'specialties' => MemberHealthcareProvider::SPECIALTIES,
            'contactMethods' => MemberHealthcareProvider::CONTACT_METHODS,
            'bloodTypes' => MemberMedicalInfo::BLOOD_TYPES,
            'medicationFrequencies' => MemberMedication::FREQUENCIES,
            'conditionStatuses' => MemberMedicalCondition::STATUSES,
            'vaccineTypes' => MemberVaccination::VACCINE_TYPES,
            'access' => $permissionService->forView(),
        ]);
    }

    /**
     * Store or update general medical info.
     */
    public function updateMedicalInfo(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'blood_type' => 'nullable|string|in:' . implode(',', array_keys(MemberMedicalInfo::BLOOD_TYPES)),
            'medications' => 'nullable|string',
            'medical_conditions' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_group_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberMedicalInfo::updateOrCreate(
            ['family_member_id' => $member->id],
            $validated
        );

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medical information updated successfully');
    }

    /**
     * Store a new allergy.
     */
    public function storeAllergy(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canCreate('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'allergy_type' => 'required|string|in:' . implode(',', array_keys(MemberAllergy::ALLERGY_TYPES)),
            'allergen_name' => 'required|string|max:255',
            'severity' => 'required|string|in:' . implode(',', array_keys(MemberAllergy::SEVERITIES)),
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|in:' . implode(',', array_keys(MemberAllergy::SYMPTOMS)),
            'emergency_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberAllergy::create($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Allergy added successfully');
    }

    /**
     * Update an allergy.
     */
    public function updateAllergy(Request $request, FamilyMember $member, MemberAllergy $allergy)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'allergy_type' => 'required|string|in:' . implode(',', array_keys(MemberAllergy::ALLERGY_TYPES)),
            'allergen_name' => 'required|string|max:255',
            'severity' => 'required|string|in:' . implode(',', array_keys(MemberAllergy::SEVERITIES)),
            'symptoms' => 'nullable|array',
            'symptoms.*' => 'string|in:' . implode(',', array_keys(MemberAllergy::SYMPTOMS)),
            'emergency_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $allergy->update($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Allergy updated successfully');
    }

    /**
     * Delete an allergy.
     */
    public function destroyAllergy(FamilyMember $member, MemberAllergy $allergy)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canDelete('medical')) {
            abort(403);
        }

        $allergy->delete();

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Allergy removed successfully');
    }

    /**
     * Store a new healthcare provider.
     */
    public function storeProvider(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canCreate('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'provider_type' => 'required|string|in:' . implode(',', array_keys(MemberHealthcareProvider::PROVIDER_TYPES)),
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string',
            'clinic_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'preferred_contact' => 'nullable|string|in:' . implode(',', array_keys(MemberHealthcareProvider::CONTACT_METHODS)),
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;
        $validated['is_primary'] = $request->boolean('is_primary');

        MemberHealthcareProvider::create($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Healthcare provider added successfully');
    }

    /**
     * Update a healthcare provider.
     */
    public function updateProvider(Request $request, FamilyMember $member, MemberHealthcareProvider $provider)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'provider_type' => 'required|string|in:' . implode(',', array_keys(MemberHealthcareProvider::PROVIDER_TYPES)),
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string',
            'clinic_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'preferred_contact' => 'nullable|string|in:' . implode(',', array_keys(MemberHealthcareProvider::CONTACT_METHODS)),
            'notes' => 'nullable|string',
            'is_primary' => 'boolean',
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

        $provider->update($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Healthcare provider updated successfully');
    }

    /**
     * Delete a healthcare provider.
     */
    public function destroyProvider(FamilyMember $member, MemberHealthcareProvider $provider)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canDelete('medical')) {
            abort(403);
        }

        $provider->delete();

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Healthcare provider removed successfully');
    }

    /**
     * Store a new medication.
     */
    public function storeMedication(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canCreate('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|in:' . implode(',', array_keys(MemberMedication::FREQUENCIES)),
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberMedication::create($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medication added successfully');
    }

    /**
     * Update a medication.
     */
    public function updateMedication(Request $request, FamilyMember $member, MemberMedication $medication)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|in:' . implode(',', array_keys(MemberMedication::FREQUENCIES)),
            'notes' => 'nullable|string',
        ]);

        $medication->update($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medication updated successfully');
    }

    /**
     * Delete a medication.
     */
    public function destroyMedication(FamilyMember $member, MemberMedication $medication)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canDelete('medical')) {
            abort(403);
        }

        $medication->delete();

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medication removed successfully');
    }

    /**
     * Store a new medical condition.
     */
    public function storeCondition(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canCreate('medical')) {
            abort(403);
        }

        // Convert date format if needed
        if ($request->has('diagnosed_date') && $request->diagnosed_date) {
            $dateParts = explode('/', $request->diagnosed_date);
            if (count($dateParts) === 3) {
                $request->merge([
                    'diagnosed_date' => $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1]
                ]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|in:' . implode(',', array_keys(MemberMedicalCondition::STATUSES)),
            'diagnosed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        MemberMedicalCondition::create($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medical condition added successfully');
    }

    /**
     * Update a medical condition.
     */
    public function updateCondition(Request $request, FamilyMember $member, MemberMedicalCondition $condition)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        // Convert date format if needed
        if ($request->has('diagnosed_date') && $request->diagnosed_date) {
            $dateParts = explode('/', $request->diagnosed_date);
            if (count($dateParts) === 3) {
                $request->merge([
                    'diagnosed_date' => $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1]
                ]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'nullable|string|in:' . implode(',', array_keys(MemberMedicalCondition::STATUSES)),
            'diagnosed_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $condition->update($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medical condition updated successfully');
    }

    /**
     * Delete a medical condition.
     */
    public function destroyCondition(FamilyMember $member, MemberMedicalCondition $condition)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canDelete('medical')) {
            abort(403);
        }

        $condition->delete();

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Medical condition removed successfully');
    }

    /**
     * Store a new vaccination record.
     */
    public function storeVaccination(Request $request, FamilyMember $member)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canCreate('medical')) {
            abort(403);
        }

        // Convert date formats if needed
        $this->convertDateFormat($request, 'vaccination_date');
        $this->convertDateFormat($request, 'next_vaccination_date');

        $validated = $request->validate([
            'vaccine_type' => 'required|string|in:' . implode(',', array_keys(MemberVaccination::VACCINE_TYPES)),
            'custom_vaccine_name' => 'nullable|required_if:vaccine_type,other|string|max:255',
            'vaccination_date' => 'nullable|date',
            'next_vaccination_date' => 'nullable|date',
            'administered_by' => 'nullable|string|max:255',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['family_member_id'] = $member->id;

        // Handle file upload
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('vaccinations/' . $member->id, 'do_spaces');
            $validated['document_path'] = $path;
            $validated['document_name'] = $file->getClientOriginalName();
        }

        unset($validated['document']);
        MemberVaccination::create($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Vaccination record added successfully');
    }

    /**
     * Update a vaccination record.
     */
    public function updateVaccination(Request $request, FamilyMember $member, MemberVaccination $vaccination)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canEdit('medical')) {
            abort(403);
        }

        // Convert date formats if needed
        $this->convertDateFormat($request, 'vaccination_date');
        $this->convertDateFormat($request, 'next_vaccination_date');

        $validated = $request->validate([
            'vaccine_type' => 'required|string|in:' . implode(',', array_keys(MemberVaccination::VACCINE_TYPES)),
            'custom_vaccine_name' => 'nullable|required_if:vaccine_type,other|string|max:255',
            'vaccination_date' => 'nullable|date',
            'next_vaccination_date' => 'nullable|date',
            'administered_by' => 'nullable|string|max:255',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Handle file upload
        if ($request->hasFile('document')) {
            // Delete old file if exists
            if ($vaccination->document_path) {
                Storage::disk('do_spaces')->delete($vaccination->document_path);
            }
            $file = $request->file('document');
            $path = $file->store('vaccinations/' . $member->id, 'do_spaces');
            $validated['document_path'] = $path;
            $validated['document_name'] = $file->getClientOriginalName();
        }

        unset($validated['document']);
        $vaccination->update($validated);

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Vaccination record updated successfully');
    }

    /**
     * Delete a vaccination record.
     */
    public function destroyVaccination(FamilyMember $member, MemberVaccination $vaccination)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canDelete('medical')) {
            abort(403);
        }

        // Delete file if exists
        if ($vaccination->document_path) {
            Storage::disk('do_spaces')->delete($vaccination->document_path);
        }

        $vaccination->delete();

        return redirect()->route('family-circle.member.medical-info', [
            $member->familyCircle,
            $member
        ])->with('success', 'Vaccination record removed successfully');
    }

    /**
     * Download vaccination document.
     */
    public function downloadVaccinationDocument(FamilyMember $member, MemberVaccination $vaccination)
    {
        // Use centralized permission service
        $permissionService = CollaboratorPermissionService::forMember($member);

        if (!$permissionService->canView('medical')) {
            abort(403);
        }

        if (!$vaccination->document_path || !Storage::disk('do_spaces')->exists($vaccination->document_path)) {
            abort(404);
        }

        return Storage::disk('do_spaces')->download(
            $vaccination->document_path,
            $vaccination->document_name
        );
    }

    /**
     * Helper to convert date format from MM/DD/YYYY to YYYY-MM-DD.
     */
    private function convertDateFormat(Request $request, string $field): void
    {
        if ($request->has($field) && $request->$field) {
            $dateParts = explode('/', $request->$field);
            if (count($dateParts) === 3) {
                $request->merge([
                    $field => $dateParts[2] . '-' . $dateParts[0] . '-' . $dateParts[1]
                ]);
            }
        }
    }
}
