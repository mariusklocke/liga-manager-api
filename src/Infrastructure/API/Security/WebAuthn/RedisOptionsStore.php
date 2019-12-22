<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Redis;
use Webauthn\PublicKeyCredentialOptions;

class RedisOptionsStore implements OptionsStoreInterface
{
    /** @var Redis */
    private $redis;

    /** @var int */
    private $ttl;

    /**
     * @param Redis $redis
     * @param int $ttl
     */
    public function __construct(Redis $redis, int $ttl = 60)
    {
        $this->redis = $redis;
        $this->ttl = $ttl;
    }

    public function save(string $id, PublicKeyCredentialOptions $options): void
    {
        $this->redis->setex($this->buildKey($id), $this->ttl, serialize($options));
    }

    public function get(string $id): ?PublicKeyCredentialOptions
    {
        $key = $this->buildKey($id);
        $data = $this->redis->get($key);
        if ($data === false) {
            return null;
        }

        return unserialize($data);
    }

    private function buildKey(string $id): string
    {
        return sha1($id);
    }
}