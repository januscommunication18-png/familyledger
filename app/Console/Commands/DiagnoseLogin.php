<?php

namespace App\Console\Commands;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DiagnoseLogin extends Command
{
    protected $signature = 'diagnose:login {email}';
    protected $description = 'Diagnose login issues for a specific email';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        $emailHash = hash('sha256', $email);

        $this->info("Diagnosing login for: {$email}");
        $this->info("Email hash: {$emailHash}");
        $this->newLine();

        // 1. Check if user exists by email_hash
        $this->info('1. Checking user by email_hash...');
        $userByHash = DB::table('users')->where('email_hash', $emailHash)->first();

        if ($userByHash) {
            $this->info("   FOUND: User ID {$userByHash->id}");

            // Check email field
            $this->info("   Raw email field: " . substr($userByHash->email ?? 'NULL', 0, 50));
            $isEncrypted = str_starts_with($userByHash->email ?? '', 'eyJ');
            $this->info("   Email is encrypted: " . ($isEncrypted ? 'YES (PROBLEM!)' : 'NO (OK)'));

            // Check name field
            $this->info("   Raw name field: " . substr($userByHash->name ?? 'NULL', 0, 50));

            // Try to decrypt name
            if ($userByHash->name && str_starts_with($userByHash->name, 'eyJ')) {
                try {
                    $decrypted = Crypt::decryptString($userByHash->name);
                    $this->info("   Name decrypts to: {$decrypted}");
                } catch (\Exception $e) {
                    $this->error("   Name decrypt ERROR: {$e->getMessage()}");
                }
            }
        } else {
            $this->error('   NOT FOUND by email_hash');
        }

        $this->newLine();

        // 2. Try User::findByEmail
        $this->info('2. Testing User::findByEmail()...');
        try {
            $user = User::findByEmail($email);
            if ($user) {
                $this->info("   FOUND: User ID {$user->id}");
                $this->info("   Name: {$user->name}");
                $this->info("   Email: {$user->email}");
                $this->info("   Is active: " . ($user->is_active ? 'YES' : 'NO'));
                $this->info("   MFA enabled: " . ($user->mfa_enabled ? 'YES' : 'NO'));
                $this->info("   Has 2FA: " . ($user->hasTwoFactorEnabled() ? 'YES' : 'NO'));
                $this->info("   Has SMS MFA: " . ($user->hasSmsMfaEnabled() ? 'YES' : 'NO'));
            } else {
                $this->error('   NOT FOUND');
            }
        } catch (\Exception $e) {
            $this->error("   ERROR: {$e->getMessage()}");
        }

        $this->newLine();

        // 3. Check recent OTPs
        $this->info('3. Checking recent OTPs...');
        $otps = DB::table('otps')
            ->where('identifier_hash', hash('sha256', $email))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($otps->count() > 0) {
            foreach ($otps as $otp) {
                $status = $otp->verified_at ? 'VERIFIED' : ($otp->expires_at < now() ? 'EXPIRED' : 'PENDING');
                $this->info("   [{$status}] Type: {$otp->type}, Created: {$otp->created_at}");
            }
        } else {
            $this->warn('   No OTPs found');
        }

        $this->newLine();

        // 4. Check Laravel logs for errors
        $this->info('4. Check your Laravel log for errors:');
        $this->info('   tail -100 storage/logs/laravel.log | grep -i "otp\|login\|decrypt"');

        return self::SUCCESS;
    }
}
