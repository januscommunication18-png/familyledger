<?php

namespace App\Http\Controllers;

use App\Models\Backoffice\DataAccessRequest;
use App\Models\Backoffice\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DataAccessController extends Controller
{
    /**
     * Show the access request details for client to approve/deny.
     */
    public function show(string $token): View
    {
        $request = DataAccessRequest::where('token', $token)->firstOrFail();

        // Check if already actioned
        if ($request->status !== DataAccessRequest::STATUS_PENDING) {
            return view('data-access.already-actioned', [
                'request' => $request,
            ]);
        }

        // Check if expired
        if ($request->isExpired()) {
            $request->markExpired();
            return view('data-access.expired', [
                'request' => $request,
            ]);
        }

        $request->load(['admin', 'tenant']);

        // Get the account owner and mask their email for the hint
        $owner = User::where('tenant_id', $request->tenant_id)
            ->orderBy('created_at')
            ->first();

        $maskedEmail = null;
        if ($owner && $owner->email) {
            $maskedEmail = $this->maskEmail($owner->email);
        }

        return view('data-access.show', [
            'request' => $request,
            'maskedEmail' => $maskedEmail,
        ]);
    }

    /**
     * Mask an email address for display (e.g., "test@example.com" -> "t***@example.com")
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***.***';
        }

        $local = $parts[0];
        $domain = $parts[1];

        // Mask local part: show first char + ***
        if (strlen($local) > 1) {
            $maskedLocal = substr($local, 0, 1) . str_repeat('*', min(strlen($local) - 1, 5));
        } else {
            $maskedLocal = '*';
        }

        // Mask domain: show first char of domain name + *** + .tld
        $domainParts = explode('.', $domain);
        if (count($domainParts) >= 2) {
            $domainName = $domainParts[0];
            $tld = implode('.', array_slice($domainParts, 1));
            $maskedDomain = substr($domainName, 0, 1) . str_repeat('*', min(strlen($domainName) - 1, 4)) . '.' . $tld;
        } else {
            $maskedDomain = '***.' . $domain;
        }

        return $maskedLocal . '@' . $maskedDomain;
    }

    /**
     * Approve the access request.
     */
    public function approve(Request $request, string $token): RedirectResponse
    {
        $accessRequest = DataAccessRequest::where('token', $token)->firstOrFail();

        // Validate
        if (!$accessRequest->isPending()) {
            return redirect()->route('data-access.show', $token)
                ->with('error', 'This request has already been processed or has expired.');
        }

        $request->validate([
            'email' => 'required|email',
            'access_hours' => 'required|integer|min:1|max:24',
        ]);

        // Get the account owner to verify email
        $owner = User::where('tenant_id', $accessRequest->tenant_id)
            ->orderBy('created_at')
            ->first();

        if (!$owner) {
            return redirect()->route('data-access.show', $token)
                ->with('error', 'Could not verify account ownership.');
        }

        // Verify the email matches the account owner's email (case-insensitive)
        if (strtolower($request->email) !== strtolower($owner->email)) {
            return redirect()->route('data-access.show', $token)
                ->with('error', 'The email address does not match the account owner\'s email. Please enter the correct email to verify your identity.');
        }

        // Approve the request
        $accessRequest->approve($request->email, $request->access_hours);

        // Log the activity
        if ($accessRequest->admin) {
            $accessRequest->admin->logActivity(
                ActivityLog::ACTION_VIEW_CLIENT,
                $accessRequest->tenant_id,
                'Data access approved by ' . $request->email . ' for ' . $request->access_hours . ' hours'
            );
        }

        return redirect()->route('data-access.approved', $token);
    }

    /**
     * Deny the access request.
     */
    public function deny(Request $request, string $token): RedirectResponse
    {
        $accessRequest = DataAccessRequest::where('token', $token)->firstOrFail();

        // Validate
        if (!$accessRequest->isPending()) {
            return redirect()->route('data-access.show', $token)
                ->with('error', 'This request has already been processed or has expired.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Deny the request
        $accessRequest->deny($request->reason);

        // Log the activity
        if ($accessRequest->admin) {
            $accessRequest->admin->logActivity(
                ActivityLog::ACTION_VIEW_CLIENT,
                $accessRequest->tenant_id,
                'Data access denied. Reason: ' . ($request->reason ?: 'No reason provided')
            );
        }

        return redirect()->route('data-access.denied', $token);
    }

    /**
     * Show approval confirmation page.
     */
    public function approved(string $token): View
    {
        $request = DataAccessRequest::where('token', $token)->firstOrFail();

        return view('data-access.approved', [
            'request' => $request,
        ]);
    }

    /**
     * Show denial confirmation page.
     */
    public function denied(string $token): View
    {
        $request = DataAccessRequest::where('token', $token)->firstOrFail();

        return view('data-access.denied', [
            'request' => $request,
        ]);
    }
}
