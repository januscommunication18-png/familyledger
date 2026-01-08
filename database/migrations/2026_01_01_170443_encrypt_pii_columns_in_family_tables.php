<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert PII columns to TEXT to accommodate AES-256 encrypted data.
     */
    public function up(): void
    {
        // Family Members table
        Schema::table('family_members', function (Blueprint $table) {
            $table->text('first_name')->nullable()->change();
            $table->text('last_name')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->text('phone_country_code')->nullable()->change();
            $table->text('father_name')->nullable()->change();
            $table->text('mother_name')->nullable()->change();
        });

        // Member Medical Info table
        Schema::table('member_medical_info', function (Blueprint $table) {
            $table->text('medications')->nullable()->change();
            $table->text('allergies')->nullable()->change();
            $table->text('medical_conditions')->nullable()->change();
            $table->text('primary_physician')->nullable()->change();
            $table->text('physician_phone')->nullable()->change();
            $table->text('insurance_provider')->nullable()->change();
            $table->text('insurance_policy_number')->nullable()->change();
            $table->text('insurance_group_number')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Member Contacts table
        Schema::table('member_contacts', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->text('phone_country_code')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Member Healthcare Providers table
        Schema::table('member_healthcare_providers', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
            $table->text('clinic_name')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->text('phone_country_code')->nullable()->change();
            $table->text('email')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Member Documents table
        Schema::table('member_documents', function (Blueprint $table) {
            $table->text('document_number')->nullable()->change();
            $table->text('details')->nullable()->change();
        });

        // Member Allergies table
        Schema::table('member_allergies', function (Blueprint $table) {
            $table->text('allergen_name')->nullable()->change();
            $table->text('emergency_instructions')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Member Medications table
        Schema::table('member_medications', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
            $table->text('dosage')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Member Medical Conditions table
        Schema::table('member_medical_conditions', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Insurance Policies table
        if (Schema::hasTable('insurance_policies')) {
            Schema::table('insurance_policies', function (Blueprint $table) {
                $table->text('provider_name')->nullable()->change();
                $table->text('policy_number')->nullable()->change();
                $table->text('group_number')->nullable()->change();
                $table->text('plan_name')->nullable()->change();
                $table->text('agent_name')->nullable()->change();
                $table->text('agent_phone')->nullable()->change();
                $table->text('agent_email')->nullable()->change();
                $table->text('claims_phone')->nullable()->change();
                $table->text('coverage_details')->nullable()->change();
                $table->text('notes')->nullable()->change();
            });
        }

        // Tax Returns table
        if (Schema::hasTable('tax_returns')) {
            Schema::table('tax_returns', function (Blueprint $table) {
                $table->text('cpa_name')->nullable()->change();
                $table->text('cpa_phone')->nullable()->change();
                $table->text('cpa_email')->nullable()->change();
                $table->text('cpa_firm')->nullable()->change();
                $table->text('notes')->nullable()->change();
            });
        }

        // People (Personal CRM) table - drop index first if exists
        $indexExists = collect(DB::select("SHOW INDEX FROM people WHERE Key_name = 'people_tenant_id_full_name_index'"))->isNotEmpty();
        if ($indexExists) {
            Schema::table('people', function (Blueprint $table) {
                $table->dropIndex('people_tenant_id_full_name_index');
            });
        }

        Schema::table('people', function (Blueprint $table) {
            $table->text('full_name')->nullable()->change();
            $table->text('nickname')->nullable()->change();
            $table->text('company')->nullable()->change();
            $table->text('job_title')->nullable()->change();
            $table->text('how_we_know')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Person Emails table
        Schema::table('person_emails', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
        });

        // Person Phones table
        Schema::table('person_phones', function (Blueprint $table) {
            $table->text('phone')->nullable()->change();
        });

        // Person Addresses table
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->text('street_address')->nullable()->change();
            $table->text('street_address_2')->nullable()->change();
            $table->text('city')->nullable()->change();
            $table->text('state')->nullable()->change();
            $table->text('zip_code')->nullable()->change();
            $table->text('country')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert family_members
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('phone_country_code')->nullable()->change();
            $table->string('father_name')->nullable()->change();
            $table->string('mother_name')->nullable()->change();
        });

        // Revert member_medical_info
        Schema::table('member_medical_info', function (Blueprint $table) {
            $table->text('medications')->nullable()->change();
            $table->text('allergies')->nullable()->change();
            $table->text('medical_conditions')->nullable()->change();
            $table->string('primary_physician')->nullable()->change();
            $table->string('physician_phone')->nullable()->change();
            $table->string('insurance_provider')->nullable()->change();
            $table->string('insurance_policy_number')->nullable()->change();
            $table->string('insurance_group_number')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert member_contacts
        Schema::table('member_contacts', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('phone_country_code')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert member_healthcare_providers
        Schema::table('member_healthcare_providers', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('clinic_name')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('phone_country_code')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert member_documents
        Schema::table('member_documents', function (Blueprint $table) {
            $table->string('document_number')->nullable()->change();
            $table->text('details')->nullable()->change();
        });

        // Revert member_allergies
        Schema::table('member_allergies', function (Blueprint $table) {
            $table->string('allergen_name')->nullable()->change();
            $table->text('emergency_instructions')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert member_medications
        Schema::table('member_medications', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('dosage')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert member_medical_conditions
        Schema::table('member_medical_conditions', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert insurance_policies
        if (Schema::hasTable('insurance_policies')) {
            Schema::table('insurance_policies', function (Blueprint $table) {
                $table->string('provider_name')->nullable()->change();
                $table->string('policy_number')->nullable()->change();
                $table->string('group_number')->nullable()->change();
                $table->string('plan_name')->nullable()->change();
                $table->string('agent_name')->nullable()->change();
                $table->string('agent_phone')->nullable()->change();
                $table->string('agent_email')->nullable()->change();
                $table->string('claims_phone')->nullable()->change();
                $table->text('coverage_details')->nullable()->change();
                $table->text('notes')->nullable()->change();
            });
        }

        // Revert tax_returns
        if (Schema::hasTable('tax_returns')) {
            Schema::table('tax_returns', function (Blueprint $table) {
                $table->string('cpa_name')->nullable()->change();
                $table->string('cpa_phone')->nullable()->change();
                $table->string('cpa_email')->nullable()->change();
                $table->string('cpa_firm')->nullable()->change();
                $table->text('notes')->nullable()->change();
            });
        }

        // Revert people
        Schema::table('people', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
            $table->string('nickname')->nullable()->change();
            $table->string('company')->nullable()->change();
            $table->string('job_title')->nullable()->change();
            $table->text('how_we_know')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });

        // Revert person_emails
        Schema::table('person_emails', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Revert person_phones
        Schema::table('person_phones', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });

        // Revert person_addresses
        Schema::table('person_addresses', function (Blueprint $table) {
            $table->string('street_address')->nullable()->change();
            $table->string('street_address_2')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('zip_code')->nullable()->change();
            $table->string('country')->nullable()->change();
        });
    }
};
