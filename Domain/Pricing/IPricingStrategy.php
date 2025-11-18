<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use DateTimeImmutable;

interface IPricingStrategy
{
    public function calculateCost(DateTimeImmutable $entryTime, DateTimeImmutable $exitTime): float;
}
