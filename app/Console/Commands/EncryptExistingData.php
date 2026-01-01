<?php

namespace App\Console\Commands;

use App\Models\FamilyMember;
use App\Models\InsurancePolicy;
use App\Models\MemberAllergy;
use App\Models\MemberContact;
use App\Models\MemberHealthcareProvider;
use App\Models\MemberMedicalCondition;
use App\Models\MemberMedicalInfo;
use App\Models\MemberMedication;
use App\Models\Person;
use App\Models\PersonAddress;
use App\Models\PersonEmail;
use App\Models\PersonPhone;
use App\Models\TaxReturn;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EncryptExistingData extends Command
{
    protected $signature = 'data:encrypt
                            {--dry-run : Run without making changes}
                            {--force : Force encryption even if data appears encrypted}';

    protected $description = 'Encrypt existing plaintext PII/PHI data using AES-256 encryption';

    protected int $encrypted = 0;
    protected int $skipped = 0;
    protected int $errors = 0;

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Starting data encryption...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->newLine();

        // Encrypt Users
        $this->encryptUsers($dryRun, $force);

        // Encrypt Family Members
        $this->encryptFamilyMembers($dryRun, $force);

        // Encrypt Member Medical Info
        $this->encryptMemberMedicalInfo($dryRun, $force);

        // Encrypt Member Contacts
        $this->encryptMemberContacts($dryRun, $force);

        // Encrypt Member Healthcare Providers
        $this->encryptMemberHealthcareProviders($dryRun, $force);

        // Encrypt Member Allergies
        $this->encryptMemberAllergies($dryRun, $force);

        // Encrypt Member Medications
        $this->encryptMemberMedications($dryRun, $force);

        // Encrypt Member Medical Conditions
        $this->encryptMemberMedicalConditions($dryRun, $force);

        // Encrypt Insurance Policies
        $this->encryptInsurancePolicies($dryRun, $force);

        // Encrypt Tax Returns
        $this->encryptTaxReturns($dryRun, $force);

        // Encrypt People
        $this->encryptPeople($dryRun, $force);

        // Encrypt Person Emails
        $this->encryptPersonEmails($dryRun, $force);

        // Encrypt Person Phones
        $this->encryptPersonPhones($dryRun, $force);

        // Encrypt Person Addresses
        $this->encryptPersonAddresses($dryRun, $force);

        $this->newLine();
        $this->info("Encryption complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Encrypted', $this->encrypted],
                ['Skipped (already encrypted)', $this->skipped],
                ['Errors', $this->errors],
            ]
        );

        if ($this->errors > 0) {
            $this->error('Some records failed to encrypt. Check logs for details.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function encryptUsers(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Users...');

        // Use raw DB to get unencrypted data
        $users = DB::table('users')->get();
        $bar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            try {
                $needsUpdate = false;
                $updates = [];

                // Check if email needs email_hash
                if (empty($user->email_hash) && !empty($user->email)) {
                    // Check if email is already encrypted (starts with eyJ)
                    if (!$this->isEncrypted($user->email) || $force) {
                        $email = $this->isEncrypted($user->email)
                            ? Crypt::decryptString($user->email)
                            : $user->email;
                        $updates['email_hash'] = hash('sha256', strtolower(trim($email)));
                        $updates['email'] = Crypt::encryptString($email);
                        $needsUpdate = true;
                    }
                }

                // Encrypt other fields
                $fieldsToEncrypt = ['name', 'first_name', 'last_name', 'backup_email', 'phone', 'country_code'];
                foreach ($fieldsToEncrypt as $field) {
                    if (!empty($user->$field) && (!$this->isEncrypted($user->$field) || $force)) {
                        $value = $this->isEncrypted($user->$field)
                            ? Crypt::decryptString($user->$field)
                            : $user->$field;
                        $updates[$field] = Crypt::encryptString($value);
                        $needsUpdate = true;
                    }
                }

                if ($needsUpdate && !$dryRun) {
                    DB::table('users')->where('id', $user->id)->update($updates);
                    $this->encrypted++;
                } elseif (!$needsUpdate) {
                    $this->skipped++;
                }
            } catch (\Exception $e) {
                $this->errors++;
                Log::error('Failed to encrypt user', ['id' => $user->id, 'error' => $e->getMessage()]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function encryptFamilyMembers(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Family Members...');
        $this->encryptTable('family_members', [
            'first_name', 'last_name', 'email', 'phone', 'phone_country_code', 'father_name', 'mother_name'
        ], $dryRun, $force);
    }

    protected function encryptMemberMedicalInfo(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Member Medical Info...');
        $this->encryptTable('member_medical_info', [
            'medications', 'allergies', 'medical_conditions', 'primary_physician',
            'physician_phone', 'insurance_provider', 'insurance_policy_number',
            'insurance_group_number', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptMemberContacts(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Member Contacts...');
        $this->encryptTable('member_contacts', [
            'name', 'email', 'phone', 'phone_country_code', 'address', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptMemberHealthcareProviders(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Member Healthcare Providers...');
        $this->encryptTable('member_healthcare_providers', [
            'name', 'clinic_name', 'phone', 'phone_country_code', 'email', 'address', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptMemberAllergies(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Member Allergies...');
        $this->encryptTable('member_allergies', [
            'allergen_name', 'emergency_instructions', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptMemberMedications(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Member Medications...');
        $this->encryptTable('member_medications', [
            'name', 'dosage', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptMemberMedicalConditions(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Member Medical Conditions...');
        $this->encryptTable('member_medical_conditions', [
            'name', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptInsurancePolicies(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Insurance Policies...');
        $this->encryptTable('insurance_policies', [
            'provider_name', 'policy_number', 'group_number', 'plan_name',
            'agent_name', 'agent_phone', 'agent_email', 'claims_phone',
            'coverage_details', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptTaxReturns(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Tax Returns...');
        $this->encryptTable('tax_returns', [
            'cpa_name', 'cpa_phone', 'cpa_email', 'cpa_firm', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptPeople(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting People...');
        $this->encryptTable('people', [
            'full_name', 'nickname', 'company', 'job_title', 'how_we_know', 'notes'
        ], $dryRun, $force);
    }

    protected function encryptPersonEmails(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Person Emails...');
        $this->encryptTable('person_emails', ['email'], $dryRun, $force);
    }

    protected function encryptPersonPhones(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Person Phones...');
        $this->encryptTable('person_phones', ['phone'], $dryRun, $force);
    }

    protected function encryptPersonAddresses(bool $dryRun, bool $force): void
    {
        $this->info('Encrypting Person Addresses...');
        $this->encryptTable('person_addresses', [
            'street_address', 'street_address_2', 'city', 'state', 'zip_code', 'country'
        ], $dryRun, $force);
    }

    protected function encryptTable(string $table, array $fields, bool $dryRun, bool $force): void
    {
        $records = DB::table($table)->get();
        $bar = $this->output->createProgressBar($records->count());

        foreach ($records as $record) {
            try {
                $needsUpdate = false;
                $updates = [];

                foreach ($fields as $field) {
                    if (!empty($record->$field) && (!$this->isEncrypted($record->$field) || $force)) {
                        $value = $this->isEncrypted($record->$field)
                            ? Crypt::decryptString($record->$field)
                            : $record->$field;
                        $updates[$field] = Crypt::encryptString($value);
                        $needsUpdate = true;
                    }
                }

                if ($needsUpdate && !$dryRun) {
                    DB::table($table)->where('id', $record->id)->update($updates);
                    $this->encrypted++;
                } elseif (!$needsUpdate) {
                    $this->skipped++;
                }
            } catch (\Exception $e) {
                $this->errors++;
                Log::error("Failed to encrypt {$table} record", ['id' => $record->id, 'error' => $e->getMessage()]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Check if a value appears to be encrypted (base64 JSON from Laravel's Crypt).
     */
    protected function isEncrypted(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Laravel's encrypted values start with 'eyJ' (base64 encoded JSON)
        return str_starts_with($value, 'eyJ');
    }
}
