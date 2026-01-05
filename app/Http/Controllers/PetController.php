<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Pet;
use App\Models\PetMedication;
use App\Models\PetVaccination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    /**
     * Display a listing of pets.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $query = Pet::where('tenant_id', $tenantId)
            ->with(['caregivers', 'vaccinations', 'medications']);

        // Filter by status (default: active only)
        if ($request->boolean('include_passed_away')) {
            // Include all pets
        } else {
            $query->active();
        }

        // Filter by species
        if ($request->filled('species')) {
            $query->where('species', $request->species);
        }

        $pets = $query->orderBy('name')->get();

        // Get stats
        $totalPets = Pet::where('tenant_id', $tenantId)->active()->count();
        $upcomingVaccinations = PetVaccination::where('tenant_id', $tenantId)
            ->upcoming(30)
            ->count();
        $overdueVaccinations = PetVaccination::where('tenant_id', $tenantId)
            ->overdue()
            ->count();

        return view('pages.pets.index', [
            'pets' => $pets,
            'totalPets' => $totalPets,
            'upcomingVaccinations' => $upcomingVaccinations,
            'overdueVaccinations' => $overdueVaccinations,
            'species' => Pet::SPECIES,
            'filters' => $request->only(['species', 'include_passed_away']),
        ]);
    }

    /**
     * Show the form for creating a new pet.
     */
    public function create()
    {
        $user = Auth::user();
        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->orderBy('first_name')
            ->get();

        return view('pages.pets.form', [
            'pet' => null,
            'species' => Pet::SPECIES,
            'genders' => Pet::GENDERS,
            'statuses' => Pet::STATUSES,
            'visibility' => Pet::VISIBILITY,
            'familyMembers' => $familyMembers,
            'currentUser' => $user,
        ]);
    }

    /**
     * Store a newly created pet.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|in:' . implode(',', array_keys(Pet::SPECIES)),
            'breed' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'approx_age' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:' . implode(',', array_keys(Pet::GENDERS)),
            'photo' => 'nullable|image|max:5120', // 5MB max
            'microchip_id' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_keys(Pet::STATUSES)),
            'passed_away_date' => 'nullable|date|before_or_equal:today',
            'allergies' => 'nullable|string|max:1000',
            'conditions' => 'nullable|string|max:1000',
            'last_vet_visit' => 'nullable|date|before_or_equal:today',
            'notes' => 'nullable|string|max:2000',
            'visibility' => 'required|string|in:' . implode(',', array_keys(Pet::VISIBILITY)),
            'vet_name' => 'nullable|string|max:255',
            'vet_phone' => 'nullable|string|max:50',
            'vet_clinic' => 'nullable|string|max:255',
            'vet_address' => 'nullable|string|max:500',
            'primary_caregiver' => 'nullable|string',
            'secondary_caregivers' => 'nullable|array',
            'secondary_caregivers.*' => 'string',
        ]);

        $user = Auth::user();

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pets/photos', 'public');
        }

        $pet = Pet::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed,
            'date_of_birth' => $request->date_of_birth,
            'approx_age' => $request->approx_age,
            'gender' => $request->gender,
            'photo' => $photoPath,
            'microchip_id' => $request->microchip_id,
            'status' => $request->status,
            'passed_away_date' => $request->status === 'passed_away' ? $request->passed_away_date : null,
            'allergies' => $request->allergies,
            'conditions' => $request->conditions,
            'last_vet_visit' => $request->last_vet_visit,
            'notes' => $request->notes,
            'visibility' => $request->visibility,
            'vet_name' => $request->vet_name,
            'vet_phone' => $request->vet_phone,
            'vet_clinic' => $request->vet_clinic,
            'vet_address' => $request->vet_address,
            'created_by' => $user->id,
        ]);

        // Add primary caregiver
        if ($request->filled('primary_caregiver')) {
            $caregiverId = $this->resolveCaregiverId($request->primary_caregiver, $user);
            if ($caregiverId) {
                $pet->addCaregiver($caregiverId, 'primary');
            }
        }

        // Add secondary caregivers
        if ($request->filled('secondary_caregivers')) {
            $primaryId = $request->primary_caregiver === 'me'
                ? $this->resolveCaregiverId('me', $user)
                : $request->primary_caregiver;

            foreach ($request->secondary_caregivers as $caregiver) {
                $caregiverId = $this->resolveCaregiverId($caregiver, $user);
                if ($caregiverId && $caregiverId != $primaryId) {
                    $pet->addCaregiver($caregiverId, 'secondary');
                }
            }
        }

        return redirect()->route('pets.show', $pet)
            ->with('success', $pet->name . ' has been added to your family!');
    }

    /**
     * Display the specified pet.
     */
    public function show(Pet $pet)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $pet->load([
            'caregivers',
            'vaccinations' => fn($q) => $q->orderBy('date_administered', 'desc'),
            'medications' => fn($q) => $q->orderBy('is_active', 'desc')->orderBy('name'),
            'createdBy',
        ]);

        // Get vaccination stats
        $upcomingVaccinations = $pet->vaccinations()->upcoming(30)->get();
        $overdueVaccinations = $pet->vaccinations()->overdue()->get();

        return view('pages.pets.show', [
            'pet' => $pet,
            'upcomingVaccinations' => $upcomingVaccinations,
            'overdueVaccinations' => $overdueVaccinations,
            'medicationFrequencies' => PetMedication::FREQUENCIES,
        ]);
    }

    /**
     * Show the form for editing the specified pet.
     */
    public function edit(Pet $pet)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $pet->load('caregivers');

        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->orderBy('first_name')
            ->get();

        return view('pages.pets.form', [
            'pet' => $pet,
            'species' => Pet::SPECIES,
            'genders' => Pet::GENDERS,
            'statuses' => Pet::STATUSES,
            'visibility' => Pet::VISIBILITY,
            'familyMembers' => $familyMembers,
            'currentUser' => $user,
        ]);
    }

    /**
     * Update the specified pet.
     */
    public function update(Request $request, Pet $pet)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|in:' . implode(',', array_keys(Pet::SPECIES)),
            'breed' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'approx_age' => 'nullable|string|max:100',
            'gender' => 'nullable|string|in:' . implode(',', array_keys(Pet::GENDERS)),
            'photo' => 'nullable|image|max:5120',
            'microchip_id' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_keys(Pet::STATUSES)),
            'passed_away_date' => 'nullable|date|before_or_equal:today',
            'allergies' => 'nullable|string|max:1000',
            'conditions' => 'nullable|string|max:1000',
            'last_vet_visit' => 'nullable|date|before_or_equal:today',
            'notes' => 'nullable|string|max:2000',
            'visibility' => 'required|string|in:' . implode(',', array_keys(Pet::VISIBILITY)),
            'vet_name' => 'nullable|string|max:255',
            'vet_phone' => 'nullable|string|max:50',
            'vet_clinic' => 'nullable|string|max:255',
            'vet_address' => 'nullable|string|max:500',
            'primary_caregiver' => 'nullable|string',
            'secondary_caregivers' => 'nullable|array',
            'secondary_caregivers.*' => 'string',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($pet->photo) {
                Storage::disk('public')->delete($pet->photo);
            }
            $pet->photo = $request->file('photo')->store('pets/photos', 'public');
        }

        // Handle photo removal
        if ($request->boolean('remove_photo') && $pet->photo) {
            Storage::disk('public')->delete($pet->photo);
            $pet->photo = null;
        }

        $pet->update([
            'name' => $request->name,
            'species' => $request->species,
            'breed' => $request->breed,
            'date_of_birth' => $request->date_of_birth,
            'approx_age' => $request->approx_age,
            'gender' => $request->gender,
            'microchip_id' => $request->microchip_id,
            'status' => $request->status,
            'passed_away_date' => $request->status === 'passed_away' ? $request->passed_away_date : null,
            'allergies' => $request->allergies,
            'conditions' => $request->conditions,
            'last_vet_visit' => $request->last_vet_visit,
            'notes' => $request->notes,
            'visibility' => $request->visibility,
            'vet_name' => $request->vet_name,
            'vet_phone' => $request->vet_phone,
            'vet_clinic' => $request->vet_clinic,
            'vet_address' => $request->vet_address,
        ]);

        // Update caregivers
        $pet->caregivers()->detach();

        $primaryCaregiverId = null;
        if ($request->filled('primary_caregiver')) {
            $primaryCaregiverId = $this->resolveCaregiverId($request->primary_caregiver, $user);
            if ($primaryCaregiverId) {
                $pet->addCaregiver($primaryCaregiverId, 'primary');
            }
        }

        if ($request->filled('secondary_caregivers')) {
            foreach ($request->secondary_caregivers as $caregiver) {
                $caregiverId = $this->resolveCaregiverId($caregiver, $user);
                if ($caregiverId && $caregiverId != $primaryCaregiverId) {
                    $pet->addCaregiver($caregiverId, 'secondary');
                }
            }
        }

        return redirect()->route('pets.show', $pet)
            ->with('success', $pet->name . "'s profile has been updated!");
    }

    /**
     * Remove the specified pet.
     */
    public function destroy(Pet $pet)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $petName = $pet->name;

        // Delete photo
        if ($pet->photo) {
            Storage::disk('public')->delete($pet->photo);
        }

        $pet->delete();

        return redirect()->route('pets.index')
            ->with('success', $petName . ' has been removed.');
    }

    // ==================== VACCINATION METHODS ====================

    /**
     * Store a vaccination record.
     */
    public function storeVaccination(Request $request, Pet $pet)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'date_administered' => 'required|date|before_or_equal:today',
            'next_due_date' => 'nullable|date|after:date_administered',
            'administered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $pet->vaccinations()->create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'date_administered' => $request->date_administered,
            'next_due_date' => $request->next_due_date,
            'administered_by' => $request->administered_by,
            'notes' => $request->notes,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Vaccination record added.');
    }

    /**
     * Update a vaccination record.
     */
    public function updateVaccination(Request $request, Pet $pet, PetVaccination $vaccination)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id || $vaccination->pet_id !== $pet->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'date_administered' => 'required|date|before_or_equal:today',
            'next_due_date' => 'nullable|date|after:date_administered',
            'administered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $vaccination->update($request->only([
            'name', 'date_administered', 'next_due_date', 'administered_by', 'notes'
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Vaccination record updated.');
    }

    /**
     * Delete a vaccination record.
     */
    public function destroyVaccination(Pet $pet, PetVaccination $vaccination)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id || $vaccination->pet_id !== $pet->id) {
            abort(403);
        }

        $vaccination->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Vaccination record deleted.');
    }

    // ==================== MEDICATION METHODS ====================

    /**
     * Store a medication record.
     */
    public function storeMedication(Request $request, Pet $pet)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|in:' . implode(',', array_keys(PetMedication::FREQUENCIES)),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'instructions' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $pet->medications()->create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'dosage' => $request->dosage,
            'frequency' => $request->frequency,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'instructions' => $request->instructions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Medication added.');
    }

    /**
     * Update a medication record.
     */
    public function updateMedication(Request $request, Pet $pet, PetMedication $medication)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id || $medication->pet_id !== $pet->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'dosage' => 'nullable|string|max:255',
            'frequency' => 'nullable|string|in:' . implode(',', array_keys(PetMedication::FREQUENCIES)),
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'instructions' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $medication->update([
            'name' => $request->name,
            'dosage' => $request->dosage,
            'frequency' => $request->frequency,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'instructions' => $request->instructions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Medication updated.');
    }

    /**
     * Toggle medication active status.
     */
    public function toggleMedication(Pet $pet, PetMedication $medication)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id || $medication->pet_id !== $pet->id) {
            abort(403);
        }

        $medication->update(['is_active' => !$medication->is_active]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => $medication->is_active,
            ]);
        }

        return redirect()->back()->with('success', 'Medication status updated.');
    }

    /**
     * Delete a medication record.
     */
    public function destroyMedication(Pet $pet, PetMedication $medication)
    {
        $user = Auth::user();

        if ($pet->tenant_id !== $user->tenant_id || $medication->pet_id !== $pet->id) {
            abort(403);
        }

        $medication->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Medication deleted.');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Resolve caregiver ID from form value.
     * Handles "me" special value by finding or creating the user's family member record.
     */
    protected function resolveCaregiverId($value, $user)
    {
        if (!$value) {
            return null;
        }

        // Handle "me" special value - find or create family member for current user
        if ($value === 'me') {
            // First try to find by linked_user_id
            $familyMember = FamilyMember::where('tenant_id', $user->tenant_id)
                ->where('linked_user_id', $user->id)
                ->first();

            // If not found, try by email
            if (!$familyMember && $user->email) {
                $familyMember = FamilyMember::where('tenant_id', $user->tenant_id)
                    ->where('email', $user->email)
                    ->first();
            }

            // If still not found, create a new family member for the user
            if (!$familyMember) {
                $nameParts = explode(' ', $user->name, 2);
                $familyMember = FamilyMember::create([
                    'tenant_id' => $user->tenant_id,
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $user->email,
                    'relationship' => 'self',
                    'linked_user_id' => $user->id,
                ]);
            }

            return $familyMember->id;
        }

        // Regular numeric ID
        return is_numeric($value) ? (int) $value : null;
    }
}
