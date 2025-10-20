<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Network;

use InvalidArgumentException;
use Stringable;

class IpAddress implements Stringable
{
    public readonly string $bytes;
    public readonly int $version;

    /**
     * @param string $address
     */
    public function __construct(string $address)
    {
        $bytes = inet_pton($address);
        is_string($bytes) || throw new InvalidArgumentException(sprintf('Failed to parse IP address from "%s"', $address));
        $this->bytes = $bytes;
        $this->version = str_contains($address, ':') ? 6 : 4;
    }

    /**
     * Compare IP address to other IP address
     * 
     * @return int Returns -1 if $this < $other, 0 if $this === $other, 1 if $this > $other
     */
    public function compare(self $other): int
    {
        return $this->bytes <=> $other->bytes;
    }

    /**
     * Determine if an IP address is local
     * 
     * @return bool
     */
    public function isLocal(): bool
    {
        foreach (self::getLoopbackAddresses() as $loopback) {
            if ($this->version !== $loopback->version) {
                continue;
            }
            if ($this->compare($loopback) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if an IP address is private
     * 
     * @return bool
     */
    public function isPrivate(): bool
    {
        foreach (self::getPrivateRanges() as $range) {
            if ($this->version !== $range['start']->version) {
                continue;
            }
            if ($this->compare($range['start']) >= 0 && $this->compare($range['end']) <= 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Converts IP address to string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return inet_ntop($this->bytes) ?: '';
    }

    /**
     * @return self[] An array of IP addresses representing the loopback interface
     */
    private static function getLoopbackAddresses(): array
    {
        return [
            new self('127.0.0.1'),
            new self('::1')
        ];
    }

    /**
     * @return array An array of IP ranges considered private
     */
    private static function getPrivateRanges(): array
    {
        return [    
            ['start' => new self('10.0.0.0'), 'end' => new self('10.255.255.255')],
            ['start' => new self('172.16.0.0'), 'end' => new self('172.31.255.255')],
            ['start' => new self('192.168.0.0'), 'end' => new self('192.168.255.255')],
            ['start' => new self('fd00:0:0:0:0:0:0:0'), 'end' => new self('fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')]
        ];
    }
}