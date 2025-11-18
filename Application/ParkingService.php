<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\IParkingRecordRepository;
use App\Domain\ParkingRecord;
use App\Domain\Pricing\PricingStrategyFactory;
use DateTimeImmutable;
use DomainException;

final class ParkingService
{
    private const DEFAULT_VEHICLE_TYPES = ['carro', 'moto', 'caminhao'];

    public function __construct(
        private readonly IParkingRecordRepository $repository,
        private readonly PricingStrategyFactory $pricingFactory
    ) {
    }

    public function registerEntry(string $plate, string $vehicleType): void
    {
        $this->validateNotAlreadyParked($plate);

        $record = ParkingRecord::createEntry($plate, $vehicleType);
        $this->repository->saveEntry($record);
    }

    public function registerExit(string $plate): ParkingRecord
    {
        $record = $this->findActiveRecord($plate);
        $exitTime = new DateTimeImmutable();
        $totalCost = $this->calculateParkingCost($record, $exitTime);

        $record->markExit($exitTime, $totalCost);
        $this->repository->updateExit($record);

        return $record;
    }

    public function generateReport(): array
    {
        $allRecords = $this->repository->findAll();
        $report = $this->initializeReport();

        foreach ($allRecords as $record) {
            $this->aggregateRecordToReport($report, $record);
        }

        return $report;
    }

    private function validateNotAlreadyParked(string $plate): void
    {
        $activeRecord = $this->repository->findActiveByPlate($plate);

        if ($activeRecord) {
            throw new DomainException("O veículo com placa {$plate} já está estacionado.");
        }
    }

    private function findActiveRecord(string $plate): ParkingRecord
    {
        $record = $this->repository->findActiveByPlate($plate);

        if (!$record) {
            throw new DomainException("O veículo com placa {$plate} não está estacionado.");
        }

        return $record;
    }

    private function calculateParkingCost(ParkingRecord $record, DateTimeImmutable $exitTime): float
    {
        $strategy = $this->pricingFactory->createStrategy($record->getVehicleType());

        return $strategy->calculateCost($record->getEntryTime(), $exitTime);
    }

    private function initializeReport(): array
    {
        $report = [];

        foreach (self::DEFAULT_VEHICLE_TYPES as $type) {
            $report[$type] = ['total_vehicles' => 0, 'total_cost' => 0.0];
        }

        return $report;
    }

    private function aggregateRecordToReport(array &$report, ParkingRecord $record): void
    {
        $type = strtolower($record->getVehicleType());

        if (!isset($report[$type])) {
            return;
        }

        $report[$type]['total_vehicles']++;

        if ($record->getTotalCost() !== null) {
            $report[$type]['total_cost'] += $record->getTotalCost();
        }
    }
}
