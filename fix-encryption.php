<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

echo "Starting encryption fix...\n\n";

function needsEncryption($value) {
    if (empty($value)) return false;
    return !str_starts_with($value, 'eyJ');
}

function safeEncrypt($value) {
    if (empty($value)) return null;
    if (!needsEncryption($value)) return $value;
    return Crypt::encryptString($value);
}

// Fix family_members table
echo "Fixing family_members table...\n";
$members = DB::table('family_members')->get();
$fixed = 0;

foreach ($members as $member) {
    $updates = [];

    if (needsEncryption($member->first_name)) $updates['first_name'] = safeEncrypt($member->first_name);
    if (needsEncryption($member->last_name)) $updates['last_name'] = safeEncrypt($member->last_name);
    if (needsEncryption($member->email)) $updates['email'] = safeEncrypt($member->email);
    if (needsEncryption($member->phone ?? null)) $updates['phone'] = safeEncrypt($member->phone);
    if (needsEncryption($member->phone_country_code ?? null)) $updates['phone_country_code'] = safeEncrypt($member->phone_country_code);
    if (needsEncryption($member->father_name ?? null)) $updates['father_name'] = safeEncrypt($member->father_name);
    if (needsEncryption($member->mother_name ?? null)) $updates['mother_name'] = safeEncrypt($member->mother_name);

    if (!empty($updates)) {
        DB::table('family_members')->where('id', $member->id)->update($updates);
        $fixed++;
        echo "Fixed family_member ID: {$member->id}\n";
    }
}
echo "✅ Fixed {$fixed} family_members records\n\n";

// Fix member_contacts table
echo "Fixing member_contacts table...\n";
$contacts = DB::table('member_contacts')->get();
$fixed = 0;

foreach ($contacts as $contact) {
    $updates = [];

    if (needsEncryption($contact->name ?? null)) $updates['name'] = safeEncrypt($contact->name);
    if (needsEncryption($contact->phone ?? null)) $updates['phone'] = safeEncrypt($contact->phone);
    if (needsEncryption($contact->email ?? null)) $updates['email'] = safeEncrypt($contact->email);
    if (needsEncryption($contact->address ?? null)) $updates['address'] = safeEncrypt($contact->address);

    if (!empty($updates)) {
        DB::table('member_contacts')->where('id', $contact->id)->update($updates);
        $fixed++;
        echo "Fixed member_contact ID: {$contact->id}\n";
    }
}
echo "✅ Fixed {$fixed} member_contacts records\n\n";

// Fix member_medical_info table
echo "Fixing member_medical_info table...\n";
$medicals = DB::table('member_medical_info')->get();
$fixed = 0;

foreach ($medicals as $medical) {
    $updates = [];

    if (needsEncryption($medical->blood_type ?? null)) $updates['blood_type'] = safeEncrypt($medical->blood_type);
    if (needsEncryption($medical->primary_physician ?? null)) $updates['primary_physician'] = safeEncrypt($medical->primary_physician);
    if (needsEncryption($medical->physician_phone ?? null)) $updates['physician_phone'] = safeEncrypt($medical->physician_phone);
    if (needsEncryption($medical->medical_notes ?? null)) $updates['medical_notes'] = safeEncrypt($medical->medical_notes);

    if (!empty($updates)) {
        DB::table('member_medical_info')->where('id', $medical->id)->update($updates);
        $fixed++;
        echo "Fixed member_medical_info ID: {$medical->id}\n";
    }
}
echo "✅ Fixed {$fixed} member_medical_info records\n\n";

// Fix people table
echo "Fixing people table...\n";
$people = DB::table('people')->get();
$fixed = 0;

foreach ($people as $person) {
    $updates = [];

    if (needsEncryption($person->first_name ?? null)) $updates['first_name'] = safeEncrypt($person->first_name);
    if (needsEncryption($person->last_name ?? null)) $updates['last_name'] = safeEncrypt($person->last_name);
    if (needsEncryption($person->company ?? null)) $updates['company'] = safeEncrypt($person->company);
    if (needsEncryption($person->notes ?? null)) $updates['notes'] = safeEncrypt($person->notes);

    if (!empty($updates)) {
        DB::table('people')->where('id', $person->id)->update($updates);
        $fixed++;
        echo "Fixed person ID: {$person->id}\n";
    }
}
echo "✅ Fixed {$fixed} people records\n\n";

echo "========================================\n";
echo "Encryption fix completed!\n";
echo "========================================\n";
