<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Need to drop foreign key first, then unique, then recreate
        DB::statement('ALTER TABLE insurance_policy_members DROP FOREIGN KEY insurance_policy_members_insurance_policy_id_foreign');
        DB::statement('ALTER TABLE insurance_policy_members DROP INDEX policy_member_unique');
        DB::statement('ALTER TABLE insurance_policy_members ADD UNIQUE policy_member_type_unique (insurance_policy_id, family_member_id, member_type)');
        DB::statement('ALTER TABLE insurance_policy_members ADD CONSTRAINT insurance_policy_members_insurance_policy_id_foreign FOREIGN KEY (insurance_policy_id) REFERENCES insurance_policies(id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE insurance_policy_members DROP FOREIGN KEY insurance_policy_members_insurance_policy_id_foreign');
        DB::statement('ALTER TABLE insurance_policy_members DROP INDEX policy_member_type_unique');
        DB::statement('ALTER TABLE insurance_policy_members ADD UNIQUE policy_member_unique (insurance_policy_id, family_member_id)');
        DB::statement('ALTER TABLE insurance_policy_members ADD CONSTRAINT insurance_policy_members_insurance_policy_id_foreign FOREIGN KEY (insurance_policy_id) REFERENCES insurance_policies(id) ON DELETE CASCADE');
    }
};
