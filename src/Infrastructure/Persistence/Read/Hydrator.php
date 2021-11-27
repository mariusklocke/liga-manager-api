<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use DateTimeZone;

class Hydrator
{
    /** @var DateTimeZone|null */
    private $dateTimeZone = null;

    public function int($value): ?int
    {
        return $value !== null ? (int)$value : null;
    }

    public function string($value): ?string
    {
        return $value !== null ? (string)$value : null;
    }

    public function float($value): ?float
    {
        return $value !== null ? (float)$value : null;
    }

    public function dateTime($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = (new \DateTimeImmutable($value))
            ->setTimezone($this->getTimezone())
            ->format(DATE_ATOM);

        return str_replace('+00:00', 'Z', $string);
    }

    public function contact(array $row): ?array
    {
        $contact = [
            'email' => $row['contact_email'],
            'first_name' => $row['contact_first_name'],
            'last_name' => $row['contact_last_name'],
            'phone' => $row['contact_phone'],
        ];

        if (array_search(null, $contact, true) !== false) {
            return null;
        }

        return $contact;
    }

    private function getTimezone(): DateTimeZone
    {
        if ($this->dateTimeZone === null) {
            $this->dateTimeZone = new DateTimeZone('UTC');
        }

        return $this->dateTimeZone;
    }
}
