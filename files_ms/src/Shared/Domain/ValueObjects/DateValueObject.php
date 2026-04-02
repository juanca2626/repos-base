<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\InvalidDateException;
use Src\Shared\Domain\ValueObject;

class DateValueObject extends ValueObject
{
    private string|null $date;

    public function __construct(string|null $date = 'now')
    {
        $this->date = $this->setDate($date);
    }

    public static function createFromFormat(string $format, string $dateTime): self
    {
        $dateTimeImmutable = \DateTimeImmutable::createFromFormat($format, $dateTime);

        if (!$dateTimeImmutable) {
            throw new InvalidDateException("The given date time is invalid", 400);
        }

        return new self(
            $dateTimeImmutable->format('Y-m-d')
        );
    }

    private function setDate(string|null $date): string|null
    {
        try {
            $date = ($date != 'now')
                ? $date
                : date('Y-m-d');

            if ($date == null) {
                return null;
            }

            $explodedDate = explode('-', $date);

            if (!checkdate(
                (int)$explodedDate[1],
                (int)$explodedDate[2],
                (int)$explodedDate[0]
            )) {
                throw new InvalidDateException('The given date time is invalid', 400);
            }

            return $date;
        } catch (\Exception $e) {
            throw new InvalidDateException("The given date time {$date} is invalid", 400);
        }
    }

    public function setValue(string|null $date = 'now'): string|null
    {
        return $this->date = $this->setDate($date);
    }

    /**
     * @throws \Exception
     */
    public static function parse(string $isoDatetime): self
    {
        $dateTimeImmutable = new \DateTimeImmutable($isoDatetime);

        return new self(
            $dateTimeImmutable->format('Y-m-d')
        );
    }

    public static function now(): self
    {
        $dateTimeImmutable = new \DateTimeImmutable;

        return new self(
            $dateTimeImmutable->format('Y-m-d')
        );
    }

    public function format(string $format): string
    {
        $dateTimeImmutable = new \DateTimeImmutable($this->date);

        return $dateTimeImmutable->format($format);
    }

    public function __toString(): string
    {
        return $this->date;
    }

    public function value(): string|null
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): string|null
    {
        return $this->date;
    }
}
