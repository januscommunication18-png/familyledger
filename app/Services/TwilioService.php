<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected ?Client $client = null;
    protected string $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');

        if ($sid && $token) {
            $this->client = new Client($sid, $token);
        }
    }

    /**
     * Send SMS verification code
     */
    public function sendVerificationCode(string $phoneNumber, string $code): bool
    {
        if (!$this->client) {
            Log::warning('Twilio client not configured');
            return false;
        }

        try {
            $message = $this->client->messages->create(
                $phoneNumber,
                [
                    'from' => $this->from,
                    'body' => "Your Family Ledger verification code is: {$code}. This code expires in 10 minutes.",
                ]
            );

            Log::info('SMS verification code sent', [
                'phone' => $this->maskPhone($phoneNumber),
                'from' => $this->from,
                'message_sid' => $message->sid,
                'status' => $message->status,
            ]);
            return true;
        } catch (TwilioException $e) {
            Log::error('Twilio SMS failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'phone' => $this->maskPhone($phoneNumber),
                'from' => $this->from,
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Twilio SMS unexpected error', [
                'error' => $e->getMessage(),
                'phone' => $this->maskPhone($phoneNumber),
            ]);
            return false;
        }
    }

    /**
     * Generate a random verification code
     */
    public function generateCode(int $length = 6): string
    {
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Generate recovery backup codes
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    /**
     * Mask phone number for logging
     */
    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) < 6) {
            return '***';
        }
        return substr($phone, 0, 3) . '****' . substr($phone, -2);
    }

    /**
     * Check if Twilio is fully configured
     */
    public function isConfigured(): bool
    {
        return $this->client !== null && !empty($this->from);
    }
}
