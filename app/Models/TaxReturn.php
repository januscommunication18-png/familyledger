<?php

namespace App\Models;

use App\Casts\SafeEncrypted;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxReturn extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'taxpayer_id',
        'tax_year',
        'filing_status',
        'status',
        'tax_jurisdiction',
        'state_jurisdiction',
        'cpa_name',
        'cpa_phone',
        'cpa_email',
        'cpa_firm',
        'filing_date',
        'due_date',
        'refund_amount',
        'amount_owed',
        'federal_returns',
        'state_returns',
        'supporting_documents',
        'notes',
    ];

    protected $casts = [
        'tax_year' => 'integer',
        'filing_date' => 'date',
        'due_date' => 'date',
        'refund_amount' => 'decimal:2',
        'amount_owed' => 'decimal:2',
        'federal_returns' => 'array',
        'state_returns' => 'array',
        'supporting_documents' => 'array',
        // AES-256 encrypted PII fields (using SafeEncrypted for graceful error handling)
        'cpa_name' => SafeEncrypted::class,
        'cpa_phone' => SafeEncrypted::class,
        'cpa_email' => SafeEncrypted::class,
        'cpa_firm' => SafeEncrypted::class,
        'notes' => SafeEncrypted::class,
    ];

    /**
     * Filing statuses.
     */
    public const FILING_STATUSES = [
        'single' => 'Single',
        'married_joint' => 'Married Filing Jointly',
        'married_separate' => 'Married Filing Separately',
        'head_of_household' => 'Head of Household',
        'qualifying_widow' => 'Qualifying Widow(er)',
    ];

    /**
     * Return statuses.
     */
    public const STATUSES = [
        'not_started' => 'Not Started',
        'gathering_docs' => 'Gathering Documents',
        'in_progress' => 'In Progress',
        'under_review' => 'Under Review',
        'filed' => 'Filed',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'amended' => 'Amended',
    ];

    /**
     * Tax jurisdictions.
     */
    public const JURISDICTIONS = [
        'federal' => 'Federal Only',
        'state' => 'State Only',
        'both' => 'Federal & State',
    ];

    /**
     * US States.
     */
    public const US_STATES = [
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia',
    ];

    /**
     * Get the taxpayer (legacy single taxpayer).
     */
    public function taxpayer(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class, 'taxpayer_id');
    }

    /**
     * Get all taxpayers.
     */
    public function taxpayers(): BelongsToMany
    {
        return $this->belongsToMany(FamilyMember::class, 'tax_return_taxpayers')
            ->withTimestamps();
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'not_started' => 'neutral',
            'gathering_docs' => 'info',
            'in_progress' => 'warning',
            'under_review' => 'info',
            'filed' => 'primary',
            'accepted' => 'success',
            'rejected' => 'error',
            'amended' => 'warning',
            default => 'neutral',
        };
    }

    /**
     * Get total files count.
     */
    public function getFilesCount(): int
    {
        $count = 0;
        if ($this->federal_returns) {
            $count += count($this->federal_returns);
        }
        if ($this->state_returns) {
            $count += count($this->state_returns);
        }
        if ($this->supporting_documents) {
            $count += count($this->supporting_documents);
        }
        return $count;
    }

    /**
     * Check if return is filed.
     */
    public function isFiled(): bool
    {
        return in_array($this->status, ['filed', 'accepted', 'amended']);
    }
}
