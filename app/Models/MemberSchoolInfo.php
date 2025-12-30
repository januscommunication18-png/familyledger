<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberSchoolInfo extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'member_school_info';

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'school_name',
        'grade_level',
        'student_id',
        'school_address',
        'school_phone',
        'school_email',
        'teacher_name',
        'teacher_email',
        'counselor_name',
        'counselor_email',
        'bus_number',
        'bus_pickup_time',
        'bus_dropoff_time',
        'notes',
    ];

    /**
     * Grade level options.
     */
    public const GRADE_LEVELS = [
        'pre-k' => 'Pre-Kindergarten',
        'kindergarten' => 'Kindergarten',
        '1st' => '1st Grade',
        '2nd' => '2nd Grade',
        '3rd' => '3rd Grade',
        '4th' => '4th Grade',
        '5th' => '5th Grade',
        '6th' => '6th Grade',
        '7th' => '7th Grade',
        '8th' => '8th Grade',
        '9th' => '9th Grade (Freshman)',
        '10th' => '10th Grade (Sophomore)',
        '11th' => '11th Grade (Junior)',
        '12th' => '12th Grade (Senior)',
        'college-freshman' => 'College Freshman',
        'college-sophomore' => 'College Sophomore',
        'college-junior' => 'College Junior',
        'college-senior' => 'College Senior',
        'graduate' => 'Graduate School',
    ];

    /**
     * Get the family member this school info belongs to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the grade level display name.
     */
    public function getGradeLevelNameAttribute(): ?string
    {
        if (!$this->grade_level) {
            return null;
        }
        return self::GRADE_LEVELS[$this->grade_level] ?? $this->grade_level;
    }

    /**
     * Check if bus info is available.
     */
    public function hasBusInfo(): bool
    {
        return !empty($this->bus_number);
    }

    /**
     * Check if teacher info is available.
     */
    public function hasTeacherInfo(): bool
    {
        return !empty($this->teacher_name);
    }
}
