<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Generates the RSA keypair used to sign license grants.
 *   php artisan license:generate-keys
 */
class GenerateRsaKeys extends Command
{
    protected $signature = 'license:generate-keys {--bits=4096} {--force}';

    protected $description = 'Generate the RSA keypair for signing license activations.';

    public function handle(): int
    {
        $dir = storage_path('keys');
        $privatePath = config('license.rsa.private_key_path');
        $publicPath  = config('license.rsa.public_key_path');

        if (File::exists($privatePath) && ! $this->option('force')) {
            $this->error('Keys already exist. Use --force to overwrite (this invalidates existing licenses).');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($dir);

        $bits = (int) $this->option('bits');
        if ($bits < 4096) {
            $this->error('Refusing to generate keys weaker than RSA-4096.');

            return self::FAILURE;
        }

        $config = [
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            throw new \RuntimeException('openssl_pkey_new failed: ' . openssl_error_string());
        }

        $passphrase = config('license.rsa.passphrase');
        openssl_pkey_export($resource, $privateKey, $passphrase ?: null);

        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['key'] ?? null;

        if ($publicKey === null) {
            throw new \RuntimeException('Failed to extract public key.');
        }

        File::put($privatePath, $privateKey);
        File::put($publicPath, $publicKey);
        @chmod($privatePath, 0600);

        $this->info('RSA keypair generated:');
        $this->line("  Private: {$privatePath}");
        $this->line("  Public:  {$publicPath}");
        $this->warn('Distribute ONLY the public key with ERP clients. Never expose the private key.');

        return self::SUCCESS;
    }
}
