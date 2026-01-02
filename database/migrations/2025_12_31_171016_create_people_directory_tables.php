<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Main people/contacts table
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Basic info
            $table->string('full_name');
            $table->string('nickname')->nullable();
            $table->string('relationship')->default('other'); // family, friend, neighbor, doctor, lawyer, school, contractor, babysitter, emergency, other
            $table->string('custom_relationship')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->date('birthday')->nullable();
            $table->text('notes')->nullable();
            $table->string('how_we_know')->nullable();
            $table->json('tags')->nullable();

            // Profile image
            $table->string('profile_image')->nullable();

            // Sync metadata
            $table->enum('source', ['manual', 'google', 'iphone', 'vcard'])->default('manual');
            $table->string('google_contact_id')->nullable();
            $table->string('ios_contact_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            // Visibility
            $table->enum('visibility', ['family', 'specific', 'private'])->default('family');
            $table->json('visible_to_members')->nullable(); // Array of family_member_ids when visibility is 'specific'

            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'full_name']);
            $table->index(['tenant_id', 'relationship']);
        });

        // Person emails (multi)
        Schema::create('person_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('email');
            $table->string('label')->nullable(); // personal, work, other
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Person phones (multi)
        Schema::create('person_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('phone');
            $table->string('country_code')->nullable();
            $table->string('label')->nullable(); // mobile, home, work, other
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Person addresses (multi)
        Schema::create('person_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('label')->nullable(); // home, work, other
            $table->string('street_address')->nullable();
            $table->string('street_address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Person important dates (custom dates with labels)
        Schema::create('person_important_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('label'); // Contract renewal, Appointment follow-up, Anniversary, etc.
            $table->date('date');
            $table->boolean('recurring_yearly')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Person attachments (business cards, documents)
        Schema::create('person_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('file_type')->nullable(); // business_card, vcard, document, other
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Person links (website, LinkedIn, social media)
        Schema::create('person_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('label'); // website, linkedin, twitter, facebook, other
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('person_links');
        Schema::dropIfExists('person_attachments');
        Schema::dropIfExists('person_important_dates');
        Schema::dropIfExists('person_addresses');
        Schema::dropIfExists('person_phones');
        Schema::dropIfExists('person_emails');
        Schema::dropIfExists('people');
        Schema::enableForeignKeyConstraints();
    }
};
