<?php

namespace App\Models\Backoffice;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'backoffice_activity_logs';

    protected $fillable = [
        'admin_id',
        'action',
        'tenant_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the admin that owns this log.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get the tenant this log is for.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Common action types.
     */
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_VIEW_CLIENT = 'view_client';
    public const ACTION_REQUEST_VIEW_CODE = 'request_view_code';
    public const ACTION_VERIFY_VIEW_CODE = 'verify_view_code';
    public const ACTION_TOGGLE_CLIENT_STATUS = 'toggle_client_status';
    public const ACTION_UPDATE_PROFILE = 'update_profile';
    public const ACTION_CHANGE_PASSWORD = 'change_password';

    // Package Plan actions
    public const ACTION_CREATE_PLAN = 'create_package_plan';
    public const ACTION_UPDATE_PLAN = 'update_package_plan';
    public const ACTION_DELETE_PLAN = 'delete_package_plan';

    // Discount Code actions
    public const ACTION_CREATE_DISCOUNT = 'create_discount_code';
    public const ACTION_UPDATE_DISCOUNT = 'update_discount_code';
    public const ACTION_DELETE_DISCOUNT = 'delete_discount_code';
}
