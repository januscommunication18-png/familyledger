<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Category mapping from old to new.
     * Old categories: home, school, health, finance, personal, work, errands, family
     * New categories: home_chores, bills, health, kids, car, pet_care, family_rituals, admin
     */
    private array $categoryMapping = [
        'home' => 'home_chores',
        'finance' => 'bills',
        'health' => 'health',
        'school' => 'kids',
        'personal' => 'admin',
        'work' => 'admin',
        'errands' => 'admin',
        'family' => 'family_rituals',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing categories to new values
        foreach ($this->categoryMapping as $oldCategory => $newCategory) {
            DB::table('todo_items')
                ->where('category', $oldCategory)
                ->update(['category' => $newCategory]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse mapping
        $reverseMapping = [
            'home_chores' => 'home',
            'bills' => 'finance',
            'health' => 'health',
            'kids' => 'school',
            'car' => 'personal',
            'pet_care' => 'personal',
            'family_rituals' => 'family',
            'admin' => 'personal',
        ];

        foreach ($reverseMapping as $newCategory => $oldCategory) {
            DB::table('todo_items')
                ->where('category', $newCategory)
                ->update(['category' => $oldCategory]);
        }
    }
};
