<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Webauthn\PublicKeyCredentialOptions;

interface OptionsStoreInterface
{
    public function save(string $id, PublicKeyCredentialOptions $options): void;

    public function get(string $id): ?PublicKeyCredentialOptions;
}
