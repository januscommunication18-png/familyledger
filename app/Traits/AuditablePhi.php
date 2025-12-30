<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait to audit access and changes to PHI data.
 * Required for HIPAA compliance - all PHI access must be logged.
 */
trait AuditablePhi
{
    /**
     * Boot the trait to register model event listeners.
     */
    public static function bootAuditablePhi(): void
    {
        // Log when PHI record is created
        static::created(function ($model) {
            $model->logPhiAccess('created');
        });

        // Log when PHI record is updated
        static::updated(function ($model) {
            $model->logPhiAccess('updated', $model->getChanges());
        });

        // Log when PHI record is deleted
        static::deleted(function ($model) {
            $model->logPhiAccess('deleted');
        });

        // Log when PHI record is retrieved (optional - can be noisy)
        static::retrieved(function ($model) {
            if (config('app.audit_phi_reads', false)) {
                $model->logPhiAccess('viewed');
            }
        });
    }

    /**
     * Log PHI access event.
     */
    protected function logPhiAccess(string $action, array $changes = []): void
    {
        $user = Auth::user();

        $logData = [
            'action' => $action,
            'model' => get_class($this),
            'model_id' => $this->getKey(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'tenant_id' => tenant()?->id ?? null,
            'timestamp' => now()->toIso8601String(),
        ];

        // For updates, log which fields changed (not the values for security)
        if (!empty($changes)) {
            $logData['changed_fields'] = array_keys($changes);

            // Flag if any PHI fields were changed
            if (method_exists($this, 'getPhiFields')) {
                $phiFieldsChanged = array_intersect(
                    array_keys($changes),
                    $this->getPhiFields()
                );
                $logData['phi_fields_changed'] = !empty($phiFieldsChanged);
            }
        }

        Log::channel('phi_audit')->info("PHI {$action}", $logData);
    }

    /**
     * Log explicit PHI field access (for when specific fields are read).
     */
    public function logPhiFieldAccess(string $fieldName): void
    {
        $user = Auth::user();

        Log::channel('phi_audit')->info('PHI field accessed', [
            'field' => $fieldName,
            'model' => get_class($this),
            'model_id' => $this->getKey(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'ip_address' => request()?->ip(),
            'tenant_id' => tenant()?->id ?? null,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
