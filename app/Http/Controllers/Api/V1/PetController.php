<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Pet;
use App\Models\PetVaccination;
use App\Models\PetMedication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PetController extends Controller
{
    private const SPECIES_LABELS = [
        'dog' => 'Dog',
        'cat' => 'Cat',
        'bird' => 'Bird',
        'fish' => 'Fish',
        'reptile' => 'Reptile',
        'small_mammal' => 'Small Mammal',
        'other' => 'Other',
    ];

    private const SPECIES_EMOJIS = [
        'dog' => '🐕',
        'cat' => '🐱',
        'bird' => '🐦',
        'fish' => '🐠',
        'reptile' => '🦎',
        'small_mammal' => '🐹',
        'other' => '🐾',
    ];

    /**
     * Get all pets.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $rawPets = Pet::where('tenant_id', $tenant->id)
            ->with(['vaccinations', 'medications'])
            ->orderBy('name', 'asc')
            ->get();

        // Transform pets to match mobile app format
        $pets = $rawPets->map(function ($pet) {
            return [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'species_label' => self::SPECIES_LABELS[$pet->species] ?? 'Other',
                'species_emoji' => self::SPECIES_EMOJIS[$pet->species] ?? '🐾',
                'breed' => $pet->breed,
                'date_of_birth' => $pet->date_of_birth?->format('Y-m-d'),
                'age' => $pet->age,
                'gender' => $pet->gender,
                'microchip_id' => $pet->microchip_id,
                'photo_url' => $pet->photo_url,
                'is_passed_away' => $pet->status === 'passed_away',
                'passed_away_date' => $pet->passed_away_date?->format('Y-m-d'),
                'vet_name' => $pet->vet_name,
                'vet_phone' => $pet->vet_phone,
                'notes' => $pet->notes,
                'overdue_vaccinations' => $pet->vaccinations->filter(function ($v) {
                    return $v->next_due_date && $v->next_due_date < now();
                })->values(),
                'upcoming_vaccinations' => $pet->vaccinations->filter(function ($v) {
                    return $v->next_due_date && $v->next_due_date >= now() && $v->next_due_date <= now()->addDays(30);
                })->values(),
                'active_medications' => $pet->medications->filter(function ($m) {
                    return !$m->end_date || $m->end_date >= now();
                })->values(),
                'created_at' => $pet->created_at?->toISOString(),
                'updated_at' => $pet->updated_at?->toISOString(),
            ];
        });

        // Calculate vaccination stats
        $allVaccinations = $rawPets->flatMap->vaccinations;
        $upcomingVaccinations = $allVaccinations->filter(function ($v) {
            return $v->next_due_date && $v->next_due_date >= now() && $v->next_due_date <= now()->addDays(30);
        })->count();
        $overdueVaccinations = $allVaccinations->filter(function ($v) {
            return $v->next_due_date && $v->next_due_date < now();
        })->count();

        return $this->success([
            'pets' => $pets,
            'total_pets' => $rawPets->count(),
            'upcoming_vaccinations' => $upcomingVaccinations,
            'overdue_vaccinations' => $overdueVaccinations,
            'stats' => [
                'total' => $rawPets->count(),
                'by_species' => $rawPets->groupBy('species')->map->count(),
            ],
        ]);
    }

    /**
     * Get a single pet.
     */
    public function show(Request $request, Pet $pet): JsonResponse
    {
        $user = $request->user();

        if ($pet->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $pet->load(['vaccinations', 'medications']);

        // Calculate age short format
        $ageShort = null;
        if ($pet->date_of_birth) {
            $years = (int) $pet->date_of_birth->diffInYears(now());
            $months = (int) ($pet->date_of_birth->diffInMonths(now()) % 12);
            $ageShort = $years > 0 ? "{$years}y {$months}m" : "{$months}m";
        } elseif ($pet->approx_age) {
            $years = (int) floor($pet->approx_age);
            $months = (int) round(($pet->approx_age - $years) * 12);
            $ageShort = $years > 0 ? "{$years}y {$months}m" : "{$months}m";
        }

        // Transform vaccinations
        $vaccinations = $pet->vaccinations->map(function ($v) {
            $status = 'current';
            if ($v->next_due_date) {
                if ($v->next_due_date < now()) {
                    $status = 'overdue';
                } elseif ($v->next_due_date <= now()->addDays(30)) {
                    $status = 'due_soon';
                }
            }
            return [
                'id' => $v->id,
                'name' => $v->vaccine_name,
                'administered_date' => $v->administered_date?->format('M d, Y'),
                'next_due_date' => $v->next_due_date?->format('M d, Y'),
                'next_due_date_raw' => $v->next_due_date?->format('Y-m-d'),
                'administered_by' => $v->administered_by,
                'batch_number' => $v->batch_number,
                'notes' => $v->notes,
                'status' => $status,
            ];
        })->sortByDesc('administered_date')->values();

        // Transform medications
        $medications = $pet->medications->map(function ($m) {
            $isActive = !$m->end_date || $m->end_date >= now();
            return [
                'id' => $m->id,
                'name' => $m->medication_name,
                'dosage' => $m->dosage,
                'frequency' => $m->frequency,
                'start_date' => $m->start_date?->format('M d, Y'),
                'end_date' => $m->end_date?->format('M d, Y'),
                'prescribed_by' => $m->prescribed_by,
                'reason' => $m->reason,
                'notes' => $m->notes,
                'is_active' => $isActive,
            ];
        })->sortByDesc('is_active')->values();

        // Count stats
        $overdueCount = $vaccinations->where('status', 'overdue')->count();
        $dueSoonCount = $vaccinations->where('status', 'due_soon')->count();
        $activeMedsCount = $medications->where('is_active', true)->count();

        return $this->success([
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'species_label' => self::SPECIES_LABELS[$pet->species] ?? 'Other',
                'species_emoji' => self::SPECIES_EMOJIS[$pet->species] ?? '🐾',
                'breed' => $pet->breed,
                'gender' => $pet->gender,
                'gender_label' => $pet->gender ? ucfirst($pet->gender) : null,
                'date_of_birth' => $pet->date_of_birth?->format('M d, Y'),
                'date_of_birth_raw' => $pet->date_of_birth?->format('Y-m-d'),
                'age' => $pet->age,
                'age_short' => $ageShort,
                'weight' => $pet->weight,
                'color' => $pet->color,
                'microchip_id' => $pet->microchip_id,
                'photo_url' => $pet->photo_url,
                'status' => $pet->status,
                'is_passed_away' => $pet->status === 'passed_away',
                'passed_away_date' => $pet->passed_away_date?->format('M d, Y'),
                'vet_name' => $pet->vet_name,
                'vet_phone' => $pet->vet_phone,
                'vet_address' => $pet->vet_address,
                'insurance_provider' => $pet->insurance_provider,
                'insurance_policy_number' => $pet->insurance_policy_number,
                'notes' => $pet->notes,
                'created_at' => $pet->created_at?->toISOString(),
                'updated_at' => $pet->updated_at?->toISOString(),
            ],
            'vaccinations' => $vaccinations,
            'medications' => $medications,
            'stats' => [
                'overdue_vaccinations' => $overdueCount,
                'due_soon_vaccinations' => $dueSoonCount,
                'active_medications' => $activeMedsCount,
                'total_vaccinations' => $vaccinations->count(),
                'total_medications' => $medications->count(),
            ],
        ]);
    }

    /**
     * Get pet vaccinations.
     */
    public function vaccinations(Request $request, Pet $pet): JsonResponse
    {
        $user = $request->user();

        if ($pet->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $vaccinations = $pet->vaccinations()->orderBy('administered_date', 'desc')->get();

        return $this->success([
            'vaccinations' => $vaccinations,
            'total' => $vaccinations->count(),
        ]);
    }

    /**
     * Get pet medications.
     */
    public function medications(Request $request, Pet $pet): JsonResponse
    {
        $user = $request->user();

        if ($pet->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $medications = $pet->medications()->orderBy('created_at', 'desc')->get();

        return $this->success([
            'medications' => $medications,
            'total' => $medications->count(),
        ]);
    }
}
