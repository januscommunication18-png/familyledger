<?php

namespace App\Jobs;

use App\Mail\DripEmail;
use App\Models\Backoffice\DripEmailLog;
use App\Models\Backoffice\DripEmailStep;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDripEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    protected DripEmailStep $step;
    protected string $email;
    protected ?int $userId;
    protected ?string $tenantId;

    public function __construct(
        DripEmailStep $step,
        string $email,
        ?int $userId = null,
        ?string $tenantId = null
    ) {
        $this->step = $step;
        $this->email = $email;
        $this->userId = $userId;
        $this->tenantId = $tenantId;
    }

    public function handle(): void
    {
        // Create log entry
        $log = DripEmailLog::create([
            'drip_campaign_id' => $this->step->drip_campaign_id,
            'drip_email_step_id' => $this->step->id,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'email' => $this->email,
            'status' => DripEmailLog::STATUS_PENDING,
        ]);

        try {
            $user = $this->userId ? User::find($this->userId) : null;
            $tenant = $this->tenantId ? Tenant::find($this->tenantId) : null;

            Mail::to($this->email)->send(new DripEmail(
                $this->step,
                $user,
                $tenant,
                $log
            ));

            $log->markAsSent();
        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());

            // Re-throw to allow Laravel to handle retries
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Find the log entry and mark as failed if not already
        $log = DripEmailLog::where('drip_campaign_id', $this->step->drip_campaign_id)
            ->where('drip_email_step_id', $this->step->id)
            ->where('email', $this->email)
            ->where('status', DripEmailLog::STATUS_PENDING)
            ->first();

        if ($log) {
            $log->markAsFailed('Job failed after all retries: ' . $exception->getMessage());
        }
    }
}
