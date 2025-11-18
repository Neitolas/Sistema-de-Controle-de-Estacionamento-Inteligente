<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use InvalidArgumentException;

final class PricingStrategyFactory
{
    private const STRATEGY_MAP = [
        'carro' => CarPricingStrategy::class,
        'moto' => MotorcyclePricingStrategy::class,
        'caminhao' => TruckPricingStrategy::class,
    ];

    public function createStrategy(string $vehicleType): IPricingStrategy
    {
        $normalizedType = strtolower($vehicleType);

        if (!isset(self::STRATEGY_MAP[$normalizedType])) {
            throw new InvalidArgumentException("Tipo de veículo desconhecido: {$vehicleType}");
        }

        $strategyClass = self::STRATEGY_MAP[$normalizedType];

        return new $strategyClass();
    }
}
