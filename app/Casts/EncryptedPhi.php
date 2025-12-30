<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Cast for encrypting PHI (Protected Health Information) fields.
 * Uses AES-256-CBC encryption via Laravel's encryption service.
 *
 * PHI fields include: Name, DOB, SSN, Medical records, Insurance IDs
 */
class EncryptedPhi implements CastsAttributes
{
    /**
     * Cast the given value (decrypt when retrieving from database).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Log decryption failure for audit purposes
            \Log::warning('PHI decryption failed', [
                'model' => get_class($model),
                'key' => $key,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Prepare the given value for storage (encrypt when saving to database).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString($value);
    }
}
