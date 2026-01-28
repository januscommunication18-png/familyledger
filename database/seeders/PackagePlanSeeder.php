<?php

namespace Database\Seeders;

use App\Models\PackagePlan;
use Illuminate\Database\Seeder;

class PackagePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Free Plan
        PackagePlan::firstOrCreate(
            ['type' => 'free'],
            [
                'name' => 'Free Plan',
                'type' => 'free',
                'description' => 'Get started with basic features for free.',
                'trial_period_days' => 0,
                'cost_per_month' => 0,
                'cost_per_year' => 0,
                'family_circles_limit' => 1,
                'family_members_limit' => 5,
                'document_storage_limit' => 5,
                'reminder_features' => ['email_reminder'],
                'is_active' => false, // Set to inactive by default
                'sort_order' => 1,
            ]
        );

        // Paid Plan
        PackagePlan::firstOrCreate(
            ['type' => 'paid', 'name' => 'Premium Plan'],
            [
                'name' => 'Premium Plan',
                'type' => 'paid',
                'description' => 'Unlock all features with unlimited access.',
                'trial_period_days' => 30,
                'cost_per_month' => 9.99,
                'cost_per_year' => 99.99,
                'family_circles_limit' => 0, // Unlimited
                'family_members_limit' => 0, // Unlimited
                'document_storage_limit' => 0, // Unlimited
                'reminder_features' => ['push_notification', 'email_reminder', 'sms_reminder'],
                'is_active' => false, // Set to inactive by default
                'sort_order' => 2,
            ]
        );

        $this->command->info('Default package plans created.');
    }
}
