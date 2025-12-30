<?php

namespace App\Traits;

use App\Casts\EncryptedPhi;

/**
 * Trait for models that contain PHI (Protected Health Information).
 * Automatically applies encryption to specified fields.
 *
 * Usage:
 * 1. Use this trait in your model
 * 2. Define $phiFields array with field names to encrypt
 *
 * Example:
 * protected array $phiFields = ['ssn', 'date_of_birth', 'medical_notes'];
 */
trait HasEncryptedPhi
{
    /**
     * Initialize the trait and set up encrypted casts.
     */
    public function initializeHasEncryptedPhi(): void
    {
        $phiFields = $this->phiFields ?? [];

        foreach ($phiFields as $field) {
            $this->casts[$field] = EncryptedPhi::class;
        }
    }

    /**
     * Get the PHI fields for this model.
     */
    public function getPhiFields(): array
    {
        return $this->phiFields ?? [];
    }

    /**
     * Check if a field contains PHI.
     */
    public function isPhiField(string $field): bool
    {
        return in_array($field, $this->getPhiFields());
    }

    /**
     * Get masked value of a PHI field for display purposes.
     * Shows only last 4 characters for SSN-like fields.
     */
    public function getMaskedPhi(string $field, int $visibleChars = 4): ?string
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        $length = strlen($value);

        if ($length <= $visibleChars) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - $visibleChars) . substr($value, -$visibleChars);
    }
}
