<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use DateTimeImmutable;

final class MotorcyclePricingStrategy extends AbstractPricingStrategy
{
    private const HOURLY_RATE = 3.00;

    public function calculateCost(DateTimeImmutable $entryTime, DateTimeImmutable $exitTime): float
    {
        $hours = $this->calculateRoundedHours($entryTime, $exitTime);

        return $hours * self::HOURLY_RATE;
    }
}
