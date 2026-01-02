<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class FixEncryptedUserData extends Command
{
    protected $signature = 'users:fix-encryption {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Fix user encrypted fields that were stored with wrong encryption method';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN - No changes will be made');
        }

        $fieldsToFix = ['name', 'first_name', 'last_name', 'backup_email', 'phone', 'country_code'];
        $users = DB::table('users')->get();

        $fixed = 0;
        $errors = 0;

        foreach ($users as $user) {
            $updates = [];

            // Fix email field (should be plain text, not encrypted)
            if ($user->email && str_starts_with($user->email, 'eyJ')) {
                try {
                    $plainEmail = $this->decryptValue($user->email);
                    $updates['email'] = $plainEmail;
                    $this->line("User {$user->id} email: {$plainEmail}");
                } catch (\Exception $e) {
                    $this->error("User {$user->id} email ERROR: {$e->getMessage()}");
                    $errors++;
                }
            }

            // Fix encrypted fields
            foreach ($fieldsToFix as $field) {
                $value = $user->$field;
                if ($value && str_starts_with($value, 'eyJ')) {
                    try {
                        $plainValue = $this->decryptValue($value);
                        // Re-encrypt with encryptString for Laravel's encrypted cast
                        $updates[$field] = Crypt::encryptString($plainValue);
                        $this->line("User {$user->id} {$field}: {$plainValue}");
                    } catch (\Exception $e) {
                        $this->error("User {$user->id} {$field} ERROR: {$e->getMessage()}");
                        $errors++;
                    }
                }
            }

            if (!empty($updates)) {
                if (!$dryRun) {
                    DB::table('users')->where('id', $user->id)->update($updates);
                }
                $fixed++;
            }
        }

        $this->newLine();
        $this->info("Fixed {$fixed} users" . ($dryRun ? ' (dry run)' : ''));

        if ($errors > 0) {
            $this->warn("{$errors} errors occurred");
        }

        return self::SUCCESS;
    }

    private function decryptValue(string $value): string
    {
        // Try decrypt (with unserialize) first - for values encrypted with encrypt()
        try {
            $decrypted = decrypt($value);
            // If result looks like serialized string, unserialize it
            if (is_string($decrypted) && preg_match('/^s:\d+:".*";$/', $decrypted)) {
                return unserialize($decrypted);
            }
            return $decrypted;
        } catch (\Exception $e) {
            // Fall back to decryptString (no unserialize)
            return Crypt::decryptString($value);
        }
    }
}
