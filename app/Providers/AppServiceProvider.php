<?php

namespace App\Providers;

use App\Models\FamilyMember;
use App\Models\MemberAllergy;
use App\Models\MemberContact;
use App\Models\MemberDocument;
use App\Models\MemberHealthcareProvider;
use App\Models\MemberMedicalCondition;
use App\Models\MemberMedicalInfo;
use App\Models\MemberMedication;
use App\Observers\FamilyMemberObserver;
use App\Observers\MemberAllergyObserver;
use App\Observers\MemberContactObserver;
use App\Observers\MemberDocumentObserver;
use App\Observers\MemberHealthcareProviderObserver;
use App\Observers\MemberMedicalConditionObserver;
use App\Observers\MemberMedicalInfoObserver;
use App\Observers\MemberMedicationObserver;
use App\Events\FamilyCircleCreated;
use App\Listeners\SendEventBasedDripEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for audit trail
        FamilyMember::observe(FamilyMemberObserver::class);
        MemberMedicalInfo::observe(MemberMedicalInfoObserver::class);
        MemberDocument::observe(MemberDocumentObserver::class);
        MemberContact::observe(MemberContactObserver::class);
        MemberAllergy::observe(MemberAllergyObserver::class);
        MemberHealthcareProvider::observe(MemberHealthcareProviderObserver::class);
        MemberMedication::observe(MemberMedicationObserver::class);
        MemberMedicalCondition::observe(MemberMedicalConditionObserver::class);

        // Register event listeners for drip campaigns
        Event::listen(FamilyCircleCreated::class, SendEventBasedDripEmail::class);
    }
}
