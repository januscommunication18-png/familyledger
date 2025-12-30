<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service for sending SMS via Twilio.
 * Used for SMS MFA and phone verification.
 */
class SmsService
{
    protected string $sid;
    protected string $authToken;
    protected string $fromNumber;

    public function __construct()
    {
        $this->sid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.auth_token');
        $this->fromNumber = config('services.twilio.phone_number');
    }

    /**
     * Send an SMS message.
     */
    public function send(string $to, string $message): bool
    {
        if (empty($this->sid) || empty($this->authToken)) {
            Log::warning('Twilio credentials not configured');
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->sid, $this->authToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json", [
                    'To' => $this->formatPhoneNumber($to),
                    'From' => $this->fromNumber,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info('SMS sent successfully', ['to' => $this->maskPhoneNumber($to)]);
                return true;
            }

            Log::error('SMS send failed', [
                'to' => $this->maskPhoneNumber($to),
                'error' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('SMS send exception', [
                'to' => $this->maskPhoneNumber($to),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send OTP verification code.
     */
    public function sendOtp(string $to, string $code): bool
    {
        $message = "Your Family Ledger verification code is: {$code}. This code expires in 10 minutes.";
        return $this->send($to, $message);
    }

    /**
     * Send MFA verification code.
     */
    public function sendMfaCode(string $to, string $code): bool
    {
        $message = "Your Family Ledger login code is: {$code}. If you didn't request this, please ignore.";
        return $this->send($to, $message);
    }

    /**
     * Format phone number to E.164 format.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Add + if not present and starts with country code
        if (!str_starts_with($phone, '+')) {
            // Assume US number if no country code
            if (strlen($phone) === 10) {
                $phone = '+1' . $phone;
            } elseif (strlen($phone) === 11 && str_starts_with($phone, '1')) {
                $phone = '+' . $phone;
            }
        }

        return $phone;
    }

    /**
     * Mask phone number for logging.
     */
    protected function maskPhoneNumber(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($phone, -4);
    }
}
