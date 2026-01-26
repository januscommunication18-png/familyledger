<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MemberEducationDocument extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'member_education_documents';

    /**
     * Document type constants.
     */
    public const TYPE_REPORT_CARD = 'report_card';
    public const TYPE_TRANSCRIPT = 'transcript';
    public const TYPE_DIPLOMA = 'diploma';
    public const TYPE_CERTIFICATE = 'certificate';
    public const TYPE_AWARD = 'award';
    public const TYPE_IEP = 'iep';
    public const TYPE_504_PLAN = '504_plan';
    public const TYPE_ENROLLMENT = 'enrollment';
    public const TYPE_IMMUNIZATION = 'immunization';
    public const TYPE_OTHER = 'other';

    public const DOCUMENT_TYPES = [
        self::TYPE_REPORT_CARD => 'Report Card',
        self::TYPE_TRANSCRIPT => 'Transcript',
        self::TYPE_DIPLOMA => 'Diploma',
        self::TYPE_CERTIFICATE => 'Certificate',
        self::TYPE_AWARD => 'Award',
        self::TYPE_IEP => 'IEP (Individualized Education Program)',
        self::TYPE_504_PLAN => '504 Plan',
        self::TYPE_ENROLLMENT => 'Enrollment Document',
        self::TYPE_IMMUNIZATION => 'Immunization Record',
        self::TYPE_OTHER => 'Other',
    ];

    protected $fillable = [
        'tenant_id',
        'family_member_id',
        'school_record_id',
        'uploaded_by',
        'document_type',
        'title',
        'description',
        'school_year',
        'grade_level',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    /**
     * Get the family member this document belongs to.
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Get the school record this document belongs to.
     */
    public function schoolRecord(): BelongsTo
    {
        return $this->belongsTo(MemberSchoolInfo::class, 'school_record_id');
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the document type display name.
     */
    public function getDocumentTypeNameAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? 'Unknown';
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::disk('do_spaces')->temporaryUrl(
            $this->file_path,
            now()->addMinutes(30)
        );
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size ?? 0;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Get the icon class based on mime type.
     */
    public function getFileIconAttribute(): string
    {
        $mime = $this->mime_type ?? '';

        if (str_contains($mime, 'pdf')) {
            return 'icon-[tabler--file-type-pdf]';
        } elseif (str_contains($mime, 'image')) {
            return 'icon-[tabler--photo]';
        } elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) {
            return 'icon-[tabler--file-type-doc]';
        } elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) {
            return 'icon-[tabler--file-type-xls]';
        }

        return 'icon-[tabler--file]';
    }
}
