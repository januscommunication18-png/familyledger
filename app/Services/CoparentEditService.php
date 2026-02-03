<?php

namespace App\Services;

use App\Events\PendingEditCreated;
use App\Models\Collaborator;
use App\Models\FamilyMember;
use App\Models\PendingCoparentEdit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CoparentEditService
{
    protected ?User $user;
    protected ?FamilyMember $familyMember = null;
    protected ?Collaborator $collaborator = null;
    protected bool $isCoparent = false;
    protected bool $isOwner = false;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
     * Initialize the service for a specific family member.
     */
    public static function forMember(FamilyMember $member, ?User $user = null): self
    {
        $service = new self($user);
        $service->familyMember = $member;
        $service->determineUserRole();
        return $service;
    }

    /**
     * Determine if the current user is an owner or coparent.
     */
    protected function determineUserRole(): void
    {
        if (!$this->user || !$this->familyMember) {
            return;
        }

        // Check if user is the owner (same tenant)
        if ($this->familyMember->tenant_id === $this->user->tenant_id) {
            $this->isOwner = true;
            $this->isCoparent = false;
            return;
        }

        // Check if user is a coparent with access
        $this->collaborator = Collaborator::where('user_id', $this->user->id)
            ->where('tenant_id', $this->familyMember->tenant_id)
            ->where('coparenting_enabled', true)
            ->where('is_active', true)
            ->whereHas('coparentChildren', function ($query) {
                $query->where('family_member_id', $this->familyMember->id);
            })
            ->first();

        if ($this->collaborator) {
            $this->isCoparent = true;
            $this->isOwner = false;
        }
    }

    /**
     * Check if the current user is a coparent (edits need approval).
     */
    public function needsApproval(): bool
    {
        return $this->isCoparent && !$this->isOwner;
    }

    /**
     * Check if user is the owner.
     */
    public function isOwner(): bool
    {
        return $this->isOwner;
    }

    /**
     * Check if user is a coparent.
     */
    public function isCoparent(): bool
    {
        return $this->isCoparent;
    }

    /**
     * Handle an update - either apply directly (owner) or create pending edit (coparent).
     *
     * @param Model $model The model being updated
     * @param string $field The field being changed
     * @param mixed $newValue The new value
     * @param string|null $notes Optional notes from the requester
     * @return array Result with success, pending flag, and message
     */
    public function handleUpdate(Model $model, string $field, $newValue, ?string $notes = null): array
    {
        $oldValue = $model->getOriginal($field) ?? $model->$field;

        // Convert to string for storage
        $oldValueStr = is_array($oldValue) ? json_encode($oldValue) : (string) $oldValue;
        $newValueStr = is_array($newValue) ? json_encode($newValue) : (string) $newValue;

        if ($this->isOwner) {
            // Owner can edit directly
            $model->update([$field => $newValue]);
            return [
                'success' => true,
                'pending' => false,
                'message' => 'Updated successfully',
            ];
        }

        if ($this->isCoparent) {
            // Create pending edit
            $pendingEdit = $this->createPendingEdit(
                $model,
                $field,
                $oldValueStr,
                $newValueStr,
                $notes
            );

            // Broadcast notification to owner
            $this->notifyOwner($pendingEdit);

            return [
                'success' => true,
                'pending' => true,
                'message' => 'Your edit request has been submitted for approval',
                'pending_edit_id' => $pendingEdit->id,
            ];
        }

        return [
            'success' => false,
            'pending' => false,
            'message' => 'You do not have permission to edit this record',
        ];
    }

    /**
     * Handle record creation - either create directly (owner) or create pending edit (coparent).
     *
     * @param string $modelClass The model class to create
     * @param array $data The data for the new record
     * @param string|null $notes Optional notes from the requester
     * @return array Result with success, pending flag, and message
     */
    public function handleCreate(string $modelClass, array $data, ?string $notes = null): array
    {
        if ($this->isOwner) {
            $record = $modelClass::create($data);
            return [
                'success' => true,
                'pending' => false,
                'record' => $record,
                'message' => 'Created successfully',
            ];
        }

        if ($this->isCoparent) {
            $pendingEdit = PendingCoparentEdit::create([
                'tenant_id' => $this->familyMember->tenant_id,
                'editable_type' => $modelClass,
                'editable_id' => null, // Will be set after approval
                'family_member_id' => $this->familyMember->id,
                'field_name' => 'new_record',
                'old_value' => null,
                'new_value' => null,
                'is_create' => true,
                'create_data' => $data,
                'requested_by' => $this->user->id,
                'ip_address' => request()->ip(),
                'request_notes' => $notes,
            ]);

            $this->notifyOwner($pendingEdit);

            return [
                'success' => true,
                'pending' => true,
                'message' => 'Your request to add this record has been submitted for approval',
                'pending_edit_id' => $pendingEdit->id,
            ];
        }

        return [
            'success' => false,
            'pending' => false,
            'message' => 'You do not have permission to create this record',
        ];
    }

    /**
     * Handle record deletion - either delete directly (owner) or create pending edit (coparent).
     *
     * @param Model $model The model to delete
     * @param string|null $notes Optional notes from the requester
     * @return array Result with success, pending flag, and message
     */
    public function handleDelete(Model $model, ?string $notes = null): array
    {
        if ($this->isOwner) {
            $model->delete();
            return [
                'success' => true,
                'pending' => false,
                'message' => 'Deleted successfully',
            ];
        }

        if ($this->isCoparent) {
            $pendingEdit = PendingCoparentEdit::create([
                'tenant_id' => $this->familyMember->tenant_id,
                'editable_type' => get_class($model),
                'editable_id' => $model->id,
                'family_member_id' => $this->familyMember->id,
                'field_name' => 'delete_record',
                'old_value' => json_encode($model->toArray()),
                'new_value' => null,
                'is_delete' => true,
                'requested_by' => $this->user->id,
                'ip_address' => request()->ip(),
                'request_notes' => $notes,
            ]);

            $this->notifyOwner($pendingEdit);

            return [
                'success' => true,
                'pending' => true,
                'message' => 'Your request to delete this record has been submitted for approval',
                'pending_edit_id' => $pendingEdit->id,
            ];
        }

        return [
            'success' => false,
            'pending' => false,
            'message' => 'You do not have permission to delete this record',
        ];
    }

    /**
     * Create a pending edit record.
     */
    protected function createPendingEdit(
        Model $model,
        string $field,
        ?string $oldValue,
        ?string $newValue,
        ?string $notes
    ): PendingCoparentEdit {
        return PendingCoparentEdit::create([
            'tenant_id' => $this->familyMember->tenant_id,
            'editable_type' => get_class($model),
            'editable_id' => $model->id,
            'family_member_id' => $this->familyMember->id,
            'field_name' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'requested_by' => $this->user->id,
            'ip_address' => request()->ip(),
            'request_notes' => $notes,
        ]);
    }

    /**
     * Notify the owner about the pending edit via broadcasting.
     */
    protected function notifyOwner(PendingCoparentEdit $pendingEdit): void
    {
        // Get the tenant owner
        $tenant = $this->familyMember->familyCircle?->tenant ?? null;
        if (!$tenant) {
            // Try to get tenant directly
            $tenant = \App\Models\Tenant::find($this->familyMember->tenant_id);
        }

        if (!$tenant || !$tenant->user_id) {
            return;
        }

        $ownerId = $tenant->user_id;

        try {
            event(new PendingEditCreated($pendingEdit, $ownerId));
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Log::warning('Failed to broadcast pending edit notification: ' . $e->getMessage());
        }
    }

    /**
     * Get pending edits count for the current user as owner.
     */
    public static function getPendingCountForOwner(User $user): int
    {
        return PendingCoparentEdit::where('tenant_id', $user->tenant_id)
            ->pending()
            ->count();
    }

    /**
     * Get all pending edits for a tenant owner.
     */
    public static function getPendingEditsForOwner(User $user)
    {
        return PendingCoparentEdit::where('tenant_id', $user->tenant_id)
            ->pending()
            ->with(['familyMember', 'requester'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
