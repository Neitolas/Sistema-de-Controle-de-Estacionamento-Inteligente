<?php

declare(strict_types=1);

namespace App\Domain;

interface IParkingRecordRepository
{
    public function saveEntry(ParkingRecord $record): int;

    public function updateExit(ParkingRecord $record): void;

    public function delete(int $id): void;

    public function findActiveByPlate(string $plate): ?ParkingRecord;

    public function findAllActive(): array;

    public function findAll(): array;
}
