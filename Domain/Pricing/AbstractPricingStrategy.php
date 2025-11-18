<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use DateTimeImmutable;

abstract class AbstractPricingStrategy implements IPricingStrategy
{
    private const SECONDS_PER_HOUR = 3600;

    protected function calculateRoundedHours(DateTimeImmutable $entryTime, DateTimeImmutable $exitTime): int
    {
        $intervalInSeconds = $exitTime->getTimestamp() - $entryTime->getTimestamp();
        $hours = $intervalInSeconds / self::SECONDS_PER_HOUR;

        return (int) ceil($hours);
    }
}
