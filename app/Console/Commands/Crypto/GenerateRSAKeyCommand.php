<?php

namespace App\Console\Commands\Crypto;

use Illuminate\Console\Command;

class GenerateRSAKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto-gen:rsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA Public and Private Key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $openSSL = openssl_pkey_new();

        openssl_pkey_export($openSSL, $privateKey);

        // Get public key
        $publicSSL = openssl_pkey_get_details($openSSL);
        $publicKey = $publicSSL["key"];


        $this->info('Public Key');
        $this->info('');
        $this->line($publicKey);
        $this->info('');

        $this->info('Private Key');
        $this->info('');
        $this->line($privateKey);
        $this->info('');
    }
}
