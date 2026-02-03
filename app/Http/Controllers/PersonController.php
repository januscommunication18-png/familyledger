<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Person;
use App\Models\PersonAddress;
use App\Models\PersonAttachment;
use App\Models\PersonEmail;
use App\Models\PersonImportantDate;
use App\Models\PersonLink;
use App\Models\PersonPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PersonController extends Controller
{
    /**
     * Display the people directory.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Person::where('tenant_id', $user->tenant_id)
            ->with(['emails', 'phones', 'addresses'])
            ->orderBy('full_name');

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Relationship filter
        if ($request->filled('relationship')) {
            $query->byRelationship($request->relationship);
        }

        // Tag filter
        if ($request->filled('tag')) {
            $query->byTag($request->tag);
        }

        $people = $query->paginate(24);

        // Get all unique tags for filter dropdown
        $allTags = Person::where('tenant_id', $user->tenant_id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('pages.people.index', [
            'people' => $people,
            'relationships' => Person::RELATIONSHIPS,
            'allTags' => $allTags,
            'filters' => [
                'search' => $request->search,
                'relationship' => $request->relationship,
                'tag' => $request->tag,
            ],
        ]);
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $user = Auth::user();

        // Get family members for visibility selection
        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->with('familyCircle')
            ->orderBy('first_name')
            ->get();

        return view('pages.people.form', [
            'person' => null,
            'relationships' => Person::RELATIONSHIPS,
            'visibilities' => Person::VISIBILITIES,
            'emailLabels' => PersonEmail::LABELS,
            'phoneLabels' => PersonPhone::LABELS,
            'addressLabels' => PersonAddress::LABELS,
            'linkLabels' => PersonLink::LABELS,
            'attachmentTypes' => PersonAttachment::TYPES,
            'familyMembers' => $familyMembers,
        ]);
    }

    /**
     * Store a new person.
     */
    public function store(Request $request)
    {
        // Parse date formats before validation
        $this->parseDateInputs($request);

        // Handle checkbox boolean - set to false if not present
        $request->merge(['birthday_reminder' => $request->has('birthday_reminder')]);

        $validated = $this->validatePerson($request);

        $data = collect($validated)->except([
            'emails', 'phones', 'addresses', 'important_dates', 'links', 'attachments', 'tags_input'
        ])->toArray();

        $data['tenant_id'] = Auth::user()->tenant_id;
        $data['created_by'] = Auth::id();
        $data['source'] = Person::SOURCE_MANUAL;

        // Handle tags
        if ($request->filled('tags_input')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags_input));
        }

        // Handle profile image
        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $request->file('profile_image')->store(
                'people/' . Auth::user()->tenant_id . '/profiles',
                'do_spaces'
            );
        }

        $person = Person::create($data);

        // Sync related data
        $this->syncEmails($person, $validated['emails'] ?? []);
        $this->syncPhones($person, $validated['phones'] ?? []);
        $this->syncAddresses($person, $validated['addresses'] ?? []);
        $this->syncImportantDates($person, $validated['important_dates'] ?? []);
        $this->syncLinks($person, $validated['links'] ?? []);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $this->handleAttachments($person, $request->file('attachments'), $validated['attachment_types'] ?? []);
        }

        return redirect()->route('people.show', $person)
            ->with('success', 'Contact added successfully');
    }

    /**
     * Show person details.
     */
    public function show(Person $person)
    {
        if ($person->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        $person->load([
            'emails', 'phones', 'addresses',
            'importantDates', 'links', 'attachments', 'creator'
        ]);

        return view('pages.people.show', [
            'person' => $person,
            'relationships' => Person::RELATIONSHIPS,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Person $person)
    {
        $user = Auth::user();

        if ($person->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $person->load([
            'emails', 'phones', 'addresses',
            'importantDates', 'links', 'attachments'
        ]);

        // Get family members for visibility selection
        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)
            ->with('familyCircle')
            ->orderBy('first_name')
            ->get();

        return view('pages.people.form', [
            'person' => $person,
            'relationships' => Person::RELATIONSHIPS,
            'visibilities' => Person::VISIBILITIES,
            'emailLabels' => PersonEmail::LABELS,
            'phoneLabels' => PersonPhone::LABELS,
            'addressLabels' => PersonAddress::LABELS,
            'linkLabels' => PersonLink::LABELS,
            'attachmentTypes' => PersonAttachment::TYPES,
            'familyMembers' => $familyMembers,
        ]);
    }

    /**
     * Update a person.
     */
    public function update(Request $request, Person $person)
    {
        if ($person->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Parse date formats before validation
        $this->parseDateInputs($request);

        // Handle checkbox boolean - set to false if not present
        $request->merge(['birthday_reminder' => $request->has('birthday_reminder')]);

        $validated = $this->validatePerson($request);

        $data = collect($validated)->except([
            'emails', 'phones', 'addresses', 'important_dates', 'links', 'attachments', 'tags_input'
        ])->toArray();

        // Handle tags
        if ($request->filled('tags_input')) {
            $data['tags'] = array_map('trim', explode(',', $request->tags_input));
        } else {
            $data['tags'] = null;
        }

        // Handle profile image
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($person->profile_image) {
                Storage::disk('do_spaces')->delete($person->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store(
                'people/' . Auth::user()->tenant_id . '/profiles',
                'do_spaces'
            );
        }

        $person->update($data);

        // Sync related data
        $this->syncEmails($person, $validated['emails'] ?? []);
        $this->syncPhones($person, $validated['phones'] ?? []);
        $this->syncAddresses($person, $validated['addresses'] ?? []);
        $this->syncImportantDates($person, $validated['important_dates'] ?? []);
        $this->syncLinks($person, $validated['links'] ?? []);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $this->handleAttachments($person, $request->file('attachments'), $validated['attachment_types'] ?? []);
        }

        return redirect()->route('people.show', $person)
            ->with('success', 'Contact updated successfully');
    }

    /**
     * Delete a person.
     */
    public function destroy(Person $person)
    {
        if ($person->tenant_id !== Auth::user()->tenant_id) {
            abort(403);
        }

        // Delete profile image
        if ($person->profile_image) {
            Storage::disk('do_spaces')->delete($person->profile_image);
        }

        // Delete attachments
        foreach ($person->attachments as $attachment) {
            Storage::disk('do_spaces')->delete($attachment->file_path);
        }

        $person->delete();

        return redirect()->route('people.index')
            ->with('success', 'Contact deleted successfully');
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(Person $person, PersonAttachment $attachment)
    {
        if ($person->tenant_id !== Auth::user()->tenant_id || $attachment->person_id !== $person->id) {
            abort(403);
        }

        Storage::disk('do_spaces')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully');
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Person $person, PersonAttachment $attachment)
    {
        if ($person->tenant_id !== Auth::user()->tenant_id || $attachment->person_id !== $person->id) {
            abort(403);
        }

        if (!Storage::disk('do_spaces')->exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::disk('do_spaces')->download($attachment->file_path, $attachment->original_filename);
    }

    /**
     * Validate person request.
     */
    private function validatePerson(Request $request): array
    {
        return $request->validate([
            'full_name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:255',
            'relationship' => 'required|string|in:' . implode(',', array_keys(Person::RELATIONSHIPS)),
            'custom_relationship' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'birthday_reminder' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'how_we_know' => 'nullable|string|max:255',
            'tags_input' => 'nullable|string',
            'visibility' => 'nullable|string|in:' . implode(',', array_keys(Person::VISIBILITIES)),
            'profile_image' => 'nullable|image|max:5120',

            // Emails
            'emails' => 'nullable|array',
            'emails.*.email' => 'nullable|email|max:255',
            'emails.*.label' => 'nullable|string|in:' . implode(',', array_keys(PersonEmail::LABELS)),
            'emails.*.is_primary' => 'nullable|boolean',

            // Phones
            'phones' => 'nullable|array',
            'phones.*.phone' => 'nullable|string|max:50',
            'phones.*.country_code' => 'nullable|string|max:10',
            'phones.*.label' => 'nullable|string|in:' . implode(',', array_keys(PersonPhone::LABELS)),
            'phones.*.is_primary' => 'nullable|boolean',

            // Addresses
            'addresses' => 'nullable|array',
            'addresses.*.label' => 'nullable|string|in:' . implode(',', array_keys(PersonAddress::LABELS)),
            'addresses.*.street_address' => 'nullable|string|max:255',
            'addresses.*.street_address_2' => 'nullable|string|max:255',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'nullable|string|max:100',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.is_primary' => 'nullable|boolean',

            // Important dates
            'important_dates' => 'nullable|array',
            'important_dates.*.label' => 'nullable|string|max:255',
            'important_dates.*.date' => 'nullable|date',
            'important_dates.*.recurring_yearly' => 'nullable|boolean',
            'important_dates.*.notes' => 'nullable|string|max:255',

            // Links
            'links' => 'nullable|array',
            'links.*.label' => 'nullable|string|in:' . implode(',', array_keys(PersonLink::LABELS)),
            'links.*.url' => 'nullable|url|max:500',

            // Attachments
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,vcf|max:10240',
            'attachment_types' => 'nullable|array',
            'attachment_types.*' => 'nullable|string|in:' . implode(',', array_keys(PersonAttachment::TYPES)),
        ]);
    }

    /**
     * Sync emails.
     */
    private function syncEmails(Person $person, array $emails): void
    {
        $person->emails()->delete();

        foreach ($emails as $emailData) {
            if (empty($emailData['email'])) {
                continue;
            }

            PersonEmail::create([
                'person_id' => $person->id,
                'email' => $emailData['email'],
                'label' => $emailData['label'] ?? 'personal',
                'is_primary' => $emailData['is_primary'] ?? false,
            ]);
        }
    }

    /**
     * Sync phones.
     */
    private function syncPhones(Person $person, array $phones): void
    {
        $person->phones()->delete();

        foreach ($phones as $phoneData) {
            if (empty($phoneData['phone'])) {
                continue;
            }

            PersonPhone::create([
                'person_id' => $person->id,
                'phone' => $phoneData['phone'],
                'country_code' => $phoneData['country_code'] ?? null,
                'label' => $phoneData['label'] ?? 'mobile',
                'is_primary' => $phoneData['is_primary'] ?? false,
            ]);
        }
    }

    /**
     * Sync addresses.
     */
    private function syncAddresses(Person $person, array $addresses): void
    {
        $person->addresses()->delete();

        foreach ($addresses as $addressData) {
            if (empty($addressData['street_address']) && empty($addressData['city'])) {
                continue;
            }

            PersonAddress::create([
                'person_id' => $person->id,
                'label' => $addressData['label'] ?? 'home',
                'street_address' => $addressData['street_address'] ?? null,
                'street_address_2' => $addressData['street_address_2'] ?? null,
                'city' => $addressData['city'] ?? null,
                'state' => $addressData['state'] ?? null,
                'zip_code' => $addressData['zip_code'] ?? null,
                'country' => $addressData['country'] ?? null,
                'is_primary' => $addressData['is_primary'] ?? false,
            ]);
        }
    }

    /**
     * Sync important dates.
     */
    private function syncImportantDates(Person $person, array $dates): void
    {
        $person->importantDates()->delete();

        foreach ($dates as $dateData) {
            if (empty($dateData['label']) || empty($dateData['date'])) {
                continue;
            }

            PersonImportantDate::create([
                'person_id' => $person->id,
                'label' => $dateData['label'],
                'date' => $dateData['date'],
                'recurring_yearly' => $dateData['recurring_yearly'] ?? false,
                'notes' => $dateData['notes'] ?? null,
            ]);
        }
    }

    /**
     * Sync links.
     */
    private function syncLinks(Person $person, array $links): void
    {
        $person->links()->delete();

        foreach ($links as $linkData) {
            if (empty($linkData['url'])) {
                continue;
            }

            PersonLink::create([
                'person_id' => $person->id,
                'label' => $linkData['label'] ?? 'website',
                'url' => $linkData['url'],
            ]);
        }
    }

    /**
     * Handle attachments upload.
     */
    private function handleAttachments(Person $person, array $files, array $types): void
    {
        foreach ($files as $index => $file) {
            $path = $file->store(
                'people/' . Auth::user()->tenant_id . '/' . $person->id,
                'do_spaces'
            );

            PersonAttachment::create([
                'tenant_id' => Auth::user()->tenant_id,
                'person_id' => $person->id,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_type' => $types[$index] ?? 'other',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Parse date inputs from m/d/Y format to Y-m-d format.
     */
    private function parseDateInputs(Request $request): void
    {
        // Parse birthday
        if ($request->filled('birthday')) {
            $birthday = $request->input('birthday');
            $parsed = \DateTime::createFromFormat('m/d/Y', $birthday);
            if ($parsed) {
                $request->merge(['birthday' => $parsed->format('Y-m-d')]);
            }
        }

        // Parse important dates from separate month/day/year fields
        if ($request->has('important_dates')) {
            $importantDates = $request->input('important_dates');
            foreach ($importantDates as $index => $dateData) {
                // Handle new format with separate month/day/year fields
                if (!empty($dateData['date_month']) && !empty($dateData['date_day']) && !empty($dateData['date_year'])) {
                    $month = str_pad($dateData['date_month'], 2, '0', STR_PAD_LEFT);
                    $day = str_pad($dateData['date_day'], 2, '0', STR_PAD_LEFT);
                    $year = $dateData['date_year'];
                    $importantDates[$index]['date'] = "{$year}-{$month}-{$day}";
                }
                // Handle legacy format (m/d/Y string)
                elseif (!empty($dateData['date'])) {
                    $parsed = \DateTime::createFromFormat('m/d/Y', $dateData['date']);
                    if ($parsed) {
                        $importantDates[$index]['date'] = $parsed->format('Y-m-d');
                    }
                }
            }
            $request->merge(['important_dates' => $importantDates]);
        }
    }
}
