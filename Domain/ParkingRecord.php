<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;
use DomainException;

final class ParkingRecord
{
    private ?DateTimeImmutable $exitTime;
    private ?float $totalCost;

    private function __construct(
        private readonly ?int $id,
        private readonly string $plate,
        private readonly string $vehicleType,
        private readonly DateTimeImmutable $entryTime,
        ?DateTimeImmutable $exitTime = null,
        ?float $totalCost = null
    ) {
        $this->exitTime = $exitTime;
        $this->totalCost = $totalCost;
    }

    public static function createEntry(string $plate, string $vehicleType): self
    {
        return new self(
            null,
            strtoupper($plate),
            strtolower($vehicleType),
            new DateTimeImmutable()
        );
    }

    public static function reconstituteFromDatabase(
        int $id,
        string $plate,
        string $vehicleType,
        DateTimeImmutable $entryTime,
        ?DateTimeImmutable $exitTime,
        ?float $totalCost
    ): self {
        return new self($id, $plate, $vehicleType, $entryTime, $exitTime, $totalCost);
    }

    public function markExit(DateTimeImmutable $exitTime, float $totalCost): void
    {
        $this->validateNotAlreadyExited();

        $this->exitTime = $exitTime;
        $this->totalCost = $totalCost;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlate(): string
    {
        return $this->plate;
    }

    public function getVehicleType(): string
    {
        return $this->vehicleType;
    }

    public function getEntryTime(): DateTimeImmutable
    {
        return $this->entryTime;
    }

    public function getExitTime(): ?DateTimeImmutable
    {
        return $this->exitTime;
    }

    public function getTotalCost(): ?float
    {
        return $this->totalCost;
    }

    public function isActive(): bool
    {
        return $this->exitTime === null;
    }

    private function validateNotAlreadyExited(): void
    {
        if ($this->exitTime !== null) {
            throw new DomainException(
                "O registro de estacionamento para a placa {$this->plate} já possui uma hora de saída."
            );
        }
    }
}
