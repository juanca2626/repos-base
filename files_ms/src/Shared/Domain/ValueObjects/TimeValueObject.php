<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\InvalidDateException;
use Src\Shared\Domain\ValueObject;

class TimeValueObject extends ValueObject
{
    private string|null $time;

    public function __construct(string|null $time = 'now', string $format = 'H:i:s')
    {
        $this->time = $this->setTime($time, $format);
    }

    public static function createFromFormat(string $format, string $time): self
    {
        return new self(
            \DateTimeImmutable::createFromFormat($format, $time)->format('H:i:s')
        );
    }

    private function setTime(string|null $time, string $format): string|null
    {
        try {
            $time = ($time !== 'now') ? $time : date($format);

            if ($time == null){
                return null;
            }

            $explodedDate = explode(':', $time);

            if (!$this->isValidTime((int)$explodedDate[0], (int)$explodedDate[1], (int)$explodedDate[2])) {
                throw new InvalidDateException("The given time '{$time}' is invalid", 400);
            }

            return $time;
        } catch (\Exception $e) {
            throw new InvalidDateException("Error setting time: {$e->getMessage()}", 400);
        }
    }

    private static function isValidTime($hour, $min, $sec): bool
    {
        return $hour >= 0 && $hour <= 23 && is_numeric($hour) &&
            $min >= 0 && $min <= 59 && is_numeric($min) &&
            $sec >= 0 && $sec <= 59 && is_numeric($sec);
    }

    /**
     * @throws \Exception
     */
    public static function parse(string $isoTime): self
    {
        $tmeImmutable = new \DateTimeImmutable($isoTime);

        return new self(
            $tmeImmutable->format('H:s:i')
        );
    }

    public static function now(): self
    {
        $dateTimeImmutable = new \DateTimeImmutable;

        return new self(
            $dateTimeImmutable->format('H:s:i')
        );
    }

    /**
     * @throws \Exception
     */
    public function format(string $format): string
    {
        $timeImmutable = new \DateTimeImmutable($this->time);

        return $timeImmutable->format($format);
    }

    public function __toString(): string
    {
        return $this->time;
    }

    public function value(): string|null
    {
        return $this->time;
    }

    /**
     * @return string|null
     */
    public function jsonSerialize(): string|null
    {
        return $this->time;
    }
}
