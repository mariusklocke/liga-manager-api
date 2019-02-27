<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Infrastructure\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJwtSecretCommand extends Command
{
    /** @var ConfirmationBehaviour */
    private $confirmation;

    protected function configure()
    {
        $this->setName('app:generate-jwt-secret');
        $this->confirmation = new ConfirmationBehaviour($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $secretPath = Environment::get('JWT_SECRET_PATH') . '/secret.key';
        if (file_exists($secretPath)) {
            $shouldOverwrite = $this->confirmation->hasBeenConfirmed(
                $input, $output, 'JWT secret already exists, do you want to overwrite?'
            );
            if ($shouldOverwrite !== true) {
                $output->writeln('Aborted due to missing user confirmation');
                return 0;
            }
        }
        $this->generate($secretPath);
        return 0;
    }

    private function generate(string $targetPath): void
    {
        // Generate a new key pair
        $resource = openssl_pkey_new([
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        ]);

        // Extract the private key from $resource to $privateKey
        openssl_pkey_export($resource, $privateKey);

        $bytes = file_put_contents($targetPath, $privateKey);
        if ($bytes <= 0) {
            throw new \RuntimeException('Could not generate JWT secret file');
        }
    }
}