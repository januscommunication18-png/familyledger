<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    /**
     * Get all people/contacts.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $people = Person::where('tenant_id', $tenant->id)
            ->with(['emails', 'phones', 'addresses'])
            ->get()
            ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        // Group by relationship
        $byRelationship = $people->groupBy('relationship')->map->count();

        return $this->success([
            'people' => $people->map(function ($person) {
                return [
                    'id' => $person->id,
                    'full_name' => $person->full_name,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                    'nickname' => $person->nickname,
                    'relationship' => $person->relationship,
                    'relationship_name' => ucfirst(str_replace('_', ' ', $person->relationship ?? 'other')),
                    'company' => $person->company,
                    'job_title' => $person->job_title,
                    'profile_image_url' => $person->profile_image_url,
                    'tags' => $person->tags ?? [],
                    'primary_email' => $person->emails->first(),
                    'primary_phone' => $person->phones->first() ? [
                        'phone' => $person->phones->first()->phone,
                        'formatted_phone' => $person->phones->first()->phone,
                    ] : null,
                ];
            }),
            'total' => $people->count(),
            'by_relationship' => $byRelationship,
        ]);
    }

    /**
     * Get people by relationship type.
     */
    public function byRelationship(Request $request, string $relationship): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $people = Person::where('tenant_id', $tenant->id)
            ->where('relationship', $relationship)
            ->with(['emails', 'phones', 'addresses'])
            ->get()
            ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return $this->success([
            'people' => $people,
            'total' => $people->count(),
        ]);
    }

    /**
     * Get a single person.
     */
    public function show(Request $request, Person $person): JsonResponse
    {
        $user = $request->user();

        if ($person->tenant_id !== $user->tenant_id) {
            return $this->forbidden();
        }

        $person->load(['emails', 'phones', 'addresses', 'attachments', 'importantDates', 'links']);

        // Transform emails
        $emails = $person->emails->map(function ($email) {
            return [
                'id' => $email->id,
                'email' => $email->email,
                'label' => $email->label ?? 'Personal',
                'is_primary' => $email->is_primary ?? false,
            ];
        });

        // Transform phones
        $phones = $person->phones->map(function ($phone) {
            return [
                'id' => $phone->id,
                'phone' => $phone->phone,
                'formatted_phone' => $phone->phone,
                'label' => $phone->label ?? 'Mobile',
                'is_primary' => $phone->is_primary ?? false,
            ];
        });

        // Transform addresses
        $addresses = $person->addresses->map(function ($address) {
            $parts = array_filter([
                $address->street,
                $address->city,
                $address->state,
                $address->postal_code,
                $address->country,
            ]);
            return [
                'id' => $address->id,
                'label' => $address->label ?? 'Home',
                'street' => $address->street,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'full_address' => implode(', ', $parts),
                'is_primary' => $address->is_primary ?? false,
            ];
        });

        // Transform important dates
        $importantDates = $person->importantDates->map(function ($date) {
            return [
                'id' => $date->id,
                'label' => $date->label,
                'date' => $date->date?->format('M d, Y'),
                'date_raw' => $date->date?->format('Y-m-d'),
                'is_annual' => $date->is_annual ?? true,
            ];
        });

        // Transform links
        $links = $person->links->map(function ($link) {
            return [
                'id' => $link->id,
                'label' => $link->label ?? 'Website',
                'url' => $link->url,
            ];
        });

        // Transform attachments
        $attachments = $person->attachments->map(function ($attachment) {
            return [
                'id' => $attachment->id,
                'name' => $attachment->original_filename,
                'file_type' => $attachment->file_type,
                'mime_type' => $attachment->mime_type,
                'file_size' => $attachment->file_size,
                'formatted_size' => $this->formatFileSize($attachment->file_size),
                'is_image' => str_starts_with($attachment->mime_type ?? '', 'image/'),
            ];
        });

        // Calculate age if birthday is set
        $age = null;
        if ($person->birthday) {
            $age = $person->birthday->age;
        }

        return $this->success([
            'person' => [
                'id' => $person->id,
                'full_name' => $person->full_name,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'nickname' => $person->nickname,
                'relationship' => $person->relationship,
                'relationship_name' => ucfirst(str_replace('_', ' ', $person->relationship ?? 'other')),
                'custom_relationship' => $person->custom_relationship,
                'company' => $person->company,
                'job_title' => $person->job_title,
                'birthday' => $person->birthday?->format('M d, Y'),
                'birthday_raw' => $person->birthday?->format('Y-m-d'),
                'age' => $age,
                'profile_image_url' => $person->profile_image_url,
                'notes' => $person->notes,
                'how_we_know' => $person->how_we_know,
                'visibility' => $person->visibility,
                'visibility_name' => ucfirst($person->visibility ?? 'family'),
                'source' => $person->source,
                'source_name' => ucfirst($person->source ?? 'manual'),
                'tags' => $person->tags ?? [],
                'met_at' => $person->met_at,
                'met_location' => $person->met_location,
                'created_at' => $person->created_at?->format('M d, Y'),
                'updated_at' => $person->updated_at?->format('M d, Y'),
            ],
            'emails' => $emails,
            'phones' => $phones,
            'addresses' => $addresses,
            'important_dates' => $importantDates,
            'links' => $links,
            'attachments' => $attachments,
            'stats' => [
                'emails' => $emails->count(),
                'phones' => $phones->count(),
                'addresses' => $addresses->count(),
                'important_dates' => $importantDates->count(),
                'attachments' => $attachments->count(),
            ],
        ]);
    }

    /**
     * Format file size to human readable string.
     */
    private function formatFileSize(?int $bytes): string
    {
        if (!$bytes) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }

    /**
     * Search people.
     */
    public function search(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $query = strtolower($request->get('q', ''));

        // Since full_name, nickname, company are encrypted, we need to search in PHP
        $people = Person::where('tenant_id', $tenant->id)
            ->with(['emails', 'phones'])
            ->get()
            ->filter(function ($person) use ($query) {
                // Search in decrypted fields
                if (str_contains(strtolower($person->full_name ?? ''), $query)) {
                    return true;
                }
                if (str_contains(strtolower($person->nickname ?? ''), $query)) {
                    return true;
                }
                if (str_contains(strtolower($person->company ?? ''), $query)) {
                    return true;
                }
                // Search in emails
                foreach ($person->emails as $email) {
                    if (str_contains(strtolower($email->email ?? ''), $query)) {
                        return true;
                    }
                }
                return false;
            })
            ->sortBy('full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->take(20);

        return $this->success([
            'people' => $people,
            'total' => $people->count(),
        ]);
    }
}
